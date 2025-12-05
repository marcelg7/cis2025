<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ActivityType;
use App\Models\CommitmentPeriod;
use App\Models\Contract;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\BellDevice;
use App\Models\RatePlan;
use App\Models\MobileInternetPlan;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request): View
    {
        $query = $request->input('query');

		// If the parameter doesn't exist at all (first visit), default to true
		// If it exists (form was submitted), use its value
		$includeTest = $request->has('submitted')
			? $request->boolean('include_test')
			: true;

        // Additional filters
        $contractStatus = $request->input('contract_status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $category = $request->input('category'); // Filter by result type
        
        // Initialize all result categories to empty collections
        $results = [
            'customers' => collect(),
            'subscribers' => collect(),
            'contracts' => collect(),
            'bell_devices' => collect(),
            'rate_plans' => collect(),
            'mobile_internet_plans' => collect(),
            'users' => collect(),
            'activity_types' => collect(),
            'commitment_periods' => collect(),
        ];

        if ($query && strlen($query) >= 2) { // Minimum 2 characters

            // Normalize phone number for searching (remove all non-numeric characters)
            $normalizedPhone = preg_replace('/[^0-9]/', '', $query);

            // Additional validation: ensure normalized phone is not empty and contains only digits
            // This prevents SQL injection by ensuring only numeric values are used in whereRaw
            $isPhoneSearch = !empty($normalizedPhone) &&
                            ctype_digit($normalizedPhone) &&
                            strlen($normalizedPhone) >= 7; // At least 7 digits suggests phone search

            // Search Customers (skip if category filter is set and not 'customers')
            if (!$category || $category === 'customers')
            {
                $customersQuery = Customer::where(function($q) use ($query, $normalizedPhone, $isPhoneSearch) {
                    $q->where('ivue_customer_number', 'LIKE', "%{$query}%")
                      ->orWhere('display_name', 'LIKE', "%{$query}%")
                      ->orWhere('first_name', 'LIKE', "%{$query}%")
                      ->orWhere('last_name', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%")
                      ->orWhere('address', 'LIKE', "%{$query}%")
                      ->orWhere('city', 'LIKE', "%{$query}%")
                      ->orWhere('zip_code', 'LIKE', "%{$query}%")
                      ->orWhere('phone', 'LIKE', "%{$query}%");

                    // If it looks like a phone number, search by digits only
                    if ($isPhoneSearch) {
                        // Strip all formatting from phone field - use parameter binding to prevent SQL injection
                        $q->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '-', ''), ' ', ''), '(', ''), ')', ''), '.', '') LIKE ?", ["%{$normalizedPhone}%"]);
                    }
                });

                if (!$includeTest) {
                    $customersQuery->where('is_test', 0);
                }

                $results['customers'] = $customersQuery->limit(10)->get();
            }

            // Search Subscribers - IMPROVED PHONE SEARCH (skip if category filter is set and not 'subscribers')
            if (!$category || $category === 'subscribers')
            {
                $subscribersQuery = Subscriber::where(function($q) use ($query, $normalizedPhone, $isPhoneSearch) {
                    // Always search by original query
                    $q->where('mobile_number', 'LIKE', "%{$query}%")
                      ->orWhere('first_name', 'LIKE', "%{$query}%")
                      ->orWhere('last_name', 'LIKE', "%{$query}%");

                    // If it looks like a phone number, search by digits only
                    if ($isPhoneSearch) {
                        // Strip all formatting: dashes, spaces, parentheses, periods - use parameter binding to prevent SQL injection
                        $q->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(mobile_number, '-', ''), ' ', ''), '(', ''), ')', ''), '.', '') LIKE ?", ["%{$normalizedPhone}%"]);
                    }
                });

                if (!$includeTest) {
                    $subscribersQuery->where('is_test', 0);
                }

                $results['subscribers'] = $subscribersQuery
                    ->with('mobilityAccount.ivueAccount.customer')
                    ->limit(10)
                    ->get();
            }

            // Search Contracts - IMPROVED PHONE SEARCH (skip if category filter is set and not 'contracts')
            if (!$category || $category === 'contracts')
            {
                $contractsQuery = Contract::where(function($q) use ($query, $normalizedPhone, $isPhoneSearch) {
                    // Search by contract ID
                    if (is_numeric($query)) {
                        $q->where('id', $query);
                    }

                    // Search by subscriber mobile number
                    $q->orWhereHas('subscriber', function ($subQ) use ($query, $normalizedPhone, $isPhoneSearch) {
                        $subQ->where('mobile_number', 'LIKE', "%{$query}%")
                             ->orWhere('first_name', 'LIKE', "%{$query}%")
                             ->orWhere('last_name', 'LIKE', "%{$query}%");

                        // If it looks like a phone number, search by digits only - use parameter binding to prevent SQL injection
                        if ($isPhoneSearch) {
                            $subQ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(mobile_number, '-', ''), ' ', ''), '(', ''), ')', ''), '.', '') LIKE ?", ["%{$normalizedPhone}%"]);
                        }
                    });

                    // Search by customer name
                    $q->orWhereHas('subscriber.mobilityAccount.ivueAccount.customer', function ($custQ) use ($query) {
                        $custQ->where('display_name', 'LIKE', "%{$query}%")
                              ->orWhere('ivue_customer_number', 'LIKE', "%{$query}%");
                    });
                });

                // Apply contract status filter
                if ($contractStatus) {
                    $contractsQuery->where('status', $contractStatus);
                }

                // Apply date range filters
                if ($dateFrom) {
                    $contractsQuery->whereDate('created_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $contractsQuery->whereDate('created_at', '<=', $dateTo);
                }

                if (!$includeTest) {
                    $contractsQuery->where('is_test', 0);
                }

                $results['contracts'] = $contractsQuery
                    ->with('subscriber.mobilityAccount.ivueAccount.customer', 'bellDevice')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            }

            // Search Bell Devices (skip if category filter is set and not 'devices')
            if (!$category || $category === 'devices')
            {
                $bellDevicesQuery = BellDevice::where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('manufacturer', 'LIKE', "%{$query}%")
                      ->orWhere('model', 'LIKE', "%{$query}%");
                })
                ->where('is_active', 1);

                if (!$includeTest) {
                    $bellDevicesQuery->where('is_test', 0);
                }

                $results['bell_devices'] = $bellDevicesQuery->limit(10)->get();
            }

            // Search Rate Plans (skip if category filter is set and not 'plans')
            if (!$category || $category === 'plans')
            {
                $ratePlansQuery = RatePlan::where(function($q) use ($query) {
                    $q->where('plan_name', 'LIKE', "%{$query}%")
                      ->orWhere('soc_code', 'LIKE', "%{$query}%");
                })
                ->where('is_current', 1)
                ->where('is_active', 1);

                if (!$includeTest) {
                    $ratePlansQuery->where('is_test', 0);
                }

                $results['rate_plans'] = $ratePlansQuery->limit(10)->get();
            }

            // Search Mobile Internet Plans (skip if category filter is set and not 'plans')
            if (!$category || $category === 'plans')
            {
                $mobileInternetQuery = MobileInternetPlan::where(function($q) use ($query) {
                    $q->where('plan_name', 'LIKE', "%{$query}%")
                      ->orWhere('soc_code', 'LIKE', "%{$query}%");
                })
                ->where('is_current', 1)
                ->where('is_active', 1);

                if (!$includeTest) {
                    $mobileInternetQuery->where('is_test', 0);
                }

                $results['mobile_internet_plans'] = $mobileInternetQuery->limit(10)->get();
            }

            // Search Users (if admin - skip if category filter is set and not 'admin')
            if ((!$category || $category === 'admin') && auth()->check() && auth()->user()->hasRole('admin')) {
                $results['users'] = User::where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get();

                $results['activity_types'] = ActivityType::where('name', 'LIKE', "%{$query}%")
                    ->limit(10)
                    ->get();

                $results['commitment_periods'] = CommitmentPeriod::where('name', 'LIKE', "%{$query}%")
                    ->limit(10)
                    ->get();
            }
        }

        // Count total results
        $totalResults = collect($results)->sum(fn($collection) => $collection->count());

        return view('search.results', compact('results', 'query', 'totalResults', 'includeTest', 'contractStatus', 'dateFrom', 'dateTo', 'category'));
    }
}