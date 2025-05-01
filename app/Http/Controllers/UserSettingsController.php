<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserSettingsController extends Controller
{
    public function edit(): View
    {
        return view('users.settings', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'session_lifetime' => 'required|integer|min:5|max:1440', // Between 5 minutes and 24 hours
        ]);
        
        $user->update($validated);
        
        // If session lifetime was changed, we need to regenerate the session
        if ($request->has('session_lifetime')) {
            $request->session()->regenerate();
        }
        
        return redirect()->route('users.settings.edit')->with('success', 'Settings updated successfully!');
    }
}