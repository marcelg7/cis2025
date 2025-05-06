<a {{ $attributes->merge([
    'class' => 'inline-flex items-center px-3 py-1 border border-transparent rounded-md font-medium uppercase tracking-wider transition-colors duration-150 ' . 
        'bg-[' . get_user_component_style('warning-link', 'background', '#fef3c7') . '] ' .
        'hover:bg-[' . get_user_component_style('warning-link', 'hover', '#fde68a') . '] ' .
        'text-[' . get_user_component_style('warning-link', 'text', '#b45309') . '] ' .
        'text-' . get_user_component_style('warning-link', 'text_size', 'xs') . ' ' .
        'focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-yellow-500'
]) }}>
    {{ $slot }}
</a>