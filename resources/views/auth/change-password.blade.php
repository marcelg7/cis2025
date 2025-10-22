<!-- resources/views/auth/change-password.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2"> <!-- Added px-2 -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">Change Password</h1>
            
            @if (session('status'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                    {{ session('status') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('password.custom_update') }}">
                @csrf
                
                <div class="mb-4">
                    <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    @error('current_password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>

                    <!-- Password Strength Indicator -->
                    <div class="mt-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-gray-600">Password Strength:</span>
                            <span id="strength-text" class="text-xs font-medium"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="strength-bar" class="h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p id="strength-feedback" class="mt-1 text-xs text-gray-500"></p>
                    </div>

                    <!-- Breach Check Indicator -->
                    <div id="breach-check" class="mt-2 hidden">
                        <div class="flex items-center gap-2 p-2 rounded-md" id="breach-status">
                            <svg class="w-4 h-4 animate-spin" id="breach-loading" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span class="text-xs" id="breach-message">Checking password...</span>
                        </div>
                    </div>

                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                
                <div>
                    <x-primary-button type="submit">
                        Change Password
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');
    const strengthFeedback = document.getElementById('strength-feedback');
    const breachCheck = document.getElementById('breach-check');
    const breachStatus = document.getElementById('breach-status');
    const breachMessage = document.getElementById('breach-message');
    const breachLoading = document.getElementById('breach-loading');

    let breachCheckTimeout;

    passwordInput.addEventListener('input', function() {
        const password = this.value;

        if (password.length === 0) {
            strengthBar.style.width = '0%';
            strengthText.textContent = '';
            strengthFeedback.textContent = '';
            breachCheck.classList.add('hidden');
            return;
        }

        // Calculate password strength
        const strength = calculatePasswordStrength(password);
        updateStrengthUI(strength);

        // Debounce breach check
        clearTimeout(breachCheckTimeout);
        breachCheckTimeout = setTimeout(() => {
            checkPasswordBreach(password);
        }, 800);
    });

    function calculatePasswordStrength(password) {
        let score = 0;
        const feedback = [];

        // Length check
        if (password.length >= 8) score += 20;
        if (password.length >= 12) score += 20;
        if (password.length >= 16) score += 10;
        else if (password.length < 8) feedback.push('Use at least 8 characters');

        // Character variety
        if (/[a-z]/.test(password)) score += 10;
        else feedback.push('Add lowercase letters');

        if (/[A-Z]/.test(password)) score += 10;
        else feedback.push('Add uppercase letters');

        if (/[0-9]/.test(password)) score += 15;
        else feedback.push('Add numbers');

        if (/[^a-zA-Z0-9]/.test(password)) score += 15;
        else feedback.push('Add special characters');

        // Penalty for common patterns
        if (/(.)\1{2,}/.test(password)) {
            score -= 10;
            feedback.push('Avoid repeated characters');
        }

        if (/^[0-9]+$/.test(password)) {
            score -= 20;
            feedback.push('Don\'t use only numbers');
        }

        if (/^[a-zA-Z]+$/.test(password)) {
            score -= 10;
            feedback.push('Mix letters with numbers');
        }

        // Sequential characters
        if (/abc|bcd|cde|123|234|345|456|567|678|789/i.test(password)) {
            score -= 10;
            feedback.push('Avoid sequential characters');
        }

        return {
            score: Math.max(0, Math.min(100, score)),
            feedback: feedback.slice(0, 2)
        };
    }

    function updateStrengthUI(strength) {
        const score = strength.score;
        let color, text;

        if (score < 30) {
            color = 'bg-red-500';
            text = 'Weak';
        } else if (score < 60) {
            color = 'bg-yellow-500';
            text = 'Fair';
        } else if (score < 80) {
            color = 'bg-blue-500';
            text = 'Good';
        } else {
            color = 'bg-green-500';
            text = 'Strong';
        }

        strengthBar.style.width = score + '%';
        strengthBar.className = `h-2 rounded-full transition-all duration-300 ${color}`;
        strengthText.textContent = text;
        strengthText.className = `text-xs font-medium ${color.replace('bg-', 'text-')}`;

        if (strength.feedback.length > 0) {
            strengthFeedback.textContent = strength.feedback.join('. ') + '.';
        } else {
            strengthFeedback.textContent = 'Excellent password!';
            strengthFeedback.className = 'mt-1 text-xs text-green-600';
        }
    }

    async function checkPasswordBreach(password) {
        // Check if crypto.subtle is available (requires HTTPS or localhost)
        if (!window.crypto || !window.crypto.subtle) {
            breachCheck.classList.remove('hidden');
            breachLoading.classList.add('hidden');
            breachStatus.className = 'flex items-center gap-2 p-2 rounded-md bg-blue-50 border border-blue-200';
            breachMessage.innerHTML = '<span class="text-blue-700">ℹ️ Client-side breach checking requires HTTPS. Your password will be validated server-side when you submit.</span>';
            return;
        }

        breachCheck.classList.remove('hidden');
        breachLoading.classList.remove('hidden');
        breachMessage.textContent = 'Checking password...';
        breachStatus.className = 'flex items-center gap-2 p-2 rounded-md bg-gray-50';

        try {
            // Create SHA-1 hash of the password
            const msgBuffer = new TextEncoder().encode(password);
            const hashBuffer = await crypto.subtle.digest('SHA-1', msgBuffer);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('').toUpperCase();

            // Send first 5 characters to Have I Been Pwned API (k-Anonymity model)
            const prefix = hashHex.substring(0, 5);
            const suffix = hashHex.substring(5);

            const response = await fetch(`https://api.pwnedpasswords.com/range/${prefix}`);

            if (!response.ok) {
                throw new Error('API request failed');
            }

            const data = await response.text();

            // Check if our suffix appears in the response
            const lines = data.split('\n');
            const found = lines.find(line => line.startsWith(suffix));

            breachLoading.classList.add('hidden');

            if (found) {
                const count = found.split(':')[1];
                breachStatus.className = 'flex items-center gap-2 p-2 rounded-md bg-red-50 border border-red-200';
                breachMessage.innerHTML = `<strong class="text-red-700">⚠️ This password has been exposed in ${parseInt(count).toLocaleString()} data breaches!</strong> <span class="text-red-600">Choose a different password.</span>`;
            } else {
                breachStatus.className = 'flex items-center gap-2 p-2 rounded-md bg-green-50 border border-green-200';
                breachMessage.innerHTML = '<span class="text-green-700">✓ This password has not been found in known data breaches.</span>';
            }
        } catch (error) {
            console.error('Breach check error:', error);
            breachLoading.classList.add('hidden');
            breachStatus.className = 'flex items-center gap-2 p-2 rounded-md bg-blue-50 border border-blue-200';
            breachMessage.innerHTML = '<span class="text-blue-700">ℹ️ Unable to verify breach status online. Your password will be validated server-side when you submit.</span>';
        }
    }
});
</script>
@endsection