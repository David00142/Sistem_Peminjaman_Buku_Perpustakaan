@extends('layouts.app')

@section('title', $book->title . ' - BOBOOK')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Book Image -->
        <div class="flex justify-center">
            <div class="w-64 h-96 bg-gray-200 rounded-lg flex items-center justify-center overflow-hidden">
                @if($book->image && Storage::disk('public')->exists($book->image))
                    <img src="{{ asset('storage/' . $book->image) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                @endif
            </div>
        </div>

        <!-- Book Details -->
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $book->title }}</h1>
            <p class="text-lg text-gray-600 mb-4">oleh {{ $book->author }}</p>
            
            <div class="mb-4">
                <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">{{ $book->category }}</span>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2">Deskripsi</h3>
                <p class="text-gray-700">{{ $book->description }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">Total Copy</p>
                    <p class="text-xl font-bold">{{ $book->quantity }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">Tersedia</p>
                    <p class="text-xl font-bold text-green-600">{{ $book->quantity - $book->borrowed - $book->booked }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">Dipinjam</p>
                    <p class="text-xl font-bold">{{ $book->borrowed }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">Dipesan</p>
                    <p class="text-xl font-bold">{{ $book->booked }}</p>
                </div>
            </div>

            @if($book->quantity > ($book->borrowed + $book->booked))
                <form action="{{ route('book.booking', $book->id) }}" method="POST" id="bookingForm">
                    @csrf
                    
                    <!-- Pilihan Durasi Peminjaman -->
                    <div class="mb-4">
                        <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">
                            📆 Durasi Peminjaman
                        </label>
                        <select name="duration" id="duration" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Pilih durasi...</option>
                            <option value="3">3 Hari</option>
                            <option value="5">5 Hari</option>
                            <option value="7">7 Hari</option>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Maksimal peminjaman 30 hari</p>
                    </div>

                    <!-- Informasi Tanggal -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <strong>Perkiraan Jadwal:</strong><br>
                                    • Tanggal Pinjam: <span id="borrowDate">{{ now()->format('d M Y') }}</span><br>
                                    • Tanggal Kembali: <span id="returnDate">-</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                        📚 Booking Buku Ini
                    </button>
                    <p class="text-sm text-gray-500 mt-2 text-center">Buku akan dipesan untuk Anda</p>
                </form>
            @else
                <button disabled class="w-full bg-gray-400 text-white font-bold py-3 px-4 rounded-lg cursor-not-allowed">
                    ❌ Tidak Tersedia
                </button>
                <p class="text-sm text-red-500 mt-2 text-center">Semua copy sedang dipinjam atau dipesan</p>
            @endif

            <div class="mt-4">
                <a href="{{ route('home') }}" class="text-blue-500 hover:text-blue-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const durationSelect = document.getElementById('duration');
    const returnDateSpan = document.getElementById('returnDate');
    const borrowDateSpan = document.getElementById('borrowDate');
    
    // Set tanggal pinjam hari ini
    const today = new Date();
    borrowDateSpan.textContent = formatDate(today);
    
    durationSelect.addEventListener('change', function() {
        const duration = parseInt(this.value);
        if (duration) {
            const returnDate = new Date(today);
            returnDate.setDate(today.getDate() + duration);
            returnDateSpan.textContent = formatDate(returnDate);
        } else {
            returnDateSpan.textContent = '-';
        }
    });
    
    function formatDate(date) {
        const options = { day: 'numeric', month: 'short', year: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    }
    
    // Validasi form
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const duration = durationSelect.value;
        if (!duration) {
            e.preventDefault();
            alert('Silakan pilih durasi peminjaman terlebih dahulu.');
            durationSelect.focus();
        }
    });
});
</script>

<style>
select:required:invalid {
    color: #6b7280;
}
select option[value=""][disabled] {
    display: none;
}
select option {
    color: #000;
}
</style>
@endsection