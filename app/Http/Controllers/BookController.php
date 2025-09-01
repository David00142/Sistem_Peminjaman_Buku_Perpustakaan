<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    // Cek apakah user memiliki akses admin
    private function checkAdminAccess()
    {
        $user = Auth::user();
        if (!$user || ($user->role !== 'admin' && $user->role !== 'pustakawan')) {
            abort(403, 'Anda tidak memiliki akses ke halaman admin.');
        }
    }

    // ==============================================
    // METHOD UNTUK ADMIN
    // ==============================================

    // Menampilkan form untuk menambah buku dan menampilkan buku yang sudah ada
    public function create()
    {
        $this->checkAdminAccess();
        
        // Ambil semua buku yang sudah ada, urutkan berdasarkan kategori dan judul
        $books = Book::orderBy('category')
                     ->orderBy('title')
                     ->get();
        
        $book = null; // Inisialisasi variabel book

        return view('admin.books.add-book', compact('books', 'book'));
    }

    // Menampilkan form edit buku
    public function edit($id)
    {
        $this->checkAdminAccess();
        
        $book = Book::findOrFail($id);
        $books = Book::orderBy('category')
                     ->orderBy('title')
                     ->get();

        return view('admin.books.add-book', compact('books', 'book'));
    }

    // Menyimpan buku baru ke database
    public function store(Request $request)
    {
        $this->checkAdminAccess();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'category' => 'required|string|max:255',
            'booked' => 'nullable|integer|min:0',
            'borrowed' => 'nullable|integer|min:0',
        ]);

        // Cek apakah ada gambar yang diunggah
        $imagePath = null;
        if ($request->hasFile('image')) {
            // Simpan gambar dengan nama unik
            $imagePath = $request->file('image')->store('books', 'public');
            
            // Debug: cek path gambar
            Log::info('Image stored at: ' . $imagePath);
        }

        // Menyimpan buku baru
        try {
            $book = Book::create([
                'title' => $request->title,
                'author' => $request->author,
                'description' => $request->description,
                'quantity' => $request->quantity,
                'image' => $imagePath,
                'category' => $request->category,
                'booked' => $request->booked ?? 0,
                'borrowed' => $request->borrowed ?? 0,
            ]);

            // Debug: cek data yang disimpan
            Log::info('Book created: ', $book->toArray());

            return redirect()->route('admin.books.create')->with('success', 'Buku berhasil ditambahkan');

        } catch (\Exception $e) {
            Log::error('Error creating book: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan buku: ' . $e->getMessage());
        }
    }

    // Update buku yang sudah ada
    public function update(Request $request, $id)
    {
        $this->checkAdminAccess();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'category' => 'required|string|max:255',
            'booked' => 'nullable|integer|min:0',
            'borrowed' => 'nullable|integer|min:0',
        ]);

        $book = Book::findOrFail($id);

        // Cek apakah ada gambar yang diunggah
        $imagePath = $book->image;
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($book->image && Storage::disk('public')->exists($book->image)) {
                Storage::disk('public')->delete($book->image);
            }
            
            // Simpan gambar baru
            $imagePath = $request->file('image')->store('books', 'public');
            Log::info('New image stored at: ' . $imagePath);
        }

        // Update buku
        try {
            $book->update([
                'title' => $request->title,
                'author' => $request->author,
                'description' => $request->description,
                'quantity' => $request->quantity,
                'image' => $imagePath,
                'category' => $request->category,
                'booked' => $request->booked ?? $book->booked,
                'borrowed' => $request->borrowed ?? $book->borrowed,
            ]);

            Log::info('Book updated: ', $book->toArray());

            return redirect()->route('admin.books.create')->with('success', 'Buku berhasil diperbarui');

        } catch (\Exception $e) {
            Log::error('Error updating book: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui buku: ' . $e->getMessage());
        }
    }

    // Hapus buku
    public function destroy($id)
    {
        $this->checkAdminAccess();
        
        $book = Book::findOrFail($id);
        
        // Hapus gambar jika ada
        if ($book->image && Storage::disk('public')->exists($book->image)) {
            Storage::disk('public')->delete($book->image);
        }
        
        $book->delete();

        return redirect()->route('admin.books.create')->with('success', 'Buku berhasil dihapus');
    }

    // Halaman Buku Terbooking
