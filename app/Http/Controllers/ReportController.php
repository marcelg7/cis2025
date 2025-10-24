<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\BellDevice;
use App\Models\RatePlan;
use App\Models\MobileInternetPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Monthly Contract Summary Report
     */
    public function contractSummary(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        $contracts = Contract::with(['subscriber.mobilityAccount.ivueAccount.customer', 'activityType', 'updatedBy', 'locationModel', 'bellDevice'])
            ->whereBetween('contract_date', [$startDate, $endDate])
            ->orderBy('contract_date')
            ->get();

        // Calculate summary statistics
        $totalContracts = $contracts->count();
        $totalRevenue = $contracts->sum(function($contract) {
            return ($contract->rate_plan_price ?? 0) + ($contract->mobile_internet_price ?? 0);
        });
        $totalDeviceRevenue = $contracts->sum('bell_retail_price');

        $contractsByType = $contracts->groupBy('activityType.name')->map->count();
        $contractsByLocation = $contracts->groupBy('locationModel.name')->map->count();
        $contractsByUser = $contracts->groupBy('updatedBy.name')->map->count();

        $data = [
            'month' => $month,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'contracts' => $contracts,
            'totalContracts' => $totalContracts,
            'totalRevenue' => $totalRevenue,
            'totalDeviceRevenue' => $totalDeviceRevenue,
            'contractsByType' => $contractsByType,
            'contractsByLocation' => $contractsByLocation,
            'contractsByUser' => $contractsByUser,
        ];

        if ($request->has('export')) {
            return $this->exportContractSummary($data, $request->input('export'));
        }

        return view('reports.contract-summary', $data);
    }

    /**
     * Device Sales Report
     */
    public function deviceSales(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        $deviceSales = Contract::with(['bellDevice', 'updatedBy', 'locationModel'])
            ->whereBetween('contract_date', [$startDate, $endDate])
            ->whereNotNull('bell_device_id')
            ->get();

        // Group by device
        $salesByDevice = $deviceSales->groupBy('bellDevice.device_name')->map(function($contracts) {
            return [
                'count' => $contracts->count(),
                'total_revenue' => $contracts->sum('bell_retail_price'),
                'avg_price' => $contracts->avg('bell_retail_price'),
            ];
        })->sortByDesc('count');

        // Group by pricing type
        $salesByPricingType = $deviceSales->groupBy('bell_pricing_type')->map->count();

        // Group by user
        $salesByUser = $deviceSales->groupBy('updatedBy.name')->map->count();

        $data = [
            'month' => $month,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'deviceSales' => $deviceSales,
            'salesByDevice' => $salesByDevice,
            'salesByPricingType' => $salesByPricingType,
            'salesByUser' => $salesByUser,
            'totalDevicesSold' => $deviceSales->count(),
            'totalRevenue' => $deviceSales->sum('bell_retail_price'),
        ];

        if ($request->has('export')) {
            return $this->exportDeviceSales($data, $request->input('export'));
        }

        return view('reports.device-sales', $data);
    }

    /**
     * Plan Adoption Trends Report
     */
    public function planAdoption(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        $contracts = Contract::with(['ratePlan', 'mobileInternetPlan', 'locationModel'])
            ->whereBetween('contract_date', [$startDate, $endDate])
            ->get();

        // Rate plan adoption
        $ratePlanAdoption = $contracts->whereNotNull('rate_plan_id')
            ->groupBy('ratePlan.plan_name')
            ->map(function($contracts) {
                return [
                    'count' => $contracts->count(),
                    'revenue' => $contracts->sum('rate_plan_price'),
                ];
            })->sortByDesc('count');

        // Mobile internet adoption
        $internetAdoption = $contracts->whereNotNull('mobile_internet_plan_id')
            ->groupBy('mobileInternetPlan.plan_name')
            ->map(function($contracts) {
                return [
                    'count' => $contracts->count(),
                    'revenue' => $contracts->sum('mobile_internet_price'),
                ];
            })->sortByDesc('count');

        // BYOD vs Device contracts
        $byodVsDevice = [
            'BYOD' => $contracts->where('bell_pricing_type', 'byod')->count(),
            'Device' => $contracts->whereIn('bell_pricing_type', ['smartpay', 'dro'])->count(),
        ];

        $data = [
            'month' => $month,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'ratePlanAdoption' => $ratePlanAdoption,
            'internetAdoption' => $internetAdoption,
            'byodVsDevice' => $byodVsDevice,
            'totalContracts' => $contracts->count(),
        ];

        if ($request->has('export')) {
            return $this->exportPlanAdoption($data, $request->input('export'));
        }

        return view('reports.plan-adoption', $data);
    }

    /**
     * Export Contract Summary to Excel/PDF
     */
    private function exportContractSummary($data, $format)
    {
        if ($format === 'excel') {
            return $this->exportContractSummaryExcel($data);
        }

        return $this->exportContractSummaryPdf($data);
    }

    /**
     * Export Contract Summary to Excel
     */
    private function exportContractSummaryExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $sheet->setTitle('Contract Summary');
        $sheet->setCellValue('A1', 'Contract Summary Report');
        $sheet->setCellValue('A2', 'Period: ' . $data['startDate']->format('M Y'));

        // Style header
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true);

        // Summary statistics
        $row = 4;
        $sheet->setCellValue('A' . $row, 'Total Contracts:');
        $sheet->setCellValue('B' . $row, $data['totalContracts']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Monthly Revenue:');
        $sheet->setCellValue('B' . $row, '$' . number_format($data['totalRevenue'], 2));
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Device Revenue:');
        $sheet->setCellValue('B' . $row, '$' . number_format($data['totalDeviceRevenue'], 2));

        // Contract details
        $row += 2;
        $headers = ['Date', 'Customer', 'Activity Type', 'Device', 'Plan Revenue', 'Device Revenue', 'Location', 'CSR'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E2E8F0');
            $col++;
        }

        $row++;
        foreach ($data['contracts'] as $contract) {
            $customer = $contract->subscriber->mobilityAccount->ivueAccount->customer ?? null;
            $sheet->setCellValue('A' . $row, $contract->contract_date->format('Y-m-d'));
            $sheet->setCellValue('B' . $row, $customer ? $customer->display_name : 'N/A');
            $sheet->setCellValue('C' . $row, $contract->activityType->name ?? 'N/A');
            $sheet->setCellValue('D' . $row, $contract->bellDevice->device_name ?? 'BYOD');
            $sheet->setCellValue('E' . $row, '$' . number_format(($contract->rate_plan_price ?? 0) + ($contract->mobile_internet_price ?? 0), 2));
            $sheet->setCellValue('F' . $row, '$' . number_format($contract->bell_retail_price ?? 0, 2));
            $sheet->setCellValue('G' . $row, $contract->locationModel->name ?? 'N/A');
            $sheet->setCellValue('H' . $row, $contract->updatedBy->name ?? 'N/A');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output
        $writer = new Xlsx($spreadsheet);
        $filename = 'contract_summary_' . $data['month'] . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Export Contract Summary to PDF
     */
    private function exportContractSummaryPdf($data)
    {
        $pdf = PDF::loadView('reports.pdf.contract-summary', $data);
        return $pdf->download('contract_summary_' . $data['month'] . '.pdf');
    }

    /**
     * Export Device Sales to Excel/PDF
     */
    private function exportDeviceSales($data, $format)
    {
        if ($format === 'excel') {
            return $this->exportDeviceSalesExcel($data);
        }

        return $this->exportDeviceSalesPdf($data);
    }

    /**
     * Export Device Sales to Excel
     */
    private function exportDeviceSalesExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Device Sales');
        $sheet->setCellValue('A1', 'Device Sales Report');
        $sheet->setCellValue('A2', 'Period: ' . $data['startDate']->format('M Y'));

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        // Summary
        $row = 4;
        $sheet->setCellValue('A' . $row, 'Total Devices Sold:');
        $sheet->setCellValue('B' . $row, $data['totalDevicesSold']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Revenue:');
        $sheet->setCellValue('B' . $row, '$' . number_format($data['totalRevenue'], 2));

        // Sales by device
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Device');
        $sheet->setCellValue('B' . $row, 'Units Sold');
        $sheet->setCellValue('C' . $row, 'Total Revenue');
        $sheet->setCellValue('D' . $row, 'Avg Price');
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);

        $row++;
        foreach ($data['salesByDevice'] as $deviceName => $stats) {
            $sheet->setCellValue('A' . $row, $deviceName);
            $sheet->setCellValue('B' . $row, $stats['count']);
            $sheet->setCellValue('C' . $row, '$' . number_format($stats['total_revenue'], 2));
            $sheet->setCellValue('D' . $row, '$' . number_format($stats['avg_price'], 2));
            $row++;
        }

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'device_sales_' . $data['month'] . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Export Device Sales to PDF
     */
    private function exportDeviceSalesPdf($data)
    {
        $pdf = PDF::loadView('reports.pdf.device-sales', $data);
        return $pdf->download('device_sales_' . $data['month'] . '.pdf');
    }

    /**
     * Export Plan Adoption to Excel/PDF
     */
    private function exportPlanAdoption($data, $format)
    {
        if ($format === 'excel') {
            return $this->exportPlanAdoptionExcel($data);
        }

        return $this->exportPlanAdoptionPdf($data);
    }

    /**
     * Export Plan Adoption to Excel
     */
    private function exportPlanAdoptionExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Plan Adoption');
        $sheet->setCellValue('A1', 'Plan Adoption Report');
        $sheet->setCellValue('A2', 'Period: ' . $data['startDate']->format('M Y'));

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        // Rate Plans
        $row = 4;
        $sheet->setCellValue('A' . $row, 'Rate Plans');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $row++;

        $sheet->setCellValue('A' . $row, 'Plan Name');
        $sheet->setCellValue('B' . $row, 'Subscriptions');
        $sheet->setCellValue('C' . $row, 'Revenue');
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $row++;

        foreach ($data['ratePlanAdoption'] as $planName => $stats) {
            $sheet->setCellValue('A' . $row, $planName);
            $sheet->setCellValue('B' . $row, $stats['count']);
            $sheet->setCellValue('C' . $row, '$' . number_format($stats['revenue'], 2));
            $row++;
        }

        // Mobile Internet
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Mobile Internet Plans');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $row++;

        $sheet->setCellValue('A' . $row, 'Plan Name');
        $sheet->setCellValue('B' . $row, 'Subscriptions');
        $sheet->setCellValue('C' . $row, 'Revenue');
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $row++;

        foreach ($data['internetAdoption'] as $planName => $stats) {
            $sheet->setCellValue('A' . $row, $planName);
            $sheet->setCellValue('B' . $row, $stats['count']);
            $sheet->setCellValue('C' . $row, '$' . number_format($stats['revenue'], 2));
            $row++;
        }

        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'plan_adoption_' . $data['month'] . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Export Plan Adoption to PDF
     */
    private function exportPlanAdoptionPdf($data)
    {
        $pdf = PDF::loadView('reports.pdf.plan-adoption', $data);
        return $pdf->download('plan_adoption_' . $data['month'] . '.pdf');
    }
}
