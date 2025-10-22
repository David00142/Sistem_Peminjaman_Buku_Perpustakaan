<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    /**
     * Menampilkan riwayat peminjaman buku pengguna.
     * Termasuk yang sedang dipinjam/terlambat dan yang sudah dikembalikan/denda.
     */
    public function show()
    {
        // Pastikan pengguna sudah login
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Silakan login untuk melihat riwayat Anda.');
        }

        $user = Auth::user();
        
        // Mengambil semua riwayat peminjaman yang relevan untuk user ini.
        // Eager load 'book' dan 'penalties' (penting untuk menampilkan status denda).
        $borrowHistory = Borrow::with(['book', 'penalties'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['borrowed', 'returned', 'overdue'])
            // Urutkan berdasarkan tanggal pinjam terbaru
            ->orderBy('borrow_date', 'desc') 
            ->paginate(10); 

        // Mengirim variabel $borrowHistory ke view history.show
        return view('history.show', compact('borrowHistory'));
    }
}