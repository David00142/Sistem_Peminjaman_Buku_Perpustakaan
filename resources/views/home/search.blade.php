@extends('layouts.app')

@section('title', 'Hasil Pencarian - BOBOOK')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Hasil Pencarian</h1>
        
        @if(request('search'))
            <p class="text-gray-600">
                Menampilkan hasil untuk: "<span class="font-semibold">{{ request('search') }}</span>"
            </p>
        @endif
    </div>

    @if(request('search') && $books->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($books as $book)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
 {{-- Ganti div p-4 di search.blade.php dengan ini --}}
<div class="p-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $book->title }}</h3>
    <p class="text-sm text-gray-600 mb-1"><span class="font-medium">Penulis:</span> {{ $book->author }}</p>
    <p class="text-sm text-gray-600 mb-1"><span class="font-medium">Penerbit:</span> {{ $book->publisher }}</p>
    <p class="text-sm text-gray-600 mb-1"><span class="font-medium">Kategori:</span> {{ $book->category }}</p>
    <p class="text-sm text-gray-600 mb-3"><span class="font-medium">ISBN:</span> {{ $book->isbn }}</p>
    
    <div class="flex justify-between items-center">
        @php
            // Logika Ketersediaan DARI home.blade.php
            $available = $book->quantity - $book->borrowed - $book->booked;
        @endphp
        @if($available > 0)
            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">
                Tersedia ({{ $available }})
            </span>
        @else
            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded">
                Tidak Tersedia
            </span>
        @endif

        @auth
            @if($available > 0)
                <form action="{{ route('book.show-search', $book->id) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="px-3 py-1 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition-colors duration-200">
                        Pinjam
                    </button>
                </form>
            @else
                <button disabled class="px-3 py-1 bg-gray-400 text-white text-sm font-medium rounded cursor-not-allowed">
                    Habis
                </button>
            @endif
        @endauth
    </div>
</div>
            @endforeach
        </div>
    @elseif(request('search'))
        <div class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Tidak ada buku yang ditemukan</h3>
            <p class="text-gray-500">Coba dengan kata kunci yang berbeda atau periksa ejaan Anda.</p>
        </div>
    @else
        <div class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Masukkan kata kunci pencarian</h3>
            <p class="text-gray-500">Gunakan form search di atas untuk mencari buku.</p>
        </div>
    @endif
</div>
@endsection