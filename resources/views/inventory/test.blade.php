@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">NISC API Inventory Test</h1>
        <p class="mt-2 text-sm text-gray-600">Test NISC equipment/inventory API endpoints to discover available data</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Test Forms Column -->
        <div class="space-y-6">
            <!-- Equipment by ID Test -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Test: Equipment by ID</h3>
                <form id="testEquipmentById" class="space-y-4">
                    <div>
                        <label for="equipment_id" class="block text-sm font-medium text-gray-700 mb-1">Equipment ID</label>
                        <input type="text" id="equipment_id" name="equipment_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Enter equipment ID">
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Test Endpoint
                    </button>
                </form>
            </div>

            <!-- Equipment by Name Test -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Test: Equipment by Name</h3>
                <form id="testEquipmentByName" class="space-y-4">
                    <div>
                        <label for="equipment_name" class="block text-sm font-medium text-gray-700 mb-1">Equipment Name</label>
                        <input type="text" id="equipment_name" name="equipment_name" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Enter equipment name">
                        <p class="mt-1 text-xs text-gray-500">Supports wildcard search (e.g., "Phone*")</p>
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Test Endpoint
                    </button>
                </form>
            </div>

            <!-- Facility Equipment Test -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Test: Facility Equipment Search</h3>
                <form id="testFacilityEquipment" class="space-y-4">
                    <div>
                        <label for="search_type" class="block text-sm font-medium text-gray-700 mb-1">Search Type</label>
                        <select id="search_type" name="search_type" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="master">Master Equipment ID</option>
                            <option value="parent">Parent Equipment ID</option>
                            <option value="name">Equipment Name</option>
                            <option value="mapping">Mapping ID</option>
                        </select>
                    </div>
                    <div>
                        <label for="search_value" class="block text-sm font-medium text-gray-700 mb-1">Search Value</label>
                        <input type="text" id="search_value" name="search_value" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Enter search value">
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Test Endpoint
                    </button>
                </form>
            </div>

            <!-- Custom Endpoint Test -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Test: Custom Endpoint</h3>
                <form id="testCustomEndpoint" class="space-y-4">
                    <div>
                        <label for="endpoint" class="block text-sm font-medium text-gray-700 mb-1">Endpoint Path</label>
                        <input type="text" id="endpoint" name="endpoint" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="/equipment?equipmentId=123">
                        <p class="mt-1 text-xs text-gray-500">Base: https://hay.cloud.coop/services/secured</p>
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        Test Custom Endpoint
                    </button>
                </form>
            </div>
        </div>

        <!-- Response Display Column -->
        <div class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Response</h3>
                    <button id="clearResponse" class="text-sm text-red-600 hover:text-red-700">Clear</button>
                </div>

                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="hidden text-center py-8">
                    <svg class="animate-spin h-8 w-8 mx-auto text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">Testing API endpoint...</p>
                </div>

                <!-- Response Display -->
                <div id="responseContainer" class="space-y-4">
                    <div id="urlDisplay" class="hidden">
                        <h4 class="text-sm font-semibold text-gray-700 mb-1">Request URL:</h4>
                        <div class="bg-gray-100 rounded p-3 text-xs font-mono overflow-x-auto"></div>
                    </div>

                    <div id="statusDisplay" class="hidden">
                        <h4 class="text-sm font-semibold text-gray-700 mb-1">Status:</h4>
                        <div class="flex items-center gap-2">
                            <span class="status-badge"></span>
                        </div>
                    </div>

                    <div id="jsonDisplay" class="hidden">
                        <h4 class="text-sm font-semibold text-gray-700 mb-1">JSON Response:</h4>
                        <pre class="bg-gray-900 text-green-400 rounded p-4 text-xs overflow-x-auto max-h-96"></pre>
                    </div>

                    <div id="errorDisplay" class="hidden">
                        <h4 class="text-sm font-semibold text-red-700 mb-1">Error:</h4>
                        <div class="bg-red-50 border border-red-200 rounded p-3 text-sm text-red-800"></div>
                    </div>

                    <div id="emptyState" class="text-center py-12 text-gray-400">
                        <svg class="h-12 w-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-sm">Test an endpoint to see the response</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = {
        testEquipmentById: {
            form: document.getElementById('testEquipmentById'),
            url: '{{ route("inventory.test-equipment-by-id") }}'
        },
        testEquipmentByName: {
            form: document.getElementById('testEquipmentByName'),
            url: '{{ route("inventory.test-equipment-by-name") }}'
        },
        testFacilityEquipment: {
            form: document.getElementById('testFacilityEquipment'),
            url: '{{ route("inventory.test-facility-equipment") }}'
        },
        testCustomEndpoint: {
            form: document.getElementById('testCustomEndpoint'),
            url: '{{ route("inventory.test-custom-endpoint") }}'
        }
    };

    // Setup form handlers
    Object.values(forms).forEach(({form, url}) => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            testEndpoint(url, formData);
        });
    });

    // Clear button
    document.getElementById('clearResponse').addEventListener('click', function() {
        clearResponse();
    });

    function testEndpoint(url, formData) {
        showLoading();

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            displayResponse(data);
        })
        .catch(error => {
            hideLoading();
            displayError('Network error: ' + error.message);
        });
    }

    function showLoading() {
        document.getElementById('loadingIndicator').classList.remove('hidden');
        document.getElementById('responseContainer').classList.add('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingIndicator').classList.add('hidden');
        document.getElementById('responseContainer').classList.remove('hidden');
    }

    function clearResponse() {
        document.getElementById('emptyState').classList.remove('hidden');
        document.getElementById('urlDisplay').classList.add('hidden');
        document.getElementById('statusDisplay').classList.add('hidden');
        document.getElementById('jsonDisplay').classList.add('hidden');
        document.getElementById('errorDisplay').classList.add('hidden');
    }

    function displayResponse(response) {
        // Hide empty state
        document.getElementById('emptyState').classList.add('hidden');

        if (response.success) {
            // Show URL
            const urlDisplay = document.getElementById('urlDisplay');
            urlDisplay.querySelector('div').textContent = response.url;
            urlDisplay.classList.remove('hidden');

            // Show status
            const statusDisplay = document.getElementById('statusDisplay');
            const statusBadge = statusDisplay.querySelector('.status-badge');
            statusBadge.className = 'status-badge px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800';
            statusBadge.textContent = 'Success';
            statusDisplay.classList.remove('hidden');

            // Show JSON
            const jsonDisplay = document.getElementById('jsonDisplay');
            jsonDisplay.querySelector('pre').textContent = response.raw;
            jsonDisplay.classList.remove('hidden');

            // Hide error
            document.getElementById('errorDisplay').classList.add('hidden');
        } else {
            // Show URL if available
            if (response.url) {
                const urlDisplay = document.getElementById('urlDisplay');
                urlDisplay.querySelector('div').textContent = response.url;
                urlDisplay.classList.remove('hidden');
            }

            // Show error status
            const statusDisplay = document.getElementById('statusDisplay');
            const statusBadge = statusDisplay.querySelector('.status-badge');
            statusBadge.className = 'status-badge px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800';
            statusBadge.textContent = 'Error' + (response.status_code ? ' (' + response.status_code + ')' : '');
            statusDisplay.classList.remove('hidden');

            // Show error message
            const errorDisplay = document.getElementById('errorDisplay');
            let errorText = response.error || 'Unknown error occurred';
            if (response.response_body) {
                errorText += '\n\nResponse Body:\n' + response.response_body;
            }
            errorDisplay.querySelector('div').textContent = errorText;
            errorDisplay.classList.remove('hidden');

            // Hide JSON
            document.getElementById('jsonDisplay').classList.add('hidden');
        }
    }

    function displayError(message) {
        document.getElementById('emptyState').classList.add('hidden');

        const statusDisplay = document.getElementById('statusDisplay');
        const statusBadge = statusDisplay.querySelector('.status-badge');
        statusBadge.className = 'status-badge px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800';
        statusBadge.textContent = 'Error';
        statusDisplay.classList.remove('hidden');

        const errorDisplay = document.getElementById('errorDisplay');
        errorDisplay.querySelector('div').textContent = message;
        errorDisplay.classList.remove('hidden');

        document.getElementById('urlDisplay').classList.add('hidden');
        document.getElementById('jsonDisplay').classList.add('hidden');
    }
});
</script>
@endsection
