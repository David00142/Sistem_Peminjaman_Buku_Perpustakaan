@extends('layouts.app')

@section('title', 'Denda Saya')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Denda Peminjaman Buku</h1>

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

        <!-- Statistik Denda -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-700">Total Denda Belum Dibayar</h3>
                        <p class="text-3xl font-bold text-red-600">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-700">Total Denda Sudah Dibayar</h3>
                        <p class="text-3xl font-bold text-green-600">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($penalties->isEmpty())
            <div class="text-center py-12 bg-white rounded-lg shadow-md">
                <div class="mb-4">
                    <svg class="w-16 h-16 text-green-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-gray-500 text-lg mb-4">Anda tidak memiliki denda.</p>
                <p class="text-sm text-gray-400">Teruskan kebiasaan baik mengembalikan buku tepat waktu!</p>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buku</th>
                                <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alasan Denda</th>
                                <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batas Bayar</th>
                                <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($penalties as $penalty)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $penalty->borrow->book->title ?? 'Buku tidak ditemukan' }}</div>
                                            <div class="text-sm text-gray-500">Pinjam: {{ $penalty->borrow->borrow_date->format('d M Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap text-sm text-gray-900">
                                    @if($penalty->reason === 'late_return')
                                        Keterlambatan Pengembalian
                                    @elseif($penalty->reason === 'damaged')
                                        Buku Rusak
                                    @elseif($penalty->reason === 'lost')
                                        Buku Hilang
                                    @else
                                        {{ $penalty->reason }}
                                    @endif
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rp {{ number_format($penalty->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap">
                                    @if($penalty->status === 'unpaid')
                                        @if($penalty->is_overdue)
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Terlambat Bayar
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Belum Dibayar
                                            </span>
                                        @endif
                                    @elseif($penalty->status === 'paid')
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Lunas
                                        </span>
                                    @elseif($penalty->status === 'waived')
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Dihapuskan
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap text-sm text-gray-900">
                                        {{ $penalty->due_date->format('d M Y') }}
                                        @if($penalty->is_overdue)
                                            <br>
                                            <span class="text-red-600 text-xs">
                                                Segera Di Bayar
                                            </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap text-sm text-gray-900">
                                    @if($penalty->status === 'paid')
                                        Dibayar: {{ $penalty->paid_date->format('d M Y') }}
                                    @elseif($penalty->status === 'unpaid' && $penalty->is_overdue)
                                        <span class="text-red-600">Harap segera bayar</span>
                                    @elseif($penalty->status === 'unpaid')
                                        <span class="text-yellow-600">Menunggu pembayaran</span>
                                    @endif
                                    @if($penalty->notes)
                                        <br>
                                        <span class="text-gray-500 text-xs">{{ $penalty->notes }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Informasi Pembayaran Denda:</strong><br>
                            • Denda dapat dibayar di perpustakaan pada jam operasional<br>
                            • Pembayaran diterima secara tunai<br>
                            • Simpan bukti pembayaran sebagai arsip pribadi<br>
                            • Untuk informasi lebih lanjut, hubungi admin perpustakaan
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection