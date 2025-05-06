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
            // Primary Button
            'component_styles.primary-button.background' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.primary-button.hover' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.primary-button.text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.primary-button.text_size' => 'nullable|in:xs,sm,base,lg,xl',
            // Secondary Button
            'component_styles.secondary-button.background' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.secondary-button.hover' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.secondary-button.text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.secondary-button.text_size' => 'nullable|in:xs,sm,base,lg,xl',
            // Primary Link
            'component_styles.primary-link.background' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.primary-link.hover' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.primary-link.text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.primary-link.text_size' => 'nullable|in:xs,sm,base,lg,xl',
            // Secondary Link
            'component_styles.secondary-link.background' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.secondary-link.hover' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.secondary-link.text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.secondary-link.text_size' => 'nullable|in:xs,sm,base,lg,xl',
            // Warning Button
            'component_styles.warning-button.background' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.warning-button.hover' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.warning-button.text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.warning-button.text_size' => 'nullable|in:xs,sm,base,lg,xl',
            // Warning Link
            'component_styles.warning-link.background' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.warning-link.hover' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.warning-link.text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.warning-link.text_size' => 'nullable|in:xs,sm,base,lg,xl',
            // Danger Button
            'component_styles.danger-button.background' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.danger-button.hover' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.danger-button.text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.danger-button.text_size' => 'nullable|in:xs,sm,base,lg,xl',
            // Danger Button Submit
            'component_styles.danger-button-submit.background' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.danger-button-submit.hover' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.danger-button-submit.text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.danger-button-submit.text_size' => 'nullable|in:xs,sm,base,lg,xl',
            // Danger Link
            'component_styles.danger-link.background' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.danger-link.hover' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.danger-link.text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.danger-link.text_size' => 'nullable|in:xs,sm,base,lg,xl',			
        ]);
        
        // Update session lifetime separately
        $user->session_lifetime = $validated['session_lifetime'];
        unset($validated['session_lifetime']);

        // Update component styles
        $user->component_styles = $validated['component_styles'];
        $user->save();

        // Regenerate session if lifetime changed
        if ($request->has('session_lifetime')) {
            $request->session()->regenerate();
        }
        
        return redirect()->route('users.settings.edit')->with('success', 'Settings updated successfully!');
    }
}