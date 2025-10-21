@props(['type' => 'button'])

<button {{ $attributes->merge([
    'type' => $type,
    'class' => 'inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest transition ease-in-out duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500'
]) }}
    style="background-color: var(--color-danger); color: white;"
    onmouseover="this.style.backgroundColor='#dc2626'"
    onmouseout="this.style.backgroundColor='var(--color-danger)'">
    {{ $slot }}
</button>