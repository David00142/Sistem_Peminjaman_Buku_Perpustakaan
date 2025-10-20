<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman profil pengguna yang sedang login.
     */
    public function show(): View
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Pastikan user terautentikasi
        if (!$user) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('profile.show', compact('user'));
    }

    /**
     * Menampilkan form untuk mengedit profil.
     */
    public function edit(): View
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Pastikan user terautentikasi
        if (!$user) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('profile.edit', compact('user'));
    }

    /**
     * Memperbarui data profil di database.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Validasi autentikasi
        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        // Validasi input dari form
        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        try {
            // Update email
            $user->email = $validated['email'];

            // Cek jika ada file foto profil baru yang di-upload
            if ($request->hasFile('profile_photo')) {
                // Hapus foto lama jika ada
                if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                    Storage::disk('public')->delete($user->profile_photo_path);
                }

                // Simpan foto baru dan dapatkan path-nya
                $path = $request->file('profile_photo')->store('profile-photos', 'public');
                $user->profile_photo_path = $path;
            }

            // Simpan perubahan ke database
            $user->save();

            // Redirect kembali ke halaman profil dengan pesan sukses
            return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui!');

        } catch (\Exception $e) {
            // Handle error
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}