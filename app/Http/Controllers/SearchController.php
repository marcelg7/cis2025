<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ActivityType;
use App\Models\CommitmentPeriod;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function search(Request $request): View
    {
        $query = $request->input('query');

        // Initialize all result categories to empty collections
        $results = [
            'customers' => collect(),
            'activity_types' => collect(),
            'commitment_periods' => collect(),
            'contracts' => collect()
        ];

        if ($query) {
            // Search Customers
            $results['customers'] = Customer::where('ivue_customer_number', 'LIKE', "%{$query}%")
                ->orWhere('display_name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->get();

            // Search Activity Types
            $results['activity_types'] = ActivityType::where('name', 'LIKE', "%{$query}%")
                ->get();

            // Search Commitment Periods
            $results['commitment_periods'] = CommitmentPeriod::where('name', 'LIKE', "%{$query}%")
                ->get();

            // Search Contracts (by ID or subscriber mobile number)
            $results['contracts'] = Contract::where('id', 'LIKE', "%{$query}%")
                ->orWhereHas('subscriber', function ($q) use ($query) {
                    $q->where('mobile_number', 'LIKE', "%{$query}%");
                })
                ->with('subscriber')
                ->get();
        }

        return view('search.results', compact('results', 'query'));
    }
}