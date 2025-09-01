@extends('layouts.app')

@section('title', 'Verifikasi OTP')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Verifikasi OTP</h2>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('otp.verify.submit') }}">
        @csrf

        <input type="hidden" name="email" value="{{ $email }}">

        <div class="mb-4">
            <label for="otp" class="block text-gray-700 text-sm font-bold mb-2">Kode OTP</label>
            <input type="text" id="otp" name="otp" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('otp') border-red-500 @enderror"
                   required autocomplete="off" autofocus placeholder="Masukkan 6 digit kode OTP">
            @error('otp')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between mb-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Verifikasi
            </button>
            
            <button type="button" onclick="event.preventDefault(); document.getElementById('resend-form').submit();" 
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Kirim Ulang OTP
            </button>
        </div>
    </form>

    <form id="resend-form" action="{{ route('otp.resend') }}" method="POST" class="hidden">
        @csrf
    </form>

    <div class="text-sm text-gray-600">
        <p>Kode OTP telah dikirim ke: <strong>{{ $email }}</strong></p>
        <p class="mt-2">Kode OTP berlaku selama 10 menit.</p>
    </div>
</div>
@endsection