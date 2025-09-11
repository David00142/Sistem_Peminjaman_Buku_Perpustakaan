@extends('layouts.admin-layout')

@section('title', 'Buku yang Dipinjam')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Daftar Buku yang Sedang Dipinjam</h1>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if($borrowedBooks->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Tidak ada buku yang sedang dipinjam.</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-6 text-left">Judul Buku</th>
                        <th class="py-3 px-6 text-left">Peminjam</th>
                        <th class="py-3 px-6 text-left">Tanggal Pinjam</th>
                        <th class="py-3 px-6 text-left">Batas Kembali</th>
                        <th class="py-3 px-6 text-left">Status</th>
                        <th class="py-3 px-6 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrowedBooks as $borrow)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-4 px-6">{{ $borrow->book->title }}</td>
                        <td class="py-4 px-6">{{ $borrow->user->name }}</td>
                        <td class="py-4 px-6">{{ \Carbon\Carbon::parse($borrow->borrow_date)->format('d M Y') }}</td>
                        <td class="py-4 px-6">{{ \Carbon\Carbon::parse($borrow->due_date)->format('d M Y') }}</td>
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                Dipinjam
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <form action="{{ route('admin.books.return-book', $borrow->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                                    Kembalikan
                                </button>
                            </form>
                            <form action="{{ route('admin.books.extend-borrow', $borrow->id) }}" method="POST" class="inline ml-2">
                                @csrf
                                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">
                                    Perpanjang
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
    @endif
</div>
@endsection