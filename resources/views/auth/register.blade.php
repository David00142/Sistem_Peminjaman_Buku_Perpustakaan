@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6 mt-10">
    <!-- Logo Section -->
    <div class="text-center mb-6">
        <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Daftar Akun</h2>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nama -->
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-user text-gray-400"></i>
                </div>
                <input type="text" id="name" name="name" value="{{ old('name') }}" 
                       class="shadow appearance-none border rounded w-full py-2 px-10 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror"
                       required autocomplete="name" autofocus placeholder="Masukkan nama lengkap">
            </div>
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-400"></i>
                </div>
                <input type="email" id="email" name="email" value="{{ old('email') }}" 
                       class="shadow appearance-none border rounded w-full py-2 px-10 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror"
                       required autocomplete="email" placeholder="Masukkan alamat email">
            </div>
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kelas (Dropdown dari Array) -->
        <div class="mb-4">
            <label for="kelas" class="block text-gray-700 text-sm font-bold mb-2">Pilih Kelas</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-graduation-cap text-gray-400"></i>
                </div>
                <select name="kelas" id="kelas" 
                        class="shadow appearance-none border rounded w-full py-2 px-10 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('kelas') border-red-500 @enderror" 
                        required>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasOptions as $key => $value)
                    <option value="{{ $key }}" {{ old('kelas') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                    @endforeach
                </select>
            </div>
            @error('kelas')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-400"></i>
                </div>
                <input type="password" id="password" name="password" 
                       class="shadow appearance-none border rounded w-full py-2 px-10 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror"
                       required autocomplete="new-password" placeholder="Buat password">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <button type="button" id="togglePassword" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Konfirmasi Password -->
        <div class="mb-6">
            <label for="password-confirm" class="block text-gray-700 text-sm font-bold mb-2">Konfirmasi Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-400"></i>
                </div>
                <input type="password" id="password-confirm" name="password_confirmation" 
                       class="shadow appearance-none border rounded w-full py-2 px-10 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       required autocomplete="new-password" placeholder="Konfirmasi password">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <button type="button" id="togglePasswordConfirm" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tombol Submit -->
        <div class="flex items-center justify-between">
            <button type="submit" 
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Daftar
            </button>
            <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" 
               href="{{ route('login') }}">
               Sudah punya akun?
            </a>
        </div>
    </form>
</div>

<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle eye icon
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // Toggle confirm password visibility
    document.getElementById('togglePasswordConfirm').addEventListener('click', function() {
        const passwordInput = document.getElementById('password-confirm');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle eye icon
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
</script>

<!-- Pastikan Font Awesome dimuat -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
@endsection