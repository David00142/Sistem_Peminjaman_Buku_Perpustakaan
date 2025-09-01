@extends('layouts.app')

@section('title', 'Selamat Datang')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6 mt-8 text-center">
    <h1 class="text-4xl font-bold text-gray-800 mb-6">Sistem Peminjaman Buku Perpustakaan</h1>
    <p class="text-gray-600 mb-8">Sistem manajemen peminjaman buku untuk perpustakaan modern</p>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-blue-100 p-4 rounded-lg">
            <h2 class="text-xl font-semibold text-blue-800 mb-2">Login</h2>
            <p class="text-blue-600 mb-4">Masuk ke akun Anda</p>
            <a href="{{ route('login') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Login Sekarang
            </a>
        </div>
        
        <div class="bg-green-100 p-4 rounded-lg">
            <h2 class="text-xl font-semibold text-green-800 mb-2">Daftar</h2>
            <p class="text-green-600 mb-4">Buat akun baru</p>
            <a href="{{ route('register') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Daftar Sekarang
            </a>
        </div>
    </div>
    
    <div class="bg-gray-100 p-4 rounded-lg">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Tentang Sistem</h2>
        <p class="text-gray-600">Sistem ini membantu mengelola peminjaman dan pengembalian buku di perpustakaan dengan efisien.</p>
    </div>
</div>
@endsection