@extends('layouts.admin-layout')

@section('title', 'Denda')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Manajemen Denda</h1>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Denda Bulan Ini</h3>
            <p class="text-3xl font-bold text-blue-600">Rp 0</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Denda Tertunggak</h3>
            <p class="text-3xl font-bold text-red-600">Rp 0</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Peminjam dengan Denda</h3>
            <p class="text-3xl font-bold text-orange-600">0 Orang</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-xl font-semibold text-gray-700">Daftar Denda</h2>
        </div>
        <div class="p-6 text-center">
            <p class="text-gray-500">Belum ada data denda.</p>
        </div>
    </div>
</div>
@endsection