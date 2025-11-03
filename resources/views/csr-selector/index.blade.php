@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-bold mb-6 text-center">Who is using this device?</h2>

                    @if(session('success'))
                        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($activeCsr)
                        <div class="mb-6 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative">
                            <p class="font-semibold">Currently working as: {{ $activeCsr->name }}</p>
                            <p class="text-sm">Tap a different name below to switch users, or continue to the dashboard.</p>
                        </div>

                        <div class="mb-6 flex gap-4 justify-center">
                            <a href="{{ route('customers.index') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-lg">
                                Continue to Dashboard
                            </a>
                            <form method="POST" action="{{ route('csr-selector.clear') }}" class="inline">
                                @csrf
                                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg text-lg">
                                    Clear Selection
                                </button>
                            </form>
                        </div>
                    @else
                        <p class="text-center text-gray-600 mb-6">Select your name to begin</p>
                    @endif

                    <!-- CSR Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($csrs as $csr)
                            <form method="POST" action="{{ route('csr-selector.select') }}" class="contents">
                                @csrf
                                <input type="hidden" name="csr_id" value="{{ $csr->id }}">
                                <button type="submit"
                                    class="bg-white hover:bg-blue-50 border-2
                                        {{ $activeCsr && $activeCsr->id === $csr->id ? 'border-blue-600 bg-blue-50' : 'border-gray-300' }}
                                        rounded-lg p-6 text-center transition-all hover:shadow-lg active:scale-95">
                                    <!-- Avatar/Initials -->
                                    <div class="w-20 h-20 mx-auto mb-3 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                                        {{ strtoupper(substr($csr->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $csr->name)[1] ?? '', 0, 1)) }}
                                    </div>
                                    <!-- Name -->
                                    <p class="font-semibold text-gray-900 text-lg">{{ $csr->name }}</p>
                                    @if($activeCsr && $activeCsr->id === $csr->id)
                                        <p class="text-xs text-blue-600 mt-1 font-semibold">ACTIVE</p>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>

                    @if($csrs->isEmpty())
                        <div class="text-center text-gray-500 py-12">
                            <p class="text-lg">No CSRs available. Please contact an administrator.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
