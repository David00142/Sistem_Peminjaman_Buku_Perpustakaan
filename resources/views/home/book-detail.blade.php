@extends('layouts.app')

@section('title', $book->title . ' - BOBOOK')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Book Image -->
        <div class="flex justify-center">
            <div class="w-64 h-96 bg-gray-200 rounded-lg flex items-center justify-center overflow-hidden">
                @if($book->image && Storage::disk('public')->exists($book->image))
                    <img src="{{ asset('storage/' . $book->image) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                @endif
            </div>
        </div>

        <!-- Book Details -->
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $book->title }}</h1>
            <p class="text-lg text-gray-600 mb-4">oleh {{ $book->author }}</p>
            
            <div class="mb-4">
                <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">{{ $book->category }}</span>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2">Deskripsi</h3>
                <p class="text-gray-700">{{ $book->description }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">Total Copy</p>
                    <p class="text-xl font-bold">{{ $book->quantity }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">Tersedia</p>
                    <p class="text-xl font-bold text-green-600">{{ $book->quantity - $book->borrowed - $book->booked }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">Dipinjam</p>
                    <p class="text-xl font-bold">{{ $book->borrowed }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">Dipesan</p>
                    <p class="text-xl font-bold">{{ $book->booked }}</p>
                </div>
            </div>

            @if($book->quantity > ($book->borrowed + $book->booked))
                <form action="{{ route('book.booking', $book->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                        📚 Booking Buku Ini
                    </button>
                    <p class="text-sm text-gray-500 mt-2 text-center">Buku akan dipesan untuk Anda selama 2 hari</p>
                </form>
            @else
                <button disabled class="w-full bg-gray-400 text-white font-bold py-3 px-4 rounded-lg cursor-not-allowed">
                    ❌ Tidak Tersedia
                </button>
                <p class="text-sm text-red-500 mt-2 text-center">Semua copy sedang dipinjam atau dipesan</p>
            @endif

            <div class="mt-4">
                <a href="{{ route('home') }}" class="text-blue-500 hover:text-blue-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection