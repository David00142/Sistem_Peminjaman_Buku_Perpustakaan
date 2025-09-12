<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\User;
use App\Models\Penalty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        
        // Ubah get() menjadi paginate()
        $bookedBooks = Borrow::with(['book', 'user'])
            ->where('status', 'booked')
            ->latest()
            ->paginate(10); // atau jumlah per halaman yang diinginkan

        // Debug: log hasil query
        logger()->info('Booked books data:', [
            'count' => $bookedBooks->count(),
            'total' => $bookedBooks->total(),
            'books' => $bookedBooks->map(fn($b) => [
                'id' => $b->id,
                'book_title' => $b->book->title,
                'user_name' => $b->user->name,
                'requested_days' => $b->requested_days
            ])->toArray()
        ]);

        return view('admin.books.booked-books', compact('bookedBooks'));
    }
    // Halaman booked books untuk user
    public function userBookedBooks()
    {
        $user = Auth::user();
        
        Log::info('User accessing booked books: ' . $user->id);
        
        $bookedBooks = Borrow::with('book')
            ->where('user_id', $user->id)
            ->where('status', 'booked')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('home.booked-books', compact('bookedBooks'));
    }

    // Riwayat peminjaman user
    public function userBorrowHistory()
    {
        $user = Auth::user();
        
        $borrowHistory = Borrow::with('book')
            ->where('user_id', $user->id)
            ->whereIn('status', ['borrowed', 'returned', 'overdue'])
            ->orderBy('borrow_date', 'desc')
            ->paginate(10);

        return view('home.borrowed-books', compact('borrowHistory'));
    }

    // Denda user
    public function userPenalties()
    {
        $user = Auth::user();
        
        // Ambil semua denda user
        $penalties = Penalty::with(['borrow.book'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung total denda
        $totalUnpaid = Penalty::where('user_id', $user->id)
            ->where('status', 'unpaid')
            ->sum('amount');

        $totalPaid = Penalty::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount');

        return view('home.penalty', compact('penalties', 'totalUnpaid', 'totalPaid'));
    }

    // Halaman Buku yang Dipinjam untuk admin
    public function borrowedBooks()
    {
        $this->checkAdminAccess();
        
        // Ambil data buku yang sedang dipinjam (status = 'borrowed')
        $borrowedBooks = Borrow::with(['book', 'user'])
            ->where('status', 'borrowed')
            ->orderBy('borrow_date', 'desc')
            ->get();

        return view('admin.books.borrowed-books', compact('borrowedBooks'));
    }

    // Get borrowed books untuk API
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
        
        $borrow = Borrow::with('book')->findOrFail($id);
        $book = $borrow->book;

        // Validasi
        $request->validate([
            'duration_days' => 'required|integer|min:1|max:30'
        ]);

        // Pastikan status masih booked
        if ($borrow->status !== 'booked') {
            return redirect()->back()->with('error', 'Status peminjaman tidak valid untuk dikonfirmasi.');
        }

        DB::beginTransaction();
        
        try {
            // Update buku
            if ($book->booked > 0) {
                $book->booked -= 1;
            }
            $book->borrowed += 1;
            $book->save();

            // Update peminjaman
            $borrow->status = 'borrowed';
            $borrow->borrow_date = now();
            
            // **PERBAIKAN DI SINI**: Pastikan konversi ke integer
            $duration = (int) $request->duration_days;
            $returnDate = now()->copy()->addDays($duration);
            $borrow->return_date = $returnDate;
            
            $borrow->duration_days = $duration;
            $borrow->save();

            DB::commit();

            Log::info('Borrow confirmed: ', [
                'borrow_id' => $borrow->id,
                'duration_days' => $duration,
                'return_date' => $returnDate
            ]);

            return redirect()->route('admin.books.booked')->with('success', 'Peminjaman berhasil dikonfirmasi untuk ' . $duration . ' hari');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirming borrow: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengkonfirmasi peminjaman: ' . $e->getMessage());
        }
    }
    // Batalkan booking
    public function cancelBooking($id)
    {
        $this->checkAdminAccess();
        
        $borrow = Borrow::findOrFail($id);
        $book = $borrow->book;
        
        // Pastikan status masih booked
        if ($borrow->status !== 'booked') {
            return redirect()->back()->with('error', 'Status peminjaman tidak valid untuk dibatalkan.');
        }

        DB::beginTransaction();
        
        try {
            // Kurangi booked count hanya jika masih positif
            if ($book->booked > 0) {
                $book->booked -= 1;
                $book->save();
            }

            // Hapus record booking
            $borrow->delete();

            DB::commit();

            Log::info('Booking cancelled: ' . $id);

            return redirect()->route('admin.books.booked')->with('success', 'Booking berhasil dibatalkan');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error canceling booking: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membatalkan booking: ' . $e->getMessage());
        }
    }

    // Kembalikan buku
    public function returnBook($id)
    {
        $this->checkAdminAccess();
        
        $borrow = Borrow::findOrFail($id);
        $book = $borrow->book;
        
        // Pastikan status borrowed
        if ($borrow->status !== 'borrowed') {
            return redirect()->back()->with('error', 'Status peminjaman tidak valid.');
        }

        if ($book->borrowed > 0) {
            DB::beginTransaction();
            
            try {
                $book->borrowed -= 1;
                $book->save();

                // Update status pengembalian
                $borrow->status = 'returned';
                $borrow->actual_return_date = now();
                $borrow->save();

                DB::commit();

                Log::info('Book returned: ' . $id);

                return redirect()->route('admin.books.borrowed')->with('success', 'Buku berhasil dikembalikan');
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error returning book: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Gagal mengembalikan buku: ' . $e->getMessage());
            }
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
        
        // Pastikan status borrowed
        if ($borrow->status !== 'borrowed') {
            return redirect()->back()->with('error', 'Hanya peminjaman yang aktif dapat diperpanjang.');
        }

        DB::beginTransaction();
        
        try {
            // **PERBAIKAN DI SINI**: Pastikan konversi ke integer
            $additionalDays = (int) $request->additional_days;
            $borrow->return_date = Carbon::parse($borrow->return_date)->addDays($additionalDays);
            $borrow->extension_count += 1;
            $borrow->save();

            DB::commit();

            Log::info('Borrow extended: ', [
                'borrow_id' => $id,
                'additional_days' => $additionalDays,
                'new_return_date' => $borrow->return_date
            ]);

            return redirect()->back()->with('success', 'Peminjaman berhasil diperpanjang ' . $additionalDays . ' hari');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error extending borrow: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperpanjang peminjaman: ' . $e->getMessage());
        }
    }

    // ==============================================
    // METHOD UNTUK ANGGOTA
    // ==============================================

    /**
     * Menampilkan detail buku untuk anggota
     */
public function booking($id, Request $request)
{
    // Validasi input
    $validated = $request->validate([
        'duration' => 'required|integer|min:1|max:30'
    ]);

    $book = Book::findOrFail($id);
    $user = Auth::user();

    // Cek ketersediaan buku
    $availableStock = $book->quantity - ($book->borrowed + $book->booked);
    if ($availableStock <= 0) {
        return back()->with('error', 'Buku tidak tersedia untuk dipinjam.');
    }

    // Cek apakah user sudah meminjam buku yang sama
    $existingBorrow = Borrow::where('user_id', $user->id)
        ->where('book_id', $book->id)
        ->whereIn('status', ['booked', 'borrowed'])
        ->first();

    if ($existingBorrow) {
        return back()->with('error', 'Anda sudah meminjam atau memesan buku ini.');
    }

    $duration = (int) $validated['duration'];

    // Buat record peminjaman dengan status 'booked' - BERIKAN NILAI DEFAULT
    $borrow = Borrow::create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'requested_days' => $duration,
        'status' => 'booked',
        'return_date' => now()->addYears(10), // Berikan nilai default jauh di masa depan
        'borrow_date' => now()->addYears(10), // Berikan nilai default
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Update jumlah buku yang dipesan
    $book->increment('booked');

    // Redirect dengan pesan sukses
    return redirect()->route('booked')->with([
        'success' => 'Booking Berhasil!',
        'message' => 'Buku "' . $book->title . '" berhasil dipesan! Silakan tunggu konfirmasi admin.'
    ]);
}

    /**
     * Mendapatkan buku populer berdasarkan jumlah peminjaman
     */
    public static function getPopularBooks($limit = 6)
    {
        return Book::orderBy('borrowed', 'desc')
                        ->orderBy('booked', 'desc')
                        ->take($limit)
                        ->get();
    }

    /**
     * Mendapatkan statistik buku untuk dashboard
     */
    public static function getBookStats()
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
    public static function getUserRecentActivity($userId, $limit = 5)
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

    /**
     * Otomatis update status overdue
     */
    public function updateOverdueStatus()
    {
        $overdueBorrows = Borrow::where('status', 'borrowed')
                                ->where('return_date', '<', now())
                                ->get();
        
        foreach ($overdueBorrows as $borrow) {
            $borrow->status = 'overdue';
            $borrow->save();
        }
        
        return $overdueBorrows->count();
    }
}