public function bookedBooks()
{
    $this->checkAdminAccess();
    
    // Ambil data booking dengan relasi user dan book
    $bookedBooks = Borrow::with(['user', 'book'])
                         ->where('status', 'booked') // Only get books with status "booked"
                         ->orderBy('created_at', 'desc') // Order by the most recent booking
                         ->get();

    // Pass the data to the view
    return view('admin.books.booked-books', compact('bookedBooks'));
}


    // Halaman Buku yang Dipinjam
    public function borrowedBooks()
    {
        $this->checkAdminAccess();
        
        // Ambil data peminjaman dengan relasi user dan book
        $borrowings = Borrow::with(['user', 'book'])
                           ->where('status', 'borrowed')
                           ->orderBy('borrow_date', 'desc')
                           ->get();

        return view('admin.books.borrowed-books', compact('borrowings'));
    }

    public function getBorrowedBooks()
{
    $user = Auth::user();
    
    // Get all borrowed books where the status is 'borrowed' for the current user
    $borrowedBooks = Borrow::with('book')
        ->where('user_id', $user->id)
        ->where('status', 'borrowed')
        ->orderBy('borrow_date', 'desc')
        ->get();
    
    return $borrowedBooks;
}


    // Konfirmasi peminjaman buku (dari booked ke borrowed)
    public function confirmBorrow(Request $request, $id)
    {
        $this->checkAdminAccess();
        
        $borrow = Borrow::findOrFail($id);
        $book = $borrow->book;

        // Validasi
        $request->validate([
            'duration_days' => 'required|integer|min:1|max:30'
        ]);

        // Kurangi booked dan tambahkan borrowed
        if ($book->booked > 0) {
            $book->booked -= 1;
            $book->borrowed += 1;
            $book->save();

            // Update status peminjaman
            $borrow->status = 'borrowed';
            $borrow->borrow_date = now();
            $borrow->return_date = now()->addDays($request->duration_days);
            $borrow->save();

            return redirect()->route('admin.books.booked')->with('success', 'Peminjaman buku dikonfirmasi');
        }

        return redirect()->back()->with('error', 'Tidak ada booking untuk buku ini');
    }

    // Batalkan booking
    public function cancelBooking($id)
    {
        $this->checkAdminAccess();
        
        $borrow = Borrow::findOrFail($id);
        $book = $borrow->book;
        
        if ($book->booked > 0) {
            $book->booked -= 1;
            $book->save();

            // Hapus record booking
            $borrow->delete();

            return redirect()->route('admin.books.booked')->with('success', 'Booking dibatalkan');
        }

        return redirect()->back()->with('error', 'Tidak ada booking untuk buku ini');
    }

    // Kembalikan buku
    public function returnBook($id)
    {
        $this->checkAdminAccess();
        
        $borrow = Borrow::findOrFail($id);
        $book = $borrow->book;
        
        if ($book->borrowed > 0) {
            $book->borrowed -= 1;
            $book->save();

            // Update status pengembalian
            $borrow->status = 'returned';
            $borrow->actual_return_date = now();
            $borrow->save();

            return redirect()->route('admin.books.borrowed')->with('success', 'Buku berhasil dikembalikan');
        }

        return redirect()->back()->with('error', 'Tidak ada peminjaman untuk buku ini');
    }

    // Perpanjang peminjaman
    public function extendBorrow(Request $request, $id)
    {
        $this->checkAdminAccess();
        
        $request->validate([ 
            'additional_days' => 'required|integer|min:1|max:14' 
        ]);

        $borrow = Borrow::findOrFail($id);
        $borrow->return_date = $borrow->return_date->addDays($request->additional_days);
        $borrow->extension_count += 1;
        $borrow->save();

        return redirect()->back()->with('success', 'Peminjaman diperpanjang');
    }

    // ==============================================
    // METHOD UNTUK ANGGOTA
    // ==============================================

    /**
     * Menampilkan detail buku untuk anggota
     */
    public function show($id)
    {
        $book = Book::findOrFail($id);
        return view('book.show', compact('book'));
    }

    /**
     * Memproses booking buku oleh anggota
     */
    public function booking(Request $request, $id)
    {
        $user = Auth::user();
        $book = Book::findOrFail($id);
        
        // Validasi stok tersedia
        $availableStock = $book->quantity - $book->borrowed - $book->booked;
        if ($availableStock <= 0) {
            return redirect()->back()->with('error', 'Buku sedang tidak tersedia untuk dipinjam');
        }
        
        // Tambahkan booking
        $book->booked += 1;
        $book->save();
        
        // Catat di history peminjaman
        Borrow::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'booked',
            'borrow_date' => now(),
            'return_date' => now()->addDays(2), // Batas waktu pengambilan 2 hari
        ]);
        
        return redirect()->route('home')->with('success', 'Buku berhasil dipesan! Silakan ambil di perpustakaan dalam 2 hari.');
    }

    /**
     * Mendapatkan buku populer berdasarkan jumlah peminjaman
     */
    public function getPopularBooks($limit = 6)
    {
        return Book::orderBy('borrowed', 'desc')
                   ->orderBy('booked', 'desc')
                   ->take($limit)
                   ->get();
    }

    /**
     * Mendapatkan statistik buku untuk dashboard
     */
    public function getBookStats()
    {
        $totalBooks = Book::count();
        $totalQuantity = Book::sum('quantity');
        $totalBorrowed = Book::sum('borrowed');
        $totalBooked = Book::sum('booked');
        
        return [
            'total_books' => $totalBooks,
            'available_books' => $totalQuantity - $totalBorrowed - $totalBooked,
            'borrowed_books' => $totalBorrowed,
            'booked_books' => $totalBooked,
        ];
    }

    /**
     * Mendapatkan aktivitas terbaru user
     */
    public function getUserRecentActivity($userId, $limit = 5)
    {
        return Borrow::with('book')
                    ->where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->take($limit)
                    ->get();
    }

    /**
     * Mencari buku berdasarkan keyword
     */
    public function searchBooks(Request $request)
    {
        $keyword = $request->input('keyword');
        
        $books = Book::where('title', 'like', "%$keyword%")
                    ->orWhere('author', 'like', "%$keyword%")
                    ->orWhere('category', 'like', "%$keyword%")
                    ->orderBy('title')
                    ->get();
        
        return view('book-search', compact('books', 'keyword'));
    }

    /**
     * Mendapatkan buku berdasarkan kategori
     */
    public function getBooksByCategory($category)
    {
        $books = Book::where('category', $category)
                    ->orderBy('title')
                    ->get();
        
        return view('book-category', compact('books', 'category'));
    }

    /**
     * Mendapatkan semua kategori buku
     */
    public function getBookCategories()
    {
        return Book::select('category')
                  ->distinct()
                  ->orderBy('category')
                  ->pluck('category');
    }

    // ==============================================
    // METHOD UTILITY
    // ==============================================

    /**
     * Export data buku ke CSV
     */
    public function exportBooks()
    {
        $this->checkAdminAccess();
        
        $books = Book::all();
        $fileName = 'books_export_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function() use ($books) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, ['ID', 'Judul', 'Penulis', 'Kategori', 'Jumlah', 'Dipinjam', 'Dipesan', 'Deskripsi']);
            
            // Data buku
            foreach ($books as $book) {
                fputcsv($file, [
                    $book->id,
                    $book->title,
                    $book->author,
                    $book->category,
                    $book->quantity,
                    $book->borrowed,
                    $book->booked,
                    strip_tags($book->description)
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate laporan peminjaman
     */
    public function generateReport(Request $request)
    {
        $this->checkAdminAccess();
        
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        
        $reportData = Borrow::with(['user', 'book'])
                          ->whereBetween('created_at', [$startDate, $endDate])
                          ->orderBy('created_at', 'desc')
                          ->get();
        
        return view('admin.reports.borrow-report', compact('reportData', 'startDate', 'endDate'));
    }
}
