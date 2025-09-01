@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6 mt-10">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Daftar Akun</h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nama -->
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror"
                   required autocomplete="name" autofocus>
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror"
                   required autocomplete="email">
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kelas -->
        <div class="mb-4">
            <label for="kelas" class="block text-gray-700 text-sm font-bold mb-2">Pilih Kelas</label>
            <select name="kelas" id="kelas" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('kelas') border-red-500 @enderror" 
                    required>
                <optgroup label="X">
                    <option value="X TKJ1">X TKJ 1</option>
                    <option value="X TKJ2">X TKJ 2</option>
                    <option value="X TKJ3">X TKJ 3</option>
                    <option value="X AK1">X AK1</option>
                    <option value="X AK2">X AK2</option>
                    <option value="X BID1">X BID1</option>
                </optgroup>
                <optgroup label="XI">
                    <option value="XI TKJ1">XI TKJ 1</option>
                    <option value="XI TKJ2">XI TKJ 2</option>
                    <option value="XI TKJ3">XI TKJ 3</option>
                    <option value="XI AK1">XI AK 1</option>
                    <option value="XI AK2">XI AK 2</option>
                    <option value="XI BID1">XI BID 1</option>
                </optgroup>
                <optgroup label="XII">
                    <option value="XII TKJ1">XII TKJ 1</option>
                    <option value="XII TKJ2">XII TKJ 2</option>
                    <option value="XII TKJ3">XII TKJ 3</option>
                    <option value="XII AK1">XII AK 1</option>
                    <option value="XII AK2">XII AK 2</option>
                    <option value="XII BID1">XII BID 1</option>
                </optgroup>
            </select>
            @error('kelas')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
            <input type="password" id="password" name="password" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror"
                   required autocomplete="new-password">
            @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Konfirmasi Password -->
        <div class="mb-6">
            <label for="password-confirm" class="block text-gray-700 text-sm font-bold mb-2">Konfirmasi Password</label>
            <input type="password" id="password-confirm" name="password_confirmation" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   required autocomplete="new-password">
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
@endsection
