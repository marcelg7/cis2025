@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Mobile Internet Plan: {{ $mobileInternetPlan->plan_name }}</h1>
    </div>

    <form method="POST" action="{{ route('cellular-pricing.mobile-internet.update', $mobileInternetPlan) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="soc_code" class="block text-sm font-medium text-gray-700">SOC Code</label>
                    <input type="text" name="soc_code" id="soc_code" value="{{ old('soc_code', $mobileInternetPlan->soc_code) }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    @error('soc_code')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="plan_name" class="block text-sm font-medium text-gray-700">Plan Name</label>
                    <input type="text" name="plan_name" id="plan_name" value="{{ old('plan_name', $mobileInternetPlan->plan_name) }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    @error('plan_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="monthly_rate" class="block text-sm font-medium text-gray-700">Monthly Rate ($)</label>
                    <input type="number" name="monthly_rate" id="monthly_rate" step="0.01" value="{{ old('monthly_rate', $mobileInternetPlan->monthly_rate) }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    @error('monthly_rate')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <input type="text" name="category" id="category" value="{{ old('category', $mobileInternetPlan->category) }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('category')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="promo_group" class="block text-sm font-medium text-gray-700">Promo Group</label>
                    <input type="text" name="promo_group" id="promo_group" value="{{ old('promo_group', $mobileInternetPlan->promo_group) }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('promo_group')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="effective_date" class="block text-sm font-medium text-gray-700">Effective Date</label>
					<input type="date" name="effective_date" id="effective_date" value="{{ old('effective_date', $mobileInternetPlan->effective_date->format('Y-m-d')) }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    @error('effective_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-center">
                    <input type="checkbox" name="is_current" id="is_current" value="1" 
                           {{ old('is_current', $mobileInternetPlan->is_current) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_current" class="ml-2 block text-sm text-gray-900">Current</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" 
                           {{ old('is_active', $mobileInternetPlan->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_test" id="is_test" value="1" 
                           {{ old('is_test', $mobileInternetPlan->is_test) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_test" class="ml-2 block text-sm text-gray-900">Test Plan</label>
                </div>
            </div>
        </div>
        
        <!-- Plan Description for Contract -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Plan Description & Contract Details</h3>
            <p class="text-sm text-gray-600 mb-4">
                Add detailed information about this mobile internet plan that will be displayed on contracts. 
                Use the editor below to format the text with bullet points, bold text, etc.
            </p>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Plan Description (appears on contract)
                </label>
                <textarea 
                    name="description" 
                    id="description" 
                    rows="10"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('description', $mobileInternetPlan->description) }}</textarea>
                @error('description')
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
                    <strong>Note:</strong> If no description is specified here, the contract will display basic plan information.
                </p>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('cellular-pricing.mobile-internet') }}" 
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
        selector: '#description',
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
        forced_root_block: 'p',
        valid_elements: 'p,br,strong,em,u,ul,ol,li,h1,h2,h3,h4,h5,h6,a[href|target],span[style]',
        valid_styles: {
            '*': 'font-weight,font-style,text-decoration'
        }
    });
</script>
@endsection