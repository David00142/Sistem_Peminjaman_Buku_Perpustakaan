<?php
// app/Http/Controllers/Auth/RegisterController.php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    // TAMBAHKAN METHOD INI
    public function showRegistrationForm()
    {
        $kelasOptions = [
            '10 AK 1' => '10 AK 1',
            '10 AK 2' => '10 AK 2',
            '10 TKJ 1' => '10 TKJ 1',
            '10 TKJ 2' => '10 TKJ 2',
            '10 TKJ 3' => '10 TKJ 3',
            '10 BID' => '10 BID',
            '11 AK 1' => '11 AK 1',
            '11 AK 2' => '11 AK 2',
            '11 TKJ 1' => '11 TKJ 1',
            '11 TKJ 2' => '11 TKJ 2',
            '11 TKJ 3' => '11 TKJ 3',
            '11 BID' => '11 BID',
            '12 AK 1' => '12 AK 1',
            '12 AK 2' => '12 AK 2',
            '12 TKJ 1' => '12 TKJ 1',
            '12 TKJ 2' => '12 TKJ 2',
            '12 TKJ 3' => '12 TKJ 3',
            '12 BID' => '12 BID',
        ];
        
        return view('auth.register', compact('kelasOptions'));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required', 
                'string', 
                'max:255',
                'regex:/^[^0-9]*$/'
            ],
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'kelas' => 'required|string', // validasi kelas
        ], [
            'name.regex' => 'Nama tidak boleh mengandung angka.',
            'kelas.required' => 'Silakan pilih kelas Anda.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Buat user dan simpan kelas
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'kelas' => $request->kelas,
            'role' => 'anggota', // default role anggota
        ]);

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Registrasi berhasil! Selamat datang.');
    }
}