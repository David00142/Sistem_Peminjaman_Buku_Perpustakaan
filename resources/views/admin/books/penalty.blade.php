@extends('layouts.admin-layout')

@section('title', 'Manajemen Denda')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Manajemen Denda</h1>

    {{-- Pesan Sukses atau Error --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-md" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-md" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    {{-- Debug Info --}}
    @if(env('APP_DEBUG') && Auth::user()->role === 'admin')
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded">
        <h4 class="font-bold text-yellow-800 mb-2">Debug Information:</h4>
        <div class="text-sm text-yellow-700 space-y-1">
            @php
                // Hitung manual untuk verifikasi
                $manualTotalThisMonth = 0;
                $manualOverdue = 0;
                $manualUsers = [];
                
                foreach($penalties as $penalty) {
                    if ($penalty->created_at->month == now()->month && $penalty->created_at->year == now()->year) {
                        $manualTotalThisMonth += $penalty->amount;
                    }
                    if ($penalty->status === 'unpaid') {
                        $manualOverdue += $penalty->amount;
                        $manualUsers[$penalty->user_id] = true;
                    }
                }
            @endphp
            <p>Total This Month (Manual): Rp {{ number_format($manualTotalThisMonth, 0, ',', '.') }}</p>
            <p>Overdue Penalties (Manual): Rp {{ number_format($manualOverdue, 0, ',', '.') }}</p>
            <p>Users with Penalties (Manual): {{ count($manualUsers) }}</p>
            <p>Total Penalties: {{ $penalties->total() }}</p>
            <p>Unpaid Count: {{ $penalties->where('status', 'unpaid')->count() }}</p>
            
            @foreach($penalties->take(3) as $penalty)
                <div class="mt-2 p-2 bg-yellow-100 rounded">
                    <strong>Penalty ID {{ $penalty->id }}:</strong><br>
                    User: {{ $penalty->user->name ?? 'N/A' }} | 
                    Amount: Rp {{ number_format($penalty->amount, 0, ',', '.') }} | 
                    Status: {{ $penalty->status }}<br>
                    Return Date: {{ $penalty->borrow->return_date ?? 'N/A' }} |
                    Late Days: 
                    @if($penalty->reason === 'late_return' && $penalty->borrow && $penalty->borrow->return_date)
                        {{ max(0, now()->diffInDays(Carbon\Carbon::parse($penalty->borrow->return_date))) }}
                    @else
                        N/A
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Kartu Statistik Denda --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- Total Denda Bulan Ini --}}
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Denda Bulan Ini</h3>
                    <p class="text-3xl font-bold text-blue-600">Rp {{ number_format($totalPenaltiesThisMonth, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-500 mt-2">Denda dibuat bulan {{ now()->translatedFormat('F Y') }}</p>
                </div>
                <div class="text-blue-500">
                    <i class="fas fa-calendar-alt text-3xl opacity-50"></i>
                </div>
            </div>
        </div>
        
        {{-- Denda Belum Dibayar --}}
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Denda Belum Dibayar</h3>
                    <p class="text-3xl font-bold text-red-600">Rp {{ number_format($overduePenalties, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-500 mt-2">
                        @php
                            $unpaidCount = $penalties->where('status', 'unpaid')->count();
                        @endphp
                        {{ $unpaidCount }} denda aktif
                    </p>
                </div>
                <div class="text-red-500">
                    <i class="fas fa-exclamation-triangle text-3xl opacity-50"></i>
                </div>
            </div>
        </div>
        
        {{-- Peminjam dengan Denda --}}
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Peminjam dengan Denda</h3>
                    <p class="text-3xl font-bold text-orange-600">{{ $usersWithPenalties }}</p>
                    <p class="text-sm text-gray-500 mt-2">Anggota memiliki denda aktif</p>
                </div>
                <div class="text-orange-500">
                    <i class="fas fa-users text-3xl opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Informasi Sistem Denda --}}
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-yellow-400 mt-1"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <strong>Sistem Denda Otomatis:</strong> Keterlambatan pengembalian buku dikenakan denda 
                    <strong>Rp 2.600 per hari</strong>. Sistem akan menghitung otomatis berdasarkan hari keterlambatan.
                </p>
            </div>
        </div>
    </div>

    {{-- Tabel Daftar Denda --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 border-b bg-gray-50">
            <h2 class="text-xl font-semibold text-gray-700">Daftar Semua Denda</h2>
            <p class="text-sm text-gray-600 mt-1">Kelola denda dari semua anggota</p>
        </div>
        
        @if($penalties->isEmpty())
            <div class="p-8 text-center">
                <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                <p class="text-gray-600 text-lg mb-2">Tidak ada data denda</p>
                <p class="text-gray-400">Belum ada denda yang tercatat dalam sistem</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Peminjam
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Buku
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jatuh Tempo
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Keterlambatan
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Alasan
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah Denda
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($penalties as $penalty)
@php
    $borrow = $penalty->borrow;
    $book = $borrow->book ?? null;
    $user = $penalty->user ?? null;
    
    // PERBAIKAN: Hitung real-time untuk display dengan cara MUTLAK
    if ($penalty->reason === 'late_return' && $borrow && $borrow->return_date) {
        $dueDate = \Carbon\Carbon::parse($borrow->return_date)->startOfDay();
        
        // Tentukan tanggal aktual pengembalian
        if ($borrow->actual_return_date) {
            $actualReturnDate = \Carbon\Carbon::parse($borrow->actual_return_date)->startOfDay();
        } else {
            $actualReturnDate = now()->startOfDay();
        }
        
        // Hitung hari keterlambatan hanya jika actual return date setelah due date
        if ($actualReturnDate->greaterThan($dueDate)) {
            // PERBAIKAN: Gunakan diffInDays() untuk hasil integer absolut
            $lateDays = $dueDate->diffInDays($actualReturnDate);
            // Pastikan minimal 1 hari
            $lateDays = max(1, $lateDays);
        } else {
            $lateDays = 0;
        }
    } else {
        $lateDays = 0;
    }
@endphp
                            
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                {{-- Kolom Peminjam --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $user->name ?? 'User Tidak Ditemukan' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $user->email ?? '-' }}
                                    </div>
                                    @if($user)
                                        <div class="text-xs text-gray-400 mt-1">
                                            ID: {{ $user->id }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Kolom Buku --}}
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $book->title ?? 'Buku Tidak Ditemukan' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        by {{ $book->author ?? '-' }}
                                    </div>
                                    @if($book && $book->isbn)
                                        <div class="text-xs text-gray-400 mt-1">
                                            ISBN: {{ $book->isbn }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Kolom Jatuh Tempo --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($borrow && $borrow->return_date)
                                        <div class="font-medium">
                                            {{ \Carbon\Carbon::parse($borrow->return_date)->format('d M Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Pinjam: {{ optional($borrow->borrow_date)->format('d M Y') ?? '-' }}
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

{{-- Kolom Keterlambatan --}}
<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
    @if($penalty->reason === 'late_return' && $borrow && $borrow->return_date)
        @if($lateDays > 0)
            <div class="flex flex-col">
                <span class="text-red-600 font-semibold">{{ $lateDays }} Hari</span>
                <span class="text-xs text-gray-500">
                    @if($borrow->actual_return_date)
                        Kembali: {{ \Carbon\Carbon::parse($borrow->actual_return_date)->format('d M Y') }}
                    @else
                        Masih dipinjam
                    @endif
                </span>
            </div>
        @else
            <span class="text-green-500">Tepat waktu</span>
        @endif
    @else
        <span class="text-gray-500">-</span>
    @endif
</td>

                                {{-- Kolom Alasan --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        @if($penalty->reason === 'late_return')
                                            <i class="fas fa-clock text-red-500 mr-2"></i>
                                            <span>Keterlambatan</span>
                                        @elseif($penalty->reason === 'damaged')
                                            <i class="fas fa-book-medical text-orange-500 mr-2"></i>
                                            <span>Buku Rusak</span>
                                        @elseif($penalty->reason === 'lost')
                                            <i class="fas fa-book-dead text-red-500 mr-2"></i>
                                            <span>Buku Hilang</span>
                                        @else
                                            <i class="fas fa-info-circle text-gray-500 mr-2"></i>
                                            <span>{{ $penalty->reason }}</span>
                                        @endif
                                    </div>
                                    @if($penalty->notes)
                                        <div class="text-xs text-gray-400 mt-1">
                                            {{ Str::limit($penalty->notes, 30) }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Kolom Jumlah Denda --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-red-600">
                                        Rp {{ number_format($penalty->amount, 0, ',', '.') }}
                                    </div>
                                    @if($penalty->reason === 'late_return' && $lateDays > 0)
                                        <div class="text-xs text-gray-500">
                                            ({{ $lateDays }} hari × Rp 2.600)
                                        </div>
                                    @endif
                                </td>

                                {{-- Kolom Status --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($penalty->status === 'unpaid')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            Belum Dibayar
                                        </span>
                                        <div class="text-xs text-red-600 mt-1">
                                            <i class="fas fa-clock mr-1"></i>
                                            Segera
                                        </div>
                                    @elseif($penalty->status === 'paid')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Lunas
                                        </span>
                                        @if($penalty->paid_date)
                                            <div class="text-xs text-green-600 mt-1">
                                                {{ $penalty->paid_date->format('d M Y') }}
                                            </div>
                                        @endif
                                    @elseif($penalty->status === 'waived')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            <i class="fas fa-ban mr-1"></i>
                                            Dihapus
                                        </span>
                                    @else
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ ucfirst($penalty->status) }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Kolom Tanggal --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="font-medium">
                                        {{ $penalty->created_at->format('d M Y') }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $penalty->created_at->format('H:i') }}
                                    </div>
                                </td>

                                {{-- Kolom Aksi --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if($penalty->status === 'unpaid')
                                        <div class="flex flex-col space-y-2">
                                            {{-- Tombol Telah Dibayar --}}
                                            <form action="{{ route('admin.penalties.complete', $penalty->id) }}" method="POST" 
                                                  onsubmit="return confirm('Apakah Anda yakin denda ini sudah dibayar? Status akan berubah menjadi LUNAS.');">
                                                @csrf
                                                <button type="submit" 
                                                        class="w-full bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200 flex items-center justify-center"
                                                        title="Tandai sudah dibayar">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Telah Dibayar
                                                </button>
                                            </form>
                                            
                                            <div class="flex space-x-2">
                                                {{-- Tombol Hapus/Waive --}}
                                                <form action="{{ route('admin.penalties.waive', $penalty->id) }}" method="POST" 
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus denda ini? Aksi ini tidak bisa dibatalkan.');">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="flex-1 bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200 flex items-center justify-center"
                                                            title="Hapus denda">
                                                        <i class="fas fa-ban mr-1"></i>
                                                        Hapus
                                                    </button>
                                                </form>

                                                {{-- Tombol Detail --}}
                                                <button type="button" 
                                                        onclick="showPenaltyDetail({{ $penalty->id }})"
                                                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200 flex items-center justify-center"
                                                        title="Lihat detail">
                                                    <i class="fas fa-eye mr-1"></i>
                                                    Detail
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">Tidak ada aksi</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="p-4 border-t">
                {{ $penalties->links() }}
            </div>
        @endif
    </div>

    {{-- Total Keseluruhan --}}
    @if($penalties->isNotEmpty())
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-6 rounded-lg">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-blue-800">Ringkasan Denda</h3>
                <p class="text-sm text-blue-600 mt-1">Total keseluruhan denda dalam sistem</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-blue-800">
                    Rp {{ number_format($penalties->sum('amount'), 0, ',', '.') }}
                </p>
                <p class="text-sm text-blue-600">
                    {{ $penalties->count() }} total denda
                </p>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Modal Detail Denda --}}
<div id="penaltyDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Detail Denda</h3>
                <button onclick="closePenaltyDetail()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="penaltyDetailContent">
                {{-- Content akan diisi oleh JavaScript --}}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
    // Fungsi untuk menampilkan detail denda
    function showPenaltyDetail(penaltyId) {
        fetch(`/admin/penalties/${penaltyId}/detail`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('penaltyDetailContent').innerHTML = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Peminjam</label>
                                <p class="mt-1 text-sm text-gray-900">${data.user_name}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <p class="mt-1 text-sm text-gray-900">${data.user_email}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Buku</label>
                            <p class="mt-1 text-sm text-gray-900">${data.book_title} - ${data.book_author}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal Pinjam</label>
                                <p class="mt-1 text-sm text-gray-900">${data.borrow_date}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jatuh Tempo</label>
                                <p class="mt-1 text-sm text-gray-900">${data.return_date}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Keterlambatan</label>
                                <p class="mt-1 text-sm text-gray-900">${data.late_days} hari</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jumlah Denda</label>
                                <p class="mt-1 text-sm font-bold text-red-600">Rp ${new Intl.NumberFormat('id-ID').format(data.amount)}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Alasan Denda</label>
                            <p class="mt-1 text-sm text-gray-900">${data.reason}</p>
                        </div>
                        ${data.notes ? `
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Catatan</label>
                            <p class="mt-1 text-sm text-gray-900">${data.notes}</p>
                        </div>
                        ` : ''}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <p class="mt-1 text-sm">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    ${data.status === 'unpaid' ? 'bg-red-100 text-red-800' : 
                                      data.status === 'paid' ? 'bg-green-100 text-green-800' : 
                                      'bg-gray-100 text-gray-800'}">
                                    ${data.status === 'unpaid' ? 'Belum Dibayar' : 
                                      data.status === 'paid' ? 'Lunas' : 
                                      data.status}
                                </span>
                            </p>
                        </div>
                    </div>
                `;
                document.getElementById('penaltyDetailModal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal memuat detail denda');
            });
    }

    // Fungsi untuk menutup modal
    function closePenaltyDetail() {
        document.getElementById('penaltyDetailModal').classList.add('hidden');
    }

    // Auto-refresh data setiap 3 menit
    document.addEventListener('DOMContentLoaded', function() {
        setInterval(function() {
            window.location.reload();
        }, 180000); // 3 menit
    });

    // Tutup modal ketika klik di luar
    window.onclick = function(event) {
        const modal = document.getElementById('penaltyDetailModal');
        if (event.target === modal) {
            closePenaltyDetail();
        }
    }
</script>
@endpush

@push('styles')
<style>
    .hover\:bg-gray-50:hover {
        background-color: #f9fafb;
    }
</style>
@endpush