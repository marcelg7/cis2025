@props(['href'])
<a {{ $attributes->merge(['class' => 'inline-flex items-center px-4 py-2 border border-transparent rounded-md font-medium uppercase tracking-wider transition-colors duration-150 bg-[#4f46e5] hover:bg-[#4338ca] text-[#ffffff] text-xs focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500']) }} href="{{ $href }}">
    {{ $slot }}
</a>