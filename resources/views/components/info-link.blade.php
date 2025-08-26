@props(['href'])
<a {{ $attributes->merge(['class' => 'inline-flex items-center border border-transparent rounded-md font-medium uppercase tracking-wider transition-colors duration-150 bg-[#dbeafe] hover:bg-[#bfdbfe] text-[#1e40af] text-xs focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500']) }} href="{{ $href }}">
    {{ $slot }}
</a>