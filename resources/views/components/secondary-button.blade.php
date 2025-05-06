<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-medium uppercase tracking-wider transition-colors duration-150 ' . 
        'bg-[' . get_user_component_style('secondary-button', 'background', '#ffffff') . '] ' .
        'hover:bg-[' . get_user_component_style('secondary-button', 'hover', '#f9fafb') . '] ' .
        'text-[' . get_user_component_style('secondary-button', 'text', '#374151') . '] ' .
        'text-' . get_user_component_style('secondary-button', 'text_size', 'xs') . ' ' .
        'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500'
]) }}>
    {{ $slot }}
</button>