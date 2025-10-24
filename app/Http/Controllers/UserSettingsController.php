<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\NotificationPreference;

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

    /**
     * Show notification preferences page
     */
    public function editNotificationPreferences(): View
    {
        $user = Auth::user();

        // Get user's current preferences
        $preferences = [];
        foreach (NotificationPreference::TYPES as $type => $label) {
            $preference = $user->notificationPreferences()->where('notification_type', $type)->first();
            $preferences[$type] = [
                'label' => $label,
                'enabled' => $preference ? $preference->enabled : true, // Default to enabled
            ];
        }

        return view('users.notification-preferences', [
            'user' => $user,
            'preferences' => $preferences,
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences(Request $request)
    {
        $user = Auth::user();

        // Update each notification type preference
        foreach (NotificationPreference::TYPES as $type => $label) {
            $enabled = $request->has('notifications.' . $type);

            // Update or create preference
            $user->notificationPreferences()->updateOrCreate(
                ['notification_type' => $type],
                ['enabled' => $enabled]
            );
        }

        return redirect()->route('users.notification-preferences.edit')
            ->with('success', 'Notification preferences updated successfully!');
    }
}