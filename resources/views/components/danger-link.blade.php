<a {{ $attributes->merge(['class' => 'inline-flex items-center px-3 py-1 bg-red-100 border border-transparent rounded-md font-medium text-xs text-red-700 uppercase tracking-wider hover:bg-red-900 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-red-500 transition-colors duration-150']) }}>
    {{ $slot }}
</a>
