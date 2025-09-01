@extends('layouts.app')

@section('title', 'Available Books')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-semibold text-gray-800 mb-6">Available Books</h1>

        @if($books->isEmpty())
            <p class="text-gray-500">No books available at the moment.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($books as $book)
                    <div class="bg-white shadow-md rounded-lg p-4">
                        <h2 class="text-xl font-semibold text-gray-800">{{ $book->title }}</h2>
                        <p class="text-gray-600">By: {{ $book->author }}</p>
                        <p class="text-sm text-gray-500 mt-2">{{ $book->description }}</p>
                        <p class="text-sm font-medium text-gray-700 mt-2">Available: {{ $book->quantity }} copies</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
