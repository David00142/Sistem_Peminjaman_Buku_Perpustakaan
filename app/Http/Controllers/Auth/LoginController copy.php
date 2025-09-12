<?php
// app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // Tentukan apakah yang dimasukkan adalah email atau nama
        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        // Cek apakah user ada dengan nama/email yang dimasukkan
        $user = User::where($field, $request->login)->first();

        // Jika user ditemukan dan password valid
        if ($user && Hash::check($request->password, $user->password)) {
            // Melakukan login
            Auth::login($user, $request->remember);

            // Redirect berdasarkan role
            if ($user->role === 'admin' || $user->role === 'pustakawan') {
                return redirect()->route('admin.index'); // Arahkan ke halaman admin
            }

            // Jika login berhasil dan role anggota, arahkan ke halaman home
            return redirect()->intended('/home');
        }

        // Jika login gagal, lemparkan error
        throw ValidationException::withMessages([
            'login' => ['Nama, Email, atau Password salah'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();  // Logout pengguna
        $request->session()->invalidate();  // Invalidasi session
        $request->session()->regenerateToken();  // Regenerasi token CSRF
        return redirect('/');  // Redirect ke halaman utama setelah logout
    }

    // Redirect setelah login sukses (fallback)
    protected function redirectTo()
    {
        $user = Auth::user();
        
        if ($user && ($user->role === 'admin' || $user->role === 'pustakawan')) {
            return route('admin.index');
        }
        
        return '/home';
    }
}