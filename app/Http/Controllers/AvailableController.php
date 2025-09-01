<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class AvailableController extends Controller
{
    public function index()
    {
        // Ambil semua buku dengan pagination (10 buku per halaman)
        $books = Book::paginate(10);
        
        // Kirim data buku ke view
        return view('available', compact('books'));
    }
}
