@extends('layouts.app')

@section('title', 'Riwayat Peminjaman Buku')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Riwayat Peminjaman Buku Saya</h1>

        {{-- Pesan Sukses/Error --}}
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

        @if($borrowHistory->isEmpty())
            {{-- Bagian Jika Riwayat Kosong --}}
            <div class="text-center py-12 bg-white rounded-lg shadow-md">
                <div class="mb-4">
                    <svg class="w-16 h-16 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <p class="text-gray-500 text-lg mb-4">Anda belum pernah meminjam buku.</p>
                <a href="{{ route('available') }}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition duration-200">
                    Jelajahi Katalog Buku
                </a>
            </div>
        @else
            {{-- Mengelompokkan riwayat di sisi Blade --}}
            @php
                // 1. Filter yang sedang dipinjam (borrowed atau overdue)
                $activeBorrows = $borrowHistory->filter(function ($borrow) {
                    return $borrow->status === 'borrowed' || $borrow->status === 'overdue';
                })->sortByDesc(function ($borrow) {
                    // Sortir: Overdue (Terlambat) di paling atas
                    return $borrow->return_date && \Carbon\Carbon::parse($borrow->return_date)->isPast() ? 2 : 1; 
                });

                // 2. Filter yang sudah dikembalikan
                $returnedBorrows = $borrowHistory->filter(function ($borrow) {
                    return $borrow->status === 'returned';
                })->sortByDesc('actual_return_date');
            @endphp
            
            {{-- ------------------------------------------------ --}}
            {{-- BAGIAN 1: BUKU SEDANG DIPINJAM (TERMASUK DENDA AKTIF) --}}
            {{-- ------------------------------------------------ --}}
            <h2 class="text-xl font-semibold text-gray-700 mb-3 border-b pb-2">Buku Sedang Dipinjam & Terlambat</h2>
            
            @if($activeBorrows->isEmpty())
                <div class="text-center py-6 bg-yellow-50 rounded-lg shadow-sm mb-8">
                    <p class="text-gray-500">Saat ini tidak ada buku yang sedang Anda pinjam.</p>
                </div>
            @else
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Buku</th>
                                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pinjam</th>
                                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batas Kembali</th>
                                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Kembali</th>
                                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($activeBorrows as $borrow)
                                @php
                                    $isOverdueNow = $borrow->return_date && \Carbon\Carbon::parse($borrow->return_date)->isPast();
                                @endphp
                                <tr class="hover:bg-gray-50 transition duration-150 @if($isOverdueNow) bg-red-50 @endif">
                                    {{-- Kolom Judul Buku --}}
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $borrow->book->title }}</div>
                                                <div class="text-sm text-gray-500">Penulis: {{ $borrow->book->author }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    {{-- Kolom Tanggal Pinjam --}}
                                    <td class="py-4 px-6 whitespace-nowrap text-sm text-gray-900">
                                        {{ $borrow->borrow_date ? \Carbon\Carbon::parse($borrow->borrow_date)->format('d M Y') : '-' }}
                                    </td>
                                    {{-- Kolom Batas Kembali --}}
                                    <td class="py-4 px-6 whitespace-nowrap text-sm text-gray-900">
                                        {{ $borrow->return_date ? \Carbon\Carbon::parse($borrow->return_date)->format('d M Y') : '-' }}
                                    </td>
                                    {{-- Kolom Tanggal Kembali Aktual --}}
                                    <td class="py-4 px-6 whitespace-nowrap text-sm text-gray-900">
                                        {{ $borrow->actual_return_date ? \Carbon\Carbon::parse($borrow->actual_return_date)->format('d M Y') : 'Belum dikembalikan' }}
                                    </td>
                                    {{-- Kolom Status --}}
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        @if($isOverdueNow)
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Terlambat
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Dipinjam
                                            </span>
                                        @endif
                                    </td>
                                    {{-- Kolom Keterangan (Aktif) --}}
                                    <td class="py-4 px-6 whitespace-nowrap text-sm text-gray-900">
                                        @if($isOverdueNow)
    @php
        // Ambil Batas Kembali dan ubah ke awal hari (00:00:00)
        $dueDate = \Carbon\Carbon::parse($borrow->return_date)->startOfDay(); 
        
        // Ambil Hari Ini dan ubah ke awal hari (00:00:00)
        // Ini akan memastikan perhitungan hanya berdasarkan hari penuh (24 jam)
        $today = now()->startOfDay(); 
        
        // Hitung selisih hari penuh. Gunakan abs() untuk memastikan hasilnya positif.
        // Jika return_date 19 Oct, dan today 22 Oct: selisihnya 3 hari penuh.
        $lateDays = $today->diffInDays($dueDate);

        // Jika Anda ingin menampilkan 1 hari terlambat setelah 1 detik melewati batas:
        // Anda bisa menggunakan $lateDays = $today->diffInDays($dueDate) + 1; 
        // Namun, menggunakan startOfDay() sudah menghasilkan integer penuh.

    @endphp
    <span class="text-red-600">Terlambat {{ $lateDays }} hari</span>
    
    {{-- Tampilkan Total Denda Belum Dibayar (kode sisanya tetap) --}}
    @php
        $unpaidPenalties = $borrow->penalties->where('status', 'unpaid')->sum('amount');
    @endphp
    @if($unpaidPenalties > 0)
        <br><span class="text-red-700 font-bold">Denda: Rp{{ number_format($unpaidPenalties, 0, ',', '.') }} (Belum Bayar)</span>
    @endif
    
@elseif($borrow->return_date)
    {{-- (Kode untuk sisa hari jika belum terlambat) --}}
    <span class="text-green-600">Sisa {{ now()->diffInDays($borrow->return_date, false) }} hari</span>
@endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
            
            {{-- ------------------------------------------------- --}}
            {{-- BAGIAN 2: BUKU SUDAH DIKEMBALIKAN (RIWAYAT DENDA SELESAI) --}}
            {{-- ------------------------------------------------- --}}
            <h2 class="text-xl font-semibold text-gray-700 mb-3 border-b pb-2 mt-8">Buku Sudah Dikembalikan (Riwayat Selesai)</h2>
            
            @if($returnedBorrows->isEmpty())
                <div class="text-center py-6 bg-green-50 rounded-lg shadow-sm">
                    <p class="text-gray-500">Belum ada riwayat pengembalian buku.</p>
                </div>
            @else
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Buku</th>
                                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pinjam</th>
                                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batas Kembali</th>
                                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Kembali</th>
                                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($returnedBorrows as $borrow)
                                @php
                                    $wasLate = $borrow->actual_return_date && $borrow->return_date && \Carbon\Carbon::parse($borrow->actual_return_date)->greaterThan(\Carbon\Carbon::parse($borrow->return_date));
                                    $penalties = $borrow->penalties;
                                    $unpaidPenalties = $penalties->where('status', 'unpaid')->sum('amount');
                                    $paidPenalties = $penalties->where('status', 'paid')->sum('amount');
                                @endphp
                                <tr class="hover:bg-gray-50 transition duration-150 @if($wasLate) bg-yellow-50 @endif">
                                    {{-- Kolom Judul Buku --}}
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $borrow->book->title }}</div>
                                                <div class="text-sm text-gray-500">Penulis: {{ $borrow->book->author }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    {{-- Kolom Tanggal Pinjam --}}
                                    <td class="py-4 px-6 whitespace-nowrap text-sm text-gray-900">
                                        {{ $borrow->borrow_date ? \Carbon\Carbon::parse($borrow->borrow_date)->format('d M Y') : '-' }}
                                    </td>
                                    {{-- Kolom Batas Kembali --}}
                                    <td class="py-4 px-6 whitespace-nowrap text-sm text-gray-900">
                                        {{ $borrow->return_date ? \Carbon\Carbon::parse($borrow->return_date)->format('d M Y') : '-' }}
                                    </td>
                                    {{-- Kolom Tanggal Kembali Aktual --}}
                                    <td class="py-4 px-6 whitespace-nowrap text-sm text-gray-900">
                                        {{ $borrow->actual_return_date ? \Carbon\Carbon::parse($borrow->actual_return_date)->format('d M Y') : 'N/A' }}
                                    </td>
                                    {{-- Kolom Status --}}
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Dikembalikan
                                        </span>
                                    </td>
                                    {{-- Kolom Keterangan (Dikembalikan) --}}
                                    <td class="py-4 px-6 text-sm text-gray-900">
                                        @if($wasLate)
    @php
        // Pastikan kedua tanggal diubah ke awal hari untuk hitungan integer penuh
        $actualReturnDate = \Carbon\Carbon::parse($borrow->actual_return_date)->startOfDay();
        $dueDate = \Carbon\Carbon::parse($borrow->return_date)->startOfDay();
        
        $lateDaysAtReturn = $actualReturnDate->diffInDays($dueDate);
    @endphp
    <span class="text-red-600">Terlambat {{ $lateDaysAtReturn }} hari</span>
                                        @else
                                            <span class="text-green-600">Tepat waktu</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Paginasi menggunakan variabel asli dari Controller --}}
            @if($borrowHistory->hasPages())
            <div class="mt-6">
                {{ $borrowHistory->links() }}
            </div>
            @endif
        @endif

        {{-- Keterangan Status --}}
        <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Keterangan Status:</strong><br>
                        • <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">Dipinjam</span> - Buku sedang Anda pinjam<br>
                        • <span class="bg-green-100 text-green-800 px-2 py-1 rounded">Dikembalikan</span> - Buku sudah dikembalikan<br>
                        • <span class="bg-red-100 text-red-800 px-2 py-1 rounded">Terlambat</span> - Melebihi batas waktu pengembalian
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection