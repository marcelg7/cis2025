<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center px-3 py-1 border border-transparent rounded-md font-medium uppercase tracking-wider transition-all duration-150 ' . 
        'bg-[' . get_user_component_style('danger-button-submit', 'background', '#fee2e2') . '] ' .
        'hover:bg-[' . get_user_component_style('danger-button-submit', 'hover', '#dc2626') . '] ' .
        'text-[' . get_user_component_style('danger-button-submit', 'text', '#b91c1c') . '] ' .
        'text-' . get_user_component_style('danger-button-submit', 'text_size', 'xs') . ' ' .
        'hover:text-white hover:scale-105 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-red-500'
]) }}>
    {{ $slot }}
</button>