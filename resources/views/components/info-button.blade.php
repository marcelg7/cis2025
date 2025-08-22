<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center px-3 py-1 border border-transparent rounded-md font-medium uppercase tracking-wider transition-colors duration-150 ' .
        'bg-[' . get_user_component_style('info-button', 'background', '#dbeafe') . '] ' .
        'hover:bg-[' . get_user_component_style('info-button', 'hover', '#bfdbfe') . '] ' .
        'text-[' . get_user_component_style('info-button', 'text', '#1e40af') . '] ' .
        'text-' . get_user_component_style('info-button', 'text_size', 'xs') . ' ' .
        'focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500'
]) }}>
    {{ $slot }}
</button>