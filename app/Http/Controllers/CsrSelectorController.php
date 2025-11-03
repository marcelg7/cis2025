<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CsrSelectorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the CSR selector page (grid of CSR names/photos)
     */
    public function index()
    {
        // Get all active CSRs (users who are not shared devices)
        $csrs = User::where('is_shared_device', false)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $activeCsr = null;
        if (session()->has('active_csr_id')) {
            $activeCsr = User::find(session('active_csr_id'));
        }

        return view('csr-selector.index', compact('csrs', 'activeCsr'));
    }

    /**
     * Select a CSR and store in session
     */
    public function select(Request $request)
    {
        $request->validate([
            'csr_id' => 'required|exists:users,id'
        ]);

        $csr = User::findOrFail($request->csr_id);

        // Store selected CSR in session
        session(['active_csr_id' => $csr->id]);
        session(['active_csr_name' => $csr->name]);

        Log::info('CSR selected for shared device', [
            'shared_device_user' => auth()->user()->name,
            'selected_csr' => $csr->name,
            'selected_csr_id' => $csr->id
        ]);

        return redirect()->intended(route('customers.index'))
            ->with('success', "Now working as: {$csr->name}");
    }

    /**
     * Clear the active CSR selection
     */
    public function clear()
    {
        $activeCsrName = session('active_csr_name', 'Unknown');

        session()->forget(['active_csr_id', 'active_csr_name']);

        Log::info('CSR selection cleared', [
            'shared_device_user' => auth()->user()->name,
            'previous_csr' => $activeCsrName
        ]);

        return redirect()->route('csr-selector.index')
            ->with('success', 'CSR selection cleared. Please select a CSR.');
    }
}
