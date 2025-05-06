<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center px-4 py-2 border border-transparent rounded-md font-medium uppercase tracking-wider transition-colors duration-150 ' . 
        'bg-[' . get_user_component_style('primary-button', 'background', '#4f46e5') . '] ' .
        'hover:bg-[' . get_user_component_style('primary-button', 'hover', '#4338ca') . '] ' .
        'text-[' . get_user_component_style('primary-button', 'text', '#ffffff') . '] ' .
        'text-' . get_user_component_style('primary-button', 'text_size', 'xs') . ' ' .
        'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500'
]) }}>
    {{ $slot }}
</button>