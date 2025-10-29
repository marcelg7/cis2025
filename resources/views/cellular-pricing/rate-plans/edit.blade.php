<!-- cellular-pricing/rate-plans/edit.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 page-container">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Rate Plan: {{ $ratePlan->plan_name }}</h1>
    </div>

    <form method="POST" action="{{ route('cellular-pricing.rate-plans.update', $ratePlan) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="soc_code" class="block text-sm font-medium text-gray-700">SOC Code</label>
                    <input type="text" name="soc_code" id="soc_code" value="{{ old('soc_code', $ratePlan->soc_code) }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    @error('soc_code')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="plan_name" class="block text-sm font-medium text-gray-700">Plan Name</label>
                    <input type="text" name="plan_name" id="plan_name" value="{{ old('plan_name', $ratePlan->plan_name) }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    @error('plan_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="plan_type" class="block text-sm font-medium text-gray-700">Plan Type</label>
                    <select name="plan_type" id="plan_type" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <option value="byod" {{ old('plan_type', $ratePlan->plan_type) === 'byod' ? 'selected' : '' }}>BYOD</option>
                        <option value="smartpay" {{ old('plan_type', $ratePlan->plan_type) === 'smartpay' ? 'selected' : '' }}>SmartPay</option>
                    </select>
                    @error('plan_type')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tier" class="block text-sm font-medium text-gray-700">Tier</label>
                    <select name="tier" id="tier" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">None</option>
                        <option value="Lite" {{ old('tier', $ratePlan->tier) === 'Lite' ? 'selected' : '' }}>Lite</option>
                        <option value="Select" {{ old('tier', $ratePlan->tier) === 'Select' ? 'selected' : '' }}>Select</option>
                        <option value="Max" {{ old('tier', $ratePlan->tier) === 'Max' ? 'selected' : '' }}>Max</option>
                        <option value="Ultra" {{ old('tier', $ratePlan->tier) === 'Ultra' ? 'selected' : '' }}>Ultra</option>
                    </select>
                    @error('tier')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="base_price" class="block text-sm font-medium text-gray-700">Base Price ($)</label>
                    <input type="number" name="base_price" id="base_price" step="0.01" value="{{ old('base_price', $ratePlan->base_price) }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    @error('base_price')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="promo_price" class="block text-sm font-medium text-gray-700">Promo Price ($)</label>
                    <input type="number" name="promo_price" id="promo_price" step="0.01" value="{{ old('promo_price', $ratePlan->promo_price) }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('promo_price')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="promo_description" class="block text-sm font-medium text-gray-700">Promo Description</label>
                    <input type="text" name="promo_description" id="promo_description" value="{{ old('promo_description', $ratePlan->promo_description) }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('promo_description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="data_amount" class="block text-sm font-medium text-gray-700">Data Amount</label>
                    <input type="text" name="data_amount" id="data_amount" value="{{ old('data_amount', $ratePlan->data_amount) }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="e.g., 50GB, Unlimited">
                    @error('data_amount')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="effective_date" class="block text-sm font-medium text-gray-700">Effective Date</label>
					<input type="date" name="effective_date" id="effective_date" value="{{ old('effective_date', $ratePlan->effective_date ? $ratePlan->effective_date->format('Y-m-d') : '') }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    @error('effective_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-center">
                    <input type="checkbox" name="is_international" id="is_international" value="1" 
                           {{ old('is_international', $ratePlan->is_international) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_international" class="ml-2 block text-sm text-gray-900">International Plan</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_us_mexico" id="is_us_mexico" value="1" 
                           {{ old('is_us_mexico', $ratePlan->is_us_mexico) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_us_mexico" class="ml-2 block text-sm text-gray-900">US/Mexico Plan</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" 
                           {{ old('is_active', $ratePlan->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                </div>
            </div>
        </div>
        
        <!-- Plan Features / Details for Contract -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Plan Features & Contract Details</h3>
            <p class="text-sm text-gray-600 mb-4">
                Add detailed information about this plan that will be displayed on contracts. 
                Use the editor below to format the text with bullet points, bold text, etc.
            </p>
            
            <div>
                <label for="features" class="block text-sm font-medium text-gray-700 mb-2">
                    Plan Features (appears on contract)
                </label>
                <textarea 
                    name="features" 
                    id="features" 
                    rows="10"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('features', $ratePlan->features) }}</textarea>
                @error('features')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                <p class="text-sm text-blue-900">
                    <strong>Tip:</strong> You can use the formatting toolbar to add:
                </p>
                <ul class="mt-2 text-sm text-blue-800 list-disc list-inside space-y-1">
                    <li>Bold, italic, and underlined text</li>
                    <li>Bullet points and numbered lists</li>
                    <li>Headers and subheaders</li>
                    <li>Links and special formatting</li>
                </ul>
                <p class="mt-2 text-xs text-blue-700">
                    <strong>Note:</strong> If no features are specified here, the contract will display default plan information.
                </p>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('cellular-pricing.rate-plans') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                Save Changes
            </button>
        </div>
    </form>
</div>

<!-- TinyMCE Rich Text Editor -->
<script src="https://cdn.tiny.mce.com/1/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#features',
        height: 400,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
            'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic underline | ' +
            'alignleft aligncenter alignright alignjustify | ' +
            'bullist numlist outdent indent | removeformat | code | help',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; }',
        branding: false,
        promotion: false,
        // Convert line breaks to paragraphs
        forced_root_block: 'p',
        // Keep clean HTML
        valid_elements: 'p,br,strong,em,u,ul,ol,li,h1,h2,h3,h4,h5,h6,a[href|target],span[style]',
        valid_styles: {
            '*': 'font-weight,font-style,text-decoration'
        }
    });
</script>
@endsection