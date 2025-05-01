<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-3 py-1 bg-yellow-100 border border-transparent rounded-md font-medium text-xs text-yellow-700 uppercase tracking-wider hover:bg-yellow-200 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-yellow-500 transition-colors duration-150']) }}>
    {{ $slot }}
</button>
