@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2"> <!-- Added px-2 -->
        <h1 class="text-2xl font-semibold text-gray-900">Edit Device: {{ $device->manufacturer }} {{ $device->model }}</h1>
        <form method="POST" action="{{ route('devices.update', $device->id) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="manufacturer" class="block text-sm font-medium text-gray-700">Manufacturer</label>
                <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer', $device->manufacturer) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                @error('manufacturer')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                <input type="text" name="model" id="model" value="{{ old('model', $device->model) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                @error('model')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="srp" class="block text-sm font-medium text-gray-700">SRP ($)</label>
                <input type="number" name="srp" id="srp" step="0.01" value="{{ old('srp', $device->srp) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                @error('srp')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700">Image (optional)</label>
                @if ($device->image)
                    <img src="{{ Storage::url($device->image) }}" width="100" class="mt-2" alt="{{ $device->model }}">
                @endif
                <input type="file" name="image" id="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-900 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                @error('image')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <h2 class="text-lg font-medium text-gray-900">Pricing Options</h2>
                <div id="pricings" class="mt-4 space-y-4">
                    @foreach ($pricings as $index => $pricing)
                        <div class="pricing grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Type</label>
                                <select name="pricings[{{ $index }}][type]" required onchange="toggleTerm(this)" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="smartpay" {{ old("pricings.{$index}.type", $pricing['type']) === 'smartpay' ? 'selected' : '' }}>SmartPay</option>
                                    <option value="byod" {{ old("pricings.{$index}.type", $pricing['type']) === 'byod' ? 'selected' : '' }}>BYOD</option>
                                </select>
                                @error("pricings.{$index}.type")
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Price ($)</label>
                                <input type="number" name="pricings[{{ $index }}][price]" step="0.01" value="{{ old("pricings.{$index}.price", $pricing['price']) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                @error("pricings.{$index}.price")
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Term (months)</label>
                                <input type="number" name="pricings[{{ $index }}][term]" min="0" value="{{ old("pricings.{$index}.term", $pricing['type'] === 'smartpay' ? ($pricing['term'] ?? 24) : ($pricing['term'] ?? 0)) }}" class="term-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" {{ old("pricings.{$index}.type", $pricing['type']) === 'smartpay' ? 'required' : '' }}>
                                @error("pricings.{$index}.term")
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addPricing()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Another Pricing
                </button>
            </div>
            <div class="flex justify-end">
                <a href="{{ route('devices.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update Device
                </button>
            </div>
        </form>
    </div>

    <script>
        let pricingCount = {{ count($pricings) }};
        function addPricing() {
            const div = document.createElement('div');
            div.className = 'pricing grid grid-cols-1 sm:grid-cols-3 gap-4';
            div.innerHTML = `
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="pricings[${pricingCount}][type]" required onchange="toggleTerm(this)" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="smartpay">SmartPay</option>
                        <option value="byod">BYOD</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Price ($)</label>
                    <input type="number" name="pricings[${pricingCount}][price]" step="0.01" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Term (months)</label>
                    <input type="number" name="pricings[${pricingCount}][term]" min="0" value="24" class="term-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                <br>
            `;
            document.getElementById('pricings').appendChild(div);
            toggleTerm(div.querySelector('select'));
            pricingCount++;
        }

        function toggleTerm(select) {
            const termInput = select.parentElement.parentElement.querySelector('.term-input');
            if (select.value === 'byod') {
                termInput.removeAttribute('required');
                termInput.value = 0;
            } else {
                termInput.setAttribute('required', 'required');
                termInput.value = 24;
            }
        }

        document.querySelectorAll('.pricing select').forEach(toggleTerm);
    </script>
@endsection