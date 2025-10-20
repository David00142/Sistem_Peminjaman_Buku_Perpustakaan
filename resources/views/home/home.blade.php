@extends('layouts.app')

@section('title', 'Home - BOBOOK')

@section('content')
<div class="max-w-6xl mx-auto flex flex-col min-h-screen"> {{-- TAMBAHKAN FLEX DISINI --}}
    
    <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-lg shadow-md p-6 mb-8 text-white">
        <h1 class="text-3xl font-bold mb-2">Selamat Datang, {{ Auth::user()->name }}!</h1>
        <p class="text-lg">Akses ribuan buku koleksi perpustakaan kami secara mudah.</p>
    </div>


    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 flex items-center">
            <div class="rounded-full bg-blue-100 p-3 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
            </div>
            <div>
                @php
                    try {
                        $stats = App\Http\Controllers\BookController::getBookStats();
                        $totalBooks = $stats['total_books'];
                    } catch (Exception $e) {
                        $totalBooks = 0;
                    }
                @endphp
                <h3 class="text-2xl font-bold text-gray-800">{{ $totalBooks }}</h3>
                <p class="text-gray-600">Total Buku</p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 flex items-center">
            <div class="rounded-full bg-green-100 p-3 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div>
                @php
                    try {
                        $stats = app(App\Http\Controllers\BookController::class)->getBookStats();
                        $availableBooks = $stats['available_books'];
                    } catch (Exception $e) {
                        $availableBooks = 0;
                    }
                @endphp
                <h3 class="text-2xl font-bold text-gray-800">{{ $availableBooks }}</h3>
                <p class="text-gray-600">Buku Tersedia</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 flex items-center">
            <div class="rounded-full bg-red-100 p-3 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                @php
                    $borrowedBooks = app(App\Http\Controllers\BookController::class)->getBorrowedBooks();
                    $borrowedCount = $borrowedBooks->count();
                @endphp
                <h3 class="text-2xl font-bold text-gray-800">{{ $borrowedCount }}</h3>
                <p class="text-gray-600">Buku Dipinjam</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 flex items-center">
            <div class="rounded-full bg-purple-100 p-3 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div>
                <a href="{{ route('available') }}" class="text-lg font-bold text-gray-800 hover:text-blue-600 block">Jelajahi Buku</a>
                <p class="text-gray-600">Temukan buku baru</p>
            </div>
        </div>
    </div>

    <!-- Konten lainnya akan otomatis terdorong ke bawah -->
    <div class="flex-grow"> {{-- FLEX-GROW UNTUK MENGISI SISA RUANG --}}
        @php
            $borrowedBooks = app(App\Http\Controllers\BookController::class)->getBorrowedBooks();
        @endphp
        
        @if($borrowedBooks->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Buku yang Sedang Dipinjam</h2>
                <a href="{{ route('history.show') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Lihat Riwayat Lengkap</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Buku</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pinjam</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batas Kembali</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($borrowedBooks as $borrow)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $borrow->book->title }}</div>
                                <div class="text-sm text-gray-500">{{ $borrow->book->author }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $borrow->borrow_date->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 {{ $borrow->return_date->isPast() ? 'text-red-600 font-semibold' : '' }}">
                                    {{ $borrow->return_date->format('d M Y') }}
                                </div>
                                @if($borrow->return_date->isPast())
                                <div class="text-xs text-red-500">Terlambat</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $borrow->return_date->isPast() ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $borrow->return_date->isPast() ? 'Terlambat' : 'Dipinjam' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Terbaru</h2>
            <div class="space-y-4">
                @php
                    $recentActivities = collect([]); 
                    try {
                        $recentActivities = app(App\Http\Controllers\BookController::class)->getUserRecentActivity(Auth::id(), 5);
                    } catch (Exception $e) {
                        // Tetap kosong jika error
                    }
                @endphp

                @if($recentActivities->isEmpty())
                    <div class="text-center py-4">
                        <p class="text-gray-500">Belum ada aktivitas peminjaman</p>
                        <p class="text-sm text-gray-400 mt-1">Mulai dengan meminjam buku pertama Anda</p>
                    </div>
                @else
                    @foreach($recentActivities as $activity)
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-2 rounded-full mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    @if($activity->status == 'booked')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                    @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    @endif
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">
                                    {{ $activity->status == 'booked' ? 'Pemesanan Buku' : 'Peminjaman Buku' }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ $activity->book->title }} - 
                                    {{ $activity->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <span class="bg-{{ $activity->status == 'booked' ? 'yellow' : 'green' }}-100 text-{{ $activity->status == 'booked' ? 'yellow' : 'green' }}-800 text-xs font-medium px-2.5 py-0.5 rounded">
                            {{ $activity->status == 'booked' ? 'Diproses' : 'Aktif' }}
                        </span>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Buku Populer</h2>
                <a href="{{ route('available') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Lihat Semua</a>
            </div>
            
            @php
                $popularBooks = collect([]); 
                try {
                    $popularBooks = app(App\Http\Controllers\BookController::class)->getPopularBooks(6);
                } catch (Exception $e) {
                    // Tetap kosong jika error
                }
            @endphp
            
            @if($popularBooks->isEmpty())
                <div class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                    <p class="text-gray-500">Belum ada data buku populer</p>
                    <p class="text-sm text-gray-400 mt-1">Buku akan muncul di sini setelah ada yang dipinjam</p>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                    @foreach($popularBooks as $book)
                        <a href="{{ route('book.show', $book->id) }}" class="border rounded-lg p-2 hover:shadow-md transition-shadow block group flex flex-col items-center text-center">
                            <div class="h-40 w-full bg-gray-200 rounded mb-3 flex items-center justify-center overflow-hidden">
                                @if($book->image && Storage::disk('public')->exists($book->image))
                                    <img src="{{ asset('storage/' . $book->image) }}" alt="{{ $book->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                    </svg>
                                @endif
                            </div>
                            <h3 class="font-bold text-lg mb-1 truncate w-full">{{ $book->title }}</h3>
                            <p class="text-gray-600 text-sm mb-2 truncate w-full">{{ $book->author }}</p>
                            <div class="flex flex-col items-center">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded mb-2">{{ $book->category }}</span>
                                <span class="text-yellow-500 flex items-center">
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
                                    <span class="ml-1 text-gray-600 text-sm">({{ $book->borrowed }})</span>
                                </span>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                @php
                                    $available = $book->quantity - $book->borrowed - $book->booked;
                                @endphp
                                @if($available > 0)
                                    <span class="text-green-600">✓ {{ $available }} Tersedia</span>
                                @else
                                    <span class="text-red-600">✗ Tidak Tersedia</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection