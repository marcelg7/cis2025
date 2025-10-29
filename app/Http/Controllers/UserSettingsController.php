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
            'background_type' => 'required|in:default,upload,random,color',
            'background_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
        ]);

        $user->session_lifetime = $validated['session_lifetime'];
        $user->theme = $validated['theme'];
        $user->show_development_info = $request->has('show_development_info');

        // Handle background preferences
        if ($validated['background_type'] === 'color') {
            $user->background_type = 'color';
            $user->background_value = $request->input('background_color', '#f3f4f6');
        } elseif ($validated['background_type'] === 'upload') {
            // If selecting upload, must provide a file OR already have one uploaded
            if ($request->hasFile('background_image')) {
                // Validate image dimensions
                $image = $request->file('background_image');
                $imageInfo = getimagesize($image->getRealPath());

                if ($imageInfo[0] < 1920 || $imageInfo[1] < 1080) {
                    return redirect()->back()
                        ->withErrors(['background_image' => 'Image must be at least 1920x1080 pixels. Your image is ' . $imageInfo[0] . 'x' . $imageInfo[1] . ' pixels.'])
                        ->withInput();
                }

                // Delete old background image if exists
                if ($user->background_type === 'upload' && $user->background_value) {
                    \Storage::disk('public')->delete('backgrounds/' . $user->background_value);
                }

                // Store new image
                $filename = 'user-' . $user->id . '-' . time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('backgrounds', $filename, 'public');
                $user->background_type = 'upload';
                $user->background_value = $filename;
            } elseif ($user->background_type === 'upload' && $user->background_value) {
                // User already has an uploaded image, keep it
                // Don't change anything
            } else {
                // No file uploaded and no existing file - show error
                return redirect()->back()
                    ->withErrors(['background_image' => 'Please select an image file to upload, or choose a different background option.'])
                    ->withInput();
            }
        } elseif ($validated['background_type'] === 'default' || $validated['background_type'] === 'random') {
            // Delete old uploaded background if switching away from upload
            if ($user->background_type === 'upload' && $user->background_value) {
                \Storage::disk('public')->delete('backgrounds/' . $user->background_value);
            }
            $user->background_type = $validated['background_type'];
            $user->background_value = null;
        }

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