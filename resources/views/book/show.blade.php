<!-- resources/views/book/show.blade.php -->
@extends('layouts.app')

@section('title', 'Detail Buku - ' . $book->title)

@section('content')
<div class="max-w-4xl mx-auto mt-10">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-3xl font-bold mb-4">{{ $book->title }}</h2>
        
        <div class="mb-4">
            <span class="font-medium">Penulis:</span> {{ $book->author }}
        </div>
        
        <div class="mb-4">
            <span class="font-medium">Kategori:</span> {{ $book->category }}
        </div>

        <div class="mb-4">
            <span class="font-medium">Deskripsi:</span> 
            <p>{{ $book->description }}</p>
        </div>

        <div class="flex justify-between mt-6">
            <a href="{{ route('available') }}" class="text-blue-600 hover:text-blue-800">Kembali ke Daftar Buku</a>
            <button class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Pinjam Buku</button>
        </div>
    </div>
</div>
@endsection
