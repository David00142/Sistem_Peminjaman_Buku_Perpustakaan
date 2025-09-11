@extends('layouts.app')

@section('title', 'Available Books')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-semibold text-gray-800 mb-6">Available Books</h1>

        @if($books->isEmpty())
            <div class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
                <p class="text-gray-500 text-lg">No books available at the moment.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($books as $book)
                    <div class="bg-white shadow-md rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <!-- Book Image -->
                        <div class="h-48 bg-gray-200 overflow-hidden">
                            @if($book->image && Storage::disk('public')->exists($book->image))
                                <img src="{{ asset('storage/' . $book->image) }}" 
                                     alt="{{ $book->title }}" 
                                     class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Book Details -->
                        <div class="p-4">
                            <h2 class="text-xl font-semibold text-gray-800 mb-2 line-clamp-2">{{ $book->title }}</h2>
                            <p class="text-gray-600 text-sm mb-2">By: {{ $book->author }}</p>
                            
                            <div class="flex items-center justify-between mb-3">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                    {{ $book->category }}
                                </span>
                            <span class="text-yellow-500">
                                @for($i = 1; $i <= 5; $i++)
                                @if($book->borrowed == 2)
                                      ★
                                    @else
                                        @if($i <= min(5, ceil($book->borrowed / 2)))
                                            ★
                                        @else
                                            ☆
                                        @endif
                                    @endif
                                @endfor
                                ({{ $book->borrowed }})
                            </span>
                            </div>
                            
                            <p class="text-sm text-gray-600 mb-3 line-clamp-3">{{ Str::limit($book->description, 100) }}</p>
                            
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">
                                        Available: {{ $book->quantity - $book->borrowed - $book->booked }} / {{ $book->quantity }}
                                    </span>
                                    @if(($book->quantity - $book->borrowed - $book->booked) > 0)
                                        <span class="text-green-600 text-xs block">✓ In Stock</span>
                                    @else
                                        <span class="text-red-600 text-xs block">✗ Out of Stock</span>
                                    @endif
                                </div>
                                
                                <a href="{{ route('book.show', $book->id) }}" 
                                   class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">
                                    Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($books->hasPages())
                <div class="mt-8">
                    {{ $books->links() }}
                </div>
            @endif
        @endif
    </div>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
@endsection