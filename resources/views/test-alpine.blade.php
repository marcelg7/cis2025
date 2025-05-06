<!DOCTYPE html>
<html>
<head>
    <title>Alpine.js Test</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js" defer></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body>
    <div class="p-6">
        <h1>Alpine.js Test</h1>
        <div x-data="{ showStyles: false }" x-init="console.log('x-data initialized', $data)" class="mb-4">
            <button type="button" x-on:click="showStyles = !showStyles" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-medium text-sm text-gray-700 hover:bg-gray-200 focus:outline-none">
                <span x-text="showStyles ? 'Hide Test Content' : 'Show Test Content'"></span>
            </button>
        </div>
        <div x-show="showStyles" class="p-4 bg-gray-50">
            <p>Test Content: This should toggle.</p>
        </div>
    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            console.log('Alpine:init fired');
            document.querySelectorAll('[x-data]').forEach(el => {
                console.log('Found x-data:', el.outerHTML, el.__x ? 'Bound' : 'Not bound');
            });
        });
    </script>
</body>
</html>