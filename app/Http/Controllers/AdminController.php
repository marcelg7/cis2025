<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use App\Models\Customer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\Admin::class . ':admin');
    }

    public function index()
    {
        $testContractsCount = DB::table('contracts')->where('is_test', 1)->count();
        $testSubscribersCount = DB::table('subscribers')->where('is_test', 1)->count();
        $testCustomersCount = DB::table('customers')->where('is_test', 1)->count();
        $totalCustomersCount = DB::table('customers')->count();

        return view('admin.index', compact(
            'testContractsCount',
            'testSubscribersCount',
            'testCustomersCount',
            'totalCustomersCount',
        ));
    }

    public function settings()
    {
        $logPruneDays = Setting::get('log_prune_days', 90);
        $supervisorEmail = Setting::get('cellular_supervisor_email', 'supervisor@hay.net');
        $showDevInfo = Setting::get('show_development_info', 'false');
        $connectionFee = Setting::get('default_connection_fee', 80); // NEW
        
        return view('admin.settings', compact(
            'logPruneDays', 
            'supervisorEmail', 
            'showDevInfo',
            'connectionFee' // NEW
        ));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'log_prune_days' => 'required|integer|min:30',
            'cellular_supervisor_email' => 'required|email',
            'default_connection_fee' => 'required|numeric|min:0|max:500', // NEW
        ]);

        Setting::set('log_prune_days', $request->log_prune_days);
        Setting::set('cellular_supervisor_email', $request->cellular_supervisor_email);
        Setting::set('show_development_info', $request->has('show_development_info') ? 'true' : 'false');
        Setting::set('default_connection_fee', $request->default_connection_fee); // NEW

        return redirect()->route('admin.settings')->with('success', 'Settings updated successfully!');
    }

    public function clearTestData(Request $request)
    {
        $dryRun = $request->input('dry_run', false);
        $reset = $request->input('reset', false);
        
        $command = ['command' => 'db:clear-test-data'];
        
        if ($dryRun) {
            $command['--dry-run'] = true;
        }
        
        if ($reset) {
            $command['--reset'] = true;
        }
        
        $exitCode = Artisan::call($command['command'], array_filter($command));
        
        if ($exitCode === 0) {
            return redirect()->route('admin.index')->with('success', 'Test data operation completed successfully.');
        }
        
        return redirect()->route('admin.index')->with('error', 'Failed to clear test data.');
    }

    public function seedTestData(Request $request)
    {
        $exitCode = Artisan::call('db:seed');

        if ($exitCode === 0) {
            return redirect()->route('admin.index')->with('success', 'Test data seeded successfully.');
        }

        return redirect()->route('admin.index')->with('error', 'Failed to seed test data.');
    }

    public function refetchAllCustomers(Request $request)
    {
        set_time_limit(300); // 5 minutes max

        $customers = Customer::all();
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        Log::info('Starting bulk customer refetch', ['total_customers' => $customers->count()]);

        $client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'debug' => false,
            'verify' => true,
            'http_errors' => true,
            'force_ip_resolve' => 'v4',
        ]);

        foreach ($customers as $customer) {
            try {
                $customerNumber = $customer->ivue_customer_number;

                // Fetch basic customer summary
                $response = $client->get(config('services.customer_api.url') . $customerNumber, [
                    'headers' => ['Authorization' => 'Basic ' . config('services.customer_api.token')]
                ]);

                $data = json_decode($response->getBody(), true);

                // Fetch detailed customer information
                $detailResponse = $client->get('https://hay.cloud.coop/services/secured/customerInformation/customer?customerId=' . $customerNumber, [
                    'headers' => ['Authorization' => 'Basic ' . config('services.customer_api.token')]
                ]);

                $detailData = json_decode($detailResponse->getBody(), true);

                if (empty($data) || !isset($data['customer'])) {
                    $errorCount++;
                    $errors[] = "Customer {$customerNumber}: No data returned from API";
                    continue;
                }

                // Process postal code (same logic as CustomerController)
                $zipCode = $data['address']['zipCode'] ?? null;
                $zip4 = $data['address']['zip4'] ?? null;

                if ($zipCode && strlen(trim($zip4 ?? '')) > 0) {
                    if (preg_match('/[A-Za-z]/', $zipCode)) {
                        // Canadian postal code
                        $fullPostal = $zipCode . $zip4;
                        if (strlen($fullPostal) >= 6) {
                            $zipCode = substr($fullPostal, 0, 3) . ' ' . substr($fullPostal, 3, 3);
                        }
                    } else {
                        // US ZIP code
                        $zipCode .= '-' . $zip4;
                    }
                }

                // Update customer
                $customer->update([
                    'first_name' => $data['firstName'] ?? 'Unknown',
                    'last_name' => $data['lastName'] ?? 'Customer',
                    'email' => $data['users'][0] ?? null,
                    'address' => $data['address']['lineOne'] . ($data['address']['lineTwo'] ? ' ' . $data['address']['lineTwo'] : ''),
                    'city' => $data['address']['city'] ?? null,
                    'state' => $data['address']['state'] ?? null,
                    'zip_code' => $zipCode,
                    'display_name' => $data['displayName'] ?? ($data['firstName'] . ' ' . $data['lastName']),
                    'is_individual' => $data['isIndividual'] ?? true,
                    'customer_json' => json_encode($data),
                    'contact_methods' => $detailData['contactMethods'] ?? null,
                    'additional_contacts' => $detailData['additionalContacts'] ?? null,
                    'last_fetched_at' => now(),
                ]);

                $successCount++;

                // Small delay to avoid overwhelming the API (100ms between requests)
                usleep(100000);

            } catch (RequestException $e) {
                $errorCount++;
                $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
                $errors[] = "Customer {$customer->ivue_customer_number}: API error (HTTP {$statusCode})";
                Log::error('Bulk refetch API error', [
                    'customer' => $customer->ivue_customer_number,
                    'error' => $e->getMessage()
                ]);
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Customer {$customer->ivue_customer_number}: {$e->getMessage()}";
                Log::error('Bulk refetch error', [
                    'customer' => $customer->ivue_customer_number,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Bulk customer refetch completed', [
            'success' => $successCount,
            'errors' => $errorCount
        ]);

        $message = "Refetch completed: {$successCount} succeeded, {$errorCount} failed.";

        if ($errorCount > 0 && count($errors) <= 10) {
            $message .= " Errors: " . implode('; ', $errors);
        } elseif ($errorCount > 10) {
            $message .= " (First 10 errors: " . implode('; ', array_slice($errors, 0, 10)) . ")";
        }

        if ($errorCount > 0) {
            return redirect()->route('admin.index')->with('warning', $message);
        }

        return redirect()->route('admin.index')->with('success', $message);
    }
}