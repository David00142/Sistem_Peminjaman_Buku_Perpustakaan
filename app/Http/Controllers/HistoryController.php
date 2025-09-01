<?php
// app/Http/Controllers/HistoryController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function show()
    {
        // Menampilkan halaman riwayat pengguna
        return view('history.show');
    }
}
