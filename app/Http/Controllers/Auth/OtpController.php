<?php
// app/Http/Controllers/Auth/OtpController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth; // Tambahkan ini

class OtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function showOtpForm(Request $request)
    {
        if (!$request->session()->has('registered_email')) {
            return redirect()->route('register');
        }

        return view('auth.verify-otp', [
            'email' => $request->session()->get('registered_email')
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
            'email' => 'required|email'
        ]);

        if ($this->otpService->verifyOtp($request->email, $request->otp)) {
            $request->session()->forget('registered_email');
            
            // Auto login setelah verifikasi - PERBAIKAN DI SINI
            $user = User::where('email', $request->email)->first();
            if ($user) {
                Auth::login($user); // Menggunakan Auth::login() instead of auth()->login()
            }
            
            return redirect()->route('home')->with('success', 'Akun berhasil diverifikasi!');
        }

        return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa']);
    }

    public function resendOtp(Request $request)
    {
        $email = $request->session()->get('registered_email');
        
        if (!$email) {
            return redirect()->route('register');
        }

        $user = User::where('email', $email)->first();
        
        if ($user) {
            $this->otpService->sendOtp($user);
            return back()->with('success', 'Kode OTP baru telah dikirim ke email Anda');
        }

        return redirect()->route('register');
    }
}