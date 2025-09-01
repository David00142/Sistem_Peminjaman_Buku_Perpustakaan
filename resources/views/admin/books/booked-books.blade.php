@extends('layouts.admin-layout')

@section('title', 'Buku yang Dipesan')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-center mb-8">Buku yang Dipesan</h2>

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

    @if($bookedBooks->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Tidak ada buku yang sedang dipesan.</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-6 text-left">Judul Buku</th>
                        <th class="py-3 px-6 text-left">Penulis</th>
                        <th class="py-3 px-6 text-left">Kategori</th>
                        <th class="py-3 px-6 text-left">Jumlah Dipesan</th>
                        <th class="py-3 px-6 text-left">Stok Tersedia</th>
                        <th class="py-3 px-6 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($bookedBooks as $book)
                    <tr class="hover:bg-gray-50">
                        <td class="py-4 px-6">{{ $book->title }}</td>
                        <td class="py-4 px-6">{{ $book->author }}</td>
                        <td class="py-4 px-6">{{ $book->category }}</td>
                        <td class="py-4 px-6">{{ $book->booked }}</td>
                        <td class="py-4 px-6">{{ $book->quantity - $book->borrowed - $book->booked }}</td>
                        <td class="py-4 px-6">
                            <div class="flex space-x-2">
                                <form action="{{ route('admin.books.confirm-borrow', $book->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="1"> <!-- Ganti dengan user_id yang sesuai -->
                                    <input type="number" name="duration_days" value="7" min="1" max="30" class="w-16 px-2 py-1 border rounded">
                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                                        Konfirmasi
                                    </button>
                                </form>
                                <form action="{{ route('admin.books.cancel-booking', $book->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                        Batalkan
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
