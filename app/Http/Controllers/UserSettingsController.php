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
            'session_lifetime' => 'required|integer|min:5|max:1440',
            'theme' => 'required|in:default,dark,high-contrast,warm,cool,deuteranopia,protanopia',
            'show_development_info' => 'nullable|boolean',
        ]);
        
        $user->session_lifetime = $validated['session_lifetime'];
        $user->theme = $validated['theme'];
        $user->show_development_info = $request->has('show_development_info');
        $user->save();

        if ($request->has('session_lifetime')) {
            $request->session()->regenerate();
        }
        
        return redirect()->route('users.settings.edit')->with('success', 'Settings updated successfully!');
    }
}