<a {{ $attributes->merge([
    'class' => 'inline-flex items-center px-3 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest transition ease-in-out duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500'
]) }}
    style="background-color: var(--color-warning); color: white;"
    onmouseover="this.style.backgroundColor='#d97706'"
    onmouseout="this.style.backgroundColor='var(--color-warning)'">
    {{ $slot }}
</a>