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

class CustomerController extends Controller
{
	public function index(): View {
		

		
		
		$latestContracts = Contract::with(['subscriber.mobilityAccount.ivueAccount.customer', 'plan', 'device', 'activityType'])
			->latest()
			->take(12)
			->get();

		// Get active users from session table
		$activeSessions = DB::table('sessions')
			->where('user_id', '!=', auth()->id())
			->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
			->get();
			
		$activeUsers = User::whereIn('id', $activeSessions->pluck('user_id'))->get();


		// Get latest fetched customers
		$recentCustomers = Customer::whereNotNull('last_fetched_at')
			->latest('last_fetched_at')
			->take(6)
			->get();
			
		return view('customers.index', compact('latestContracts', 'activeUsers', 'recentCustomers'));
	}

    public function fetch(Request $request): View {
        try {
            $customerNumber = $request->input('customer_number');
            Log::info('Fetching customer: ' . $customerNumber);


            $client = new Client();
            $response = $client->get('https://hay.cloud.coop/services/secured/customer/summary/' . $customerNumber, [
            'headers' => ['Authorization' => 'Basic TWFyY2VsOkNBVDlvbDhpaztOSVNDNA==']
           ]);

        $data = json_decode($response->getBody(), true);
        Log::info('API response: ' . json_encode($data));

        // Check if response is empty or invalid
        if (empty($data) || !isset($data['customer'])) {
            Log::warning('Empty or invalid API response for customer: ' . $customerNumber);
            return view('customers.index')->withErrors(['customer_number' => 'Customer not found. Please check the customer number and try again.']);
        }

        // Proceed with customer creation/update
        $customer = Customer::updateOrCreate(
            ['ivue_customer_number' => $data['customer']],
            [
                'first_name' => $data['firstName'] ?? 'Unknown',
                'last_name' => $data['lastName'] ?? 'Customer',
                'email' => $data['users'][0] ?? null,
                'address' => $data['address']['lineOne'] . ($data['address']['lineTwo'] ? ' ' . $data['address']['lineTwo'] : ''),
                'city' => $data['address']['city'] ?? null,
                'state' => $data['address']['state'] ?? null,
                'zip_code' => $data['address']['zipCode'] ?? null,
                'display_name' => $data['displayName'] ?? ($data['firstName'] . ' ' . $data['lastName']),
                'is_individual' => $data['isIndividual'] ?? true,
                'customer_json' => json_encode($data),
            ]
        );

        // Store IVUE accounts
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
		
        // Update the last_fetched_at timestamp
        $customer->update(['last_fetched_at' => now()]);
        		

        return view('customers.show', compact('customer'));
		
    } catch (RequestException $e) {
        Log::error('API fetch error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        return view('customers.index')->withErrors(['customer_number' => 'Failed to fetch customer: ' . $e->getMessage()]);
    } catch (\Exception $e) {
        Log::error('Fetch error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        return view('customers.index')->withErrors(['customer_number' => 'An error occurred while fetching the customer. Please try again.']);
    }
}

    public function addMobilityForm($customerId): View {
        $customer = Customer::with('ivueAccounts')->findOrFail($customerId);

		// Check if there are any IVUE accounts without mobility accounts
		$hasAvailableIvueAccounts = $customer->ivueAccounts->filter(function($ivueAccount) {
			return $ivueAccount->mobilityAccount === null;
		})->isNotEmpty();
		
		if (!$hasAvailableIvueAccounts) {
			return redirect()->route('customers.show', $customer->id)
				->with('error', 'No available IVUE accounts to attach a mobility account to.');
		}
				
		
        return view('customers.add-mobility', compact('customer'));
    }

    public function storeMobility(Request $request, $customerId) {
        $request->validate([
            'ivue_account_id' => 'required|exists:ivue_accounts,id',
            'mobility_account' => 'required|string|max:127|unique:mobility_accounts,mobility_account',
        ]);

        $ivueAccount = IvueAccount::findOrFail($request->ivue_account_id);

        // Check if this IVUE account already has a mobility account
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

    public function show($customerId): View {
        $customer = Customer::with('ivueAccounts.mobilityAccount')->findOrFail($customerId);
        return view('customers.show', compact('customer'));
    }
	
    public function addSubscriberForm($customerId): View {
        $customer = Customer::with('ivueAccounts.mobilityAccount')->findOrFail($customerId);
        return view('customers.add-subscriber', compact('customer'));
    }

    public function storeSubscriber(Request $request, $customerId) {
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
}