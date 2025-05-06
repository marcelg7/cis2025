<x-app-layout>
    <h1>Mobile Devices</h1>
    <ul>
        @foreach ($devices as $device)
            <li>
                <h2>{{ $device->post_title }}</h2>
                <p>{!! $device->post_content !!}</p>
                @if ($device->meta->price)
                    <p>Price: {{ $device->meta->price }}</p>
                @endif
            </li>
        @endforeach
    </ul>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
</x-app-layout>