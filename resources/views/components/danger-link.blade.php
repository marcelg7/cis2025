<a {{ $attributes->merge([
    'class' => 'inline-flex items-center px-3 py-1 border border-transparent rounded-md font-medium uppercase tracking-wider transition-colors duration-150 ' . 
        'bg-[' . get_user_component_style('danger-link', 'background', '#fee2e2') . '] ' .
        'hover:bg-[' . get_user_component_style('danger-link', 'hover', '#991b1b') . '] ' .
        'text-[' . get_user_component_style('danger-link', 'text', '#b91c1c') . '] ' .
        'text-' . get_user_component_style('danger-link', 'text_size', 'xs') . ' ' .
        'focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-red-500'
]) }}>
    {{ $slot }}
</a>