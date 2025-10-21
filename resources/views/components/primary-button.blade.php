@props(['type' => 'submit'])

<button {{ $attributes->merge([
    'type' => $type, 
    'class' => 'inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest transition ease-in-out duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2'
]) }}
    style="background-color: var(--color-primary); color: white;"
    onmouseover="this.style.backgroundColor='var(--color-primary-hover)'"
    onmouseout="this.style.backgroundColor='var(--color-primary)'">
    {{ $slot }}
</button>