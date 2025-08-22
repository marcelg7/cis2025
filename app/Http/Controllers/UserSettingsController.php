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
        
        if ($request->input('action') === 'reset') {
            // Define default styles
            $defaultStyles = [
                'primary-button' => [
                    'background' => '#4f46e5',
                    'hover' => '#4338ca',
                    'text' => '#ffffff',
                    'text_size' => 'xs',
                ],
                'secondary-button' => [
                    'background' => '#ffffff',
                    'hover' => '#f9fafb',
                    'text' => '#374151',
                    'text_size' => 'xs',
                ],
                'primary-link' => [
                    'background' => '#4f46e5',
                    'hover' => '#4338ca',
                    'text' => '#ffffff',
                    'text_size' => 'xs',
                ],
                'secondary-link' => [
                    'background' => '#ffffff',
                    'hover' => '#f9fafb',
                    'text' => '#374151',
                    'text_size' => 'xs',
                ],
                'warning-button' => [
                    'background' => '#fef3c7',
                    'hover' => '#fde68a',
                    'text' => '#b45309',
                    'text_size' => 'xs',
                ],
                'warning-link' => [
                    'background' => '#fef3c7',
                    'hover' => '#fde68a',
                    'text' => '#b45309',
                    'text_size' => 'xs',
                ],
                'danger-button' => [
                    'background' => '#fee2e2',
                    'hover' => '#dc2626',
                    'text' => '#b91c1c',
                    'text_size' => 'xs',
                ],
                'danger-button-submit' => [
                    'background' => '#fee2e2',
                    'hover' => '#dc2626',
                    'text' => '#b91c1c',
                    'text_size' => 'xs',
                ],
                'danger-link' => [
                    'background' => '#fee2e2',
                    'hover' => '#991b1b',
                    'text' => '#b91c1c',
                    'text_size' => 'xs',
                ],
                'info-button' => [
                    'background' => '#dbeafe',
                    'hover' => '#bfdbfe',
                    'text' => '#1e40af',
                    'text_size' => 'xs',
                ],
                'info-link' => [
                    'background' => '#dbeafe',
                    'hover' => '#bfdbfe',
                    'text' => '#1e40af',
                    'text_size' => 'xs',
                ],
            ];

            $user->component_styles = $defaultStyles;
            $user->save();

            return redirect()->route('users.settings.edit')->with('success', 'Styles reset to defaults successfully!');
        }
        
        $validated = $request->validate([
            'session_lifetime' => 'required|integer|min:5|max:1440',
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
            // Info Button
            'component_styles.info-button.background' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.info-button.hover' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.info-button.text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.info-button.text_size' => 'nullable|in:xs,sm,base,lg,xl',
            // Info Link
            'component_styles.info-link.background' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.info-link.hover' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.info-link.text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'component_styles.info-link.text_size' => 'nullable|in:xs,sm,base,lg,xl',
        ]);
        
        $user->session_lifetime = $validated['session_lifetime'];
        unset($validated['session_lifetime']);
        $user->component_styles = $validated['component_styles'];
        $user->save();

        if ($request->has('session_lifetime')) {
            $request->session()->regenerate();
        }
        
        return redirect()->route('users.settings.edit')->with('success', 'Settings updated successfully!');
    }
}