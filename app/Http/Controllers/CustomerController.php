<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use App\Models\Contract;
use App\Models\IvueAccount;
use App\Models\MobilityAccount;
use App\Models\Subscriber;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Helpers\SettingsHelper;

class CustomerController extends Controller
{
    public function index(): View
    {
        $latestContracts = Contract::with(['subscriber.mobilityAccount.ivueAccount.customer', 'activityType'])
            ->latest()
            ->take(12)
            ->get();
        $activeSessions = DB::table('sessions')
            ->where('user_id', '!=', auth()->id())
            ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
            ->get();
        $activeUsers = User::whereIn('id', $activeSessions->pluck('user_id'))->get();
        $recentCustomers = Customer::whereNotNull('last_fetched_at')
            ->latest('last_fetched_at')
            ->take(6)
            ->get();

        // Get deletable contract statuses from settings
        $deletableStatusesStr = SettingsHelper::get('deletable_contract_statuses', 'draft');
        $deletableStatuses = array_filter(explode(',', $deletableStatusesStr));

        return view('customers.index', compact('latestContracts', 'activeUsers', 'recentCustomers', 'deletableStatuses'));
    }

    public function fetch(Request $request): View
    {
        try {
            $customerNumber = $request->input('customer_number');
            Log::info('Fetching customer: ' . $customerNumber);
			$client = new Client([
						'timeout' => 120, // Total request timeout
						'connect_timeout' => 10, // Connection timeout
						'debug' => false, // Verbose logging
						'verify' => true, // Enforce SSL verification
						'http_errors' => true, // Throw exceptions for 4xx/5xx responses
						'force_ip_resolve' => 'v4', // Force IPv4 to avoid DNS issues
					]);
            

			// Fetch basic customer summary
            $response = $client->get(config('services.customer_api.url') . $customerNumber, [
                'headers' => ['Authorization' => 'Basic ' . config('services.customer_api.token')]
            ]);

			$data = json_decode($response->getBody(), true);

            // Fetch detailed customer information including contact methods
            $detailResponse = $client->get('https://hay.cloud.coop/services/secured/customerInformation/customer?customerId=' . $customerNumber, [
                'headers' => ['Authorization' => 'Basic ' . config('services.customer_api.token')]
            ]);

            $detailData = json_decode($detailResponse->getBody(), true);
            // Log only non-sensitive data (removed full API response to protect PII)
            Log::info('API response received for customer', ['customer_number' => $customerNumber, 'has_data' => !empty($data)]);
            if (empty($data) || !isset($data['customer'])) {
                Log::warning('Empty or invalid API response for customer: ' . $customerNumber);
                $latestContracts = Contract::with(['subscriber.mobilityAccount.ivueAccount.customer', 'activityType'])
                    ->latest()
                    ->take(12)
                    ->get();
                $activeSessions = DB::table('sessions')
                    ->where('user_id', '!=', auth()->id())
                    ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
                    ->get();
                $activeUsers = User::whereIn('id', $activeSessions->pluck('user_id'))->get();
                $recentCustomers = Customer::whereNotNull('last_fetched_at')
                    ->latest('last_fetched_at')
                    ->take(6)
                    ->get();
                return view('customers.index', compact('latestContracts', 'activeUsers', 'recentCustomers'))
                    ->withErrors(['customer_number' => 'Customer not found. Please check the customer number and try again.']);
            }
            // Merge zipCode and zip4 for full postal code
            $zipCode = $data['address']['zipCode'] ?? null;
            $zip4 = $data['address']['zip4'] ?? null;

            Log::info('Postal code processing', [
                'customer' => $customerNumber,
                'original_zipCode' => $zipCode,
                'original_zip4' => $zip4,
                'zip4_trimmed' => trim($zip4 ?? ''),
                'zip4_length' => strlen(trim($zip4 ?? ''))
            ]);

            // Combine zipCode and zip4 if zip4 exists and is not empty/whitespace
            // Note: "0" is a VALID character in postal codes, so we don't exclude it
            // IMPORTANT: Use strlen() instead of empty() because empty("0") returns true in PHP!
            if ($zipCode && strlen(trim($zip4 ?? '')) > 0) {
                // Check if it's a Canadian postal code (contains letters)
                if (preg_match('/[A-Za-z]/', $zipCode)) {
                    // Canadian postal code: format as "A1A 1A1"
                    // Combine zipCode and zip4, then add space after 3rd character
                    $fullPostal = $zipCode . $zip4;
                    Log::info('Formatting Canadian postal code', [
                        'fullPostal' => $fullPostal,
                        'length' => strlen($fullPostal)
                    ]);
                    if (strlen($fullPostal) >= 6) {
                        $zipCode = substr($fullPostal, 0, 3) . ' ' . substr($fullPostal, 3, 3);
                        Log::info('Formatted Canadian postal code', ['result' => $zipCode]);
                    }
                } else {
                    // US ZIP code: format as "12345-6789"
                    $zipCode .= '-' . $zip4;
                    Log::info('Formatted US ZIP code', ['result' => $zipCode]);
                }
            }

            Log::info('Final postal code to save', ['zipCode' => $zipCode]);

            $customer = Customer::updateOrCreate(
                ['ivue_customer_number' => $data['customer']],
                [
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
                ]
            );
            $ivueAccountsData = collect($data['accounts'] ?? [])->map(function ($accountNumber) {
                return [
                    'ivue_account' => $accountNumber,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();
            $customer->ivueAccounts()->upsert(
                $ivueAccountsData,
                ['ivue_account'],
                ['status']
            );

            // Get deletable contract statuses from settings
            $deletableStatusesStr = SettingsHelper::get('deletable_contract_statuses', 'draft');
            $deletableStatuses = array_filter(explode(',', $deletableStatusesStr));

            return view('customers.show', compact('customer', 'deletableStatuses'));
        } catch (RequestException $e) {
            Log::error('API fetch error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            // Determine user-friendly error message based on status code
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;
            if ($statusCode === 404) {
                $errorMessage = 'Customer number not found in NISC billing system. Please verify the customer number and try again.';
            } elseif ($statusCode >= 500) {
                $errorMessage = 'NISC billing system is currently unavailable. Please try again later.';
            } elseif ($statusCode === 401 || $statusCode === 403) {
                $errorMessage = 'Unable to access customer information. Please contact IT support.';
            } else {
                $errorMessage = 'Failed to fetch customer information. Please try again.';
            }

            $latestContracts = Contract::with(['subscriber.mobilityAccount.ivueAccount.customer', 'activityType'])
                ->latest()
                ->take(12)
                ->get();
            $activeSessions = DB::table('sessions')
                ->where('user_id', '!=', auth()->id())
                ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
                ->get();
            $activeUsers = User::whereIn('id', $activeSessions->pluck('user_id'))->get();
            $recentCustomers = Customer::whereNotNull('last_fetched_at')
                ->latest('last_fetched_at')
                ->take(6)
                ->get();
            return view('customers.index', compact('latestContracts', 'activeUsers', 'recentCustomers'))
                ->withErrors(['customer_number' => $errorMessage]);
        } catch (\Exception $e) {
            Log::error('Fetch error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $latestContracts = Contract::with(['subscriber.mobilityAccount.ivueAccount.customer', 'activityType'])
                ->latest()
                ->take(12)
                ->get();
            $activeSessions = DB::table('sessions')
                ->where('user_id', '!=', auth()->id())
                ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
                ->get();
            $activeUsers = User::whereIn('id', $activeSessions->pluck('user_id'))->get();
            $recentCustomers = Customer::whereNotNull('last_fetched_at')
                ->latest('last_fetched_at')
                ->take(6)
                ->get();
            return view('customers.index', compact('latestContracts', 'activeUsers', 'recentCustomers'))
                ->withErrors(['customer_number' => 'An error occurred while fetching the customer. Please try again.']);
        }
    }

    public function addMobilityForm($customerId): View
    {
        $customer = Customer::with('ivueAccounts')->findOrFail($customerId);
        $hasAvailableIvueAccounts = $customer->ivueAccounts->filter(function($ivueAccount) {
            return $ivueAccount->mobilityAccount === null;
        })->isNotEmpty();
        if (!$hasAvailableIvueAccounts) {
            return redirect()->route('customers.show', $customer->id)
                ->with('error', 'No available IVUE accounts to attach a mobility account to.');
        }
        return view('customers.add-mobility', compact('customer'));
    }

    public function storeMobility(Request $request, $customerId)
    {
        $request->validate([
            'ivue_account_id' => 'required|exists:ivue_accounts,id',
            'mobility_account' => 'required|string|max:127|unique:mobility_accounts,mobility_account',
        ]);
        $ivueAccount = IvueAccount::findOrFail($request->ivue_account_id);
        if ($ivueAccount->mobilityAccount) {
            return redirect()->route('customers.show', $customerId)
                ->with('error', 'This IVUE account already has a mobility account.');
        }
        MobilityAccount::create([
            'ivue_account_id' => $ivueAccount->id,
            'mobility_account' => $request->mobility_account,
            'status' => 'active',
        ]);
        return redirect()->route('customers.show', $customerId)
            ->with('success', 'Mobility account added successfully.');
    }

    public function editMobilityForm($customerId, $mobilityAccountId): View
    {
        $customer = Customer::findOrFail($customerId);
        $mobilityAccount = MobilityAccount::with('ivueAccount')->findOrFail($mobilityAccountId);

        // Verify this mobility account belongs to this customer
        if ($mobilityAccount->ivueAccount->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to mobility account.');
        }

        return view('customers.edit-mobility', compact('customer', 'mobilityAccount'));
    }

    public function updateMobility(Request $request, $customerId, $mobilityAccountId)
    {
        $mobilityAccount = MobilityAccount::findOrFail($mobilityAccountId);

        $request->validate([
            'mobility_account' => 'required|string|max:127|unique:mobility_accounts,mobility_account,' . $mobilityAccount->id,
        ]);

        $mobilityAccount->update([
            'mobility_account' => $request->mobility_account,
        ]);

        return redirect()->route('customers.show', $customerId)
            ->with('success', 'Mobility account updated successfully.');
    }

    public function show($customerId): View
    {
        $customer = Customer::with('ivueAccounts.mobilityAccount')->findOrFail($customerId);

        // Get deletable contract statuses from settings
        $deletableStatusesStr = SettingsHelper::get('deletable_contract_statuses', 'draft');
        $deletableStatuses = array_filter(explode(',', $deletableStatusesStr));

        return view('customers.show', compact('customer', 'deletableStatuses'));
    }

    public function edit($customerId): View
    {
        $customer = Customer::findOrFail($customerId);
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, $customerId)
    {
        $customer = Customer::findOrFail($customerId);

        $request->validate([
            'contract_email' => 'nullable|email|max:255',
        ]);

        $customer->update([
            'contract_email' => $request->contract_email,
        ]);

        return redirect()->route('customers.show', $customerId)
            ->with('success', 'Contract email updated successfully.');
    }

    public function addSubscriberForm($customerId): View
    {
        $customer = Customer::with('ivueAccounts.mobilityAccount')->findOrFail($customerId);
        return view('customers.add-subscriber', compact('customer'));
    }

    public function storeSubscriber(Request $request, $customerId)
    {
        $request->validate([
            'mobility_account_id' => 'required|exists:mobility_accounts,id',
            'mobile_number' => 'required|string|max:20|unique:subscribers,mobile_number',
            'first_name' => 'nullable|string|max:60',
            'last_name' => 'nullable|string|max:60',
        ]);
        Subscriber::create([
            'mobility_account_id' => $request->mobility_account_id,
            'mobile_number' => $request->mobile_number,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'status' => 'active',
        ]);
        return redirect()->route('customers.show', $customerId)
            ->with('success', 'Subscriber added successfully.');
    }

    public function search(Request $request)
    {
        try {
            // Build query parameters
            $queryParams = [];
            if ($request->has('lastName')) {
                $queryParams['lastName'] = $request->input('lastName');
            }
            if ($request->has('firstName')) {
                $queryParams['firstName'] = $request->input('firstName');
            }
            if ($request->has('businessName')) {
                $queryParams['businessName'] = $request->input('businessName');
            }
            if ($request->has('address')) {
                $queryParams['address'] = $request->input('address');
            }

            // Require at least one search parameter
            if (empty($queryParams)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide at least one search criteria.'
                ]);
            }

            $client = new Client([
                'timeout' => 120,
                'connect_timeout' => 10,
                'verify' => true,
                'http_errors' => true,
                'force_ip_resolve' => 'v4',
            ]);

            // Build query string
            $queryString = http_build_query($queryParams);
            $url = 'https://hay.cloud.coop/services/secured/customer/summary?' . $queryString;

            Log::info('Searching customers with: ' . $queryString);

            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => 'Basic ' . config('services.customer_api.token'),
                    'Accept' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            Log::info('Customer search response', ['data' => $data]);

            // Transform the data for frontend
            $customers = [];

            // Check if response is an array of customers (not wrapped in customerList)
            if (is_array($data)) {
                // If it's a direct array of customers
                if (isset($data[0])) {
                    foreach ($data as $customer) {
                        $customers[] = [
                            'customerNumber' => $customer['customer'] ?? $customer['customerNumber'] ?? '',
                            'displayName' => $customer['displayName'] ?? 'N/A',
                            'address' => $this->formatAddress($customer),
                        ];
                    }
                }
                // Or if it's wrapped in customerList
                else if (isset($data['customerList']) && is_array($data['customerList'])) {
                    foreach ($data['customerList'] as $customer) {
                        $customers[] = [
                            'customerNumber' => $customer['customer'] ?? $customer['customerNumber'] ?? '',
                            'displayName' => $customer['displayName'] ?? 'N/A',
                            'address' => $this->formatAddress($customer),
                        ];
                    }
                }
            }

            Log::info('Transformed customers', ['customers' => $customers, 'count' => count($customers)]);

            return response()->json([
                'success' => true,
                'customers' => $customers
            ]);

        } catch (RequestException $e) {
            Log::error('Customer search failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search customers. Please try again.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Customer search error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    private function formatAddress($customer): string
    {
        $parts = [];

        // Check if address is a nested object
        if (isset($customer['address']) && is_array($customer['address'])) {
            $address = $customer['address'];
            if (!empty($address['lineOne'])) {
                $parts[] = $address['lineOne'];
            }
            if (!empty($address['lineTwo'])) {
                $parts[] = $address['lineTwo'];
            }
            if (!empty($address['city'])) {
                $parts[] = $address['city'];
            }
            if (!empty($address['state'])) {
                $parts[] = $address['state'];
            }
            if (!empty($address['zipCode']) && !empty($address['zip4'])) {
                $parts[] = $address['zipCode'] . '-' . $address['zip4'];
            } else if (!empty($address['zipCode'])) {
                $parts[] = $address['zipCode'];
            }
        } else {
            // Fallback for flat structure
            if (!empty($customer['address'])) {
                $parts[] = $customer['address'];
            }
            if (!empty($customer['city'])) {
                $parts[] = $customer['city'];
            }
            if (!empty($customer['state'])) {
                $parts[] = $customer['state'];
            }
            if (!empty($customer['zipCode'])) {
                $parts[] = $customer['zipCode'];
            }
        }

        return !empty($parts) ? implode(', ', $parts) : 'N/A';
    }
}