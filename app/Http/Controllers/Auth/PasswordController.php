<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        // Check if password has been breached using Have I Been Pwned API
        if ($this->isPasswordBreached($validated['password'])) {
            throw ValidationException::withMessages([
                'password' => 'This password has been exposed in known data breaches. Please choose a different password for your security.',
            ])->errorBag('updatePassword');
        }

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'Sweet! Your password has been updated.');
    }

    /**
     * Check if password has been breached using Have I Been Pwned API (k-Anonymity model)
     */
    private function isPasswordBreached(string $password): bool
    {
        try {
            // Create SHA-1 hash of the password
            $hash = strtoupper(sha1($password));
            $prefix = substr($hash, 0, 5);
            $suffix = substr($hash, 5);

            // Query Have I Been Pwned API with first 5 characters
            $response = Http::timeout(5)->get("https://api.pwnedpasswords.com/range/{$prefix}");

            if (!$response->successful()) {
                // If API is down, allow the password (don't block users)
                \Log::warning('Have I Been Pwned API check failed', ['status' => $response->status()]);
                return false;
            }

            // Check if our suffix appears in the response
            $lines = explode("\n", $response->body());
            foreach ($lines as $line) {
                if (strpos($line, $suffix) === 0) {
                    // Password found in breach database
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            // If any error occurs, don't block the user
            \Log::error('Password breach check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
