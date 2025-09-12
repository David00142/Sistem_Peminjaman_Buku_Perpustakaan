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
                        <th class="py-3 px-6 text-left">Pemesan</th>
                        <th class="py-3 px-6 text-left">Durasi Diminta</th>
                        <th class="py-3 px-6 text-left">Tanggal Pemesanan</th>
                        <th class="py-3 px-6 text-left">Batas Pengambilan</th>
                        <th class="py-3 px-6 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($bookedBooks as $borrow)
                    <tr class="hover:bg-gray-50">
                        <td class="py-4 px-6">
                            <div class="font-medium">{{ $borrow->book->title }}</div>
                            <div class="text-sm text-gray-500">Oleh: {{ $borrow->book->author }}</div>
                            <div class="text-sm text-gray-500">Kategori: {{ $borrow->book->category }}</div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="font-medium">{{ $borrow->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $borrow->user->email }}</div>
                            @if($borrow->user->kelas)
                            <div class="text-sm text-gray-500">Kelas: {{ $borrow->user->kelas }}</div>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            @if($borrow->requested_days)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                    {{ $borrow->requested_days }} hari
                                </span>
                            @else
                                <span class="text-gray-500 text-sm">Tidak ditentukan</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            {{ $borrow->created_at->format('d M Y H:i') }}
                        </td>
@php
    $expiryDate = $borrow->created_at->addDays(2);
    $isExpired = now()->greaterThan($expiryDate); // Ini bukan penyebab error.
@endphp
<td class="py-4 px-6">
    @php
        $expiryDate = $borrow->created_at->addDays(2);
        $isExpired = now()->greaterThan($expiryDate); // Ini juga bukan penyebab error.
    @endphp
    <span class="{{ $isExpired ? 'text-red-600 font-medium' : 'text-gray-700' }}">
        {{ $expiryDate->format('d M Y H:i') }}
    </span>
    @if($isExpired)
    <div class="text-xs text-red-500 mt-1">Telah melewati batas</div>
    @endif
</td>
                        <td class="py-4 px-6">
                            <div class="flex flex-col space-y-2"> <!-- Kembalikan ke flex-col untuk tampilan lebih rapi -->
                                <!-- Form Konfirmasi Peminjaman -->
                                <form action="{{ route('admin.books.confirm-borrow', $borrow->id) }}" method="POST" class="flex items-center space-x-2">
                                    @csrf
                                    <select name="duration_days" class="w-20 px-2 py-1 border rounded text-sm" required>
                                        <!-- Tampilkan opsi default sesuai yang diminta user -->
                                        @if($borrow->requested_days)
                                        <option value="{{ $borrow->requested_days }}" selected>
                                            {{ $borrow->requested_days }} hari
                                        </option>
                                        @else
                                        <option value="" selected disabled>Pilih durasi</option>
                                        @endif
                                        <option value="3">3 hari</option>
                                        <option value="7">7 hari</option>
                                        <option value="14">14 hari</option>
                                        <option value="21">21 hari</option>
                                        <option value="30">30 hari</option>
                                    </select>
                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm whitespace-nowrap">
                                        Konfirmasi
                                    </button>
                                </form>
                                
                                <!-- Form Batalkan Pemesanan -->
                                <form action="{{ route('admin.books.cancel-booking', $borrow->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm whitespace-nowrap w-full text-center" 
                                            onclick="return confirm('Apakah Anda yakin ingin membatalkan pemesanan ini?')">
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

        <!-- Pagination -->
        @if($bookedBooks->hasPages())
        <div class="mt-6">
            {{ $bookedBooks->links() }}
        </div>
        @endif
    @endif
</div>

@endsection