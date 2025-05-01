<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-3 py-1 bg-red-100 border border-transparent rounded-md font-medium text-xs text-red-700 uppercase tracking-wider hover:bg-red-600 hover:text-white hover:scale-105 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-red-500 transition-all duration-150']) }}>
    {{ $slot }}
</button>
