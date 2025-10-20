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

    /**
     * Menampilkan form untuk menambah buku dan menampilkan buku yang sudah ada.
     */
    public function create()
    {
        $this->checkAdminAccess();
        
        $books = Book::orderBy('category')
                    ->orderBy('title')
                    ->get();
        
        $book = null;

        return view('admin.books.add-book', compact('books', 'book'));
    }

    /**
     * Menampilkan form edit buku.
     */
    public function edit($id)
    {
        $this->checkAdminAccess();
        
        $book = Book::findOrFail($id);
        $books = Book::orderBy('category')
                        ->orderBy('title')
                        ->get();

        return view('admin.books.add-book', compact('books', 'book'));
    }

    /**
     * Menyimpan buku baru ke database.
     */
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

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('books', 'public');
            Log::info('Image stored at: ' . $imagePath);
        }

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

            Log::info('Book created: ', $book->toArray());

            return redirect()->route('admin.books.create')->with('success', 'Buku berhasil ditambahkan');

        } catch (\Exception $e) {
            Log::error('Error creating book: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan buku: ' . $e->getMessage());
        }
    }

    /**
     * Update buku yang sudah ada.
     */
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

        $imagePath = $book->image;
        if ($request->hasFile('image')) {
            if ($book->image && Storage::disk('public')->exists($book->image)) {
                Storage::disk('public')->delete($book->image);
            }
            $imagePath = $request->file('image')->store('books', 'public');
            Log::info('New image stored at: ' . $imagePath);
        }

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

    /**
     * Hapus buku.
     */
    public function destroy($id)
    {
        $this->checkAdminAccess();
        
        $book = Book::findOrFail($id);
        
        if ($book->image && Storage::disk('public')->exists($book->image)) {
            Storage::disk('public')->delete($book->image);
        }
        
        $book->delete();

        return redirect()->route('admin.books.create')->with('success', 'Buku berhasil dihapus');
    }

    /**
     * Halaman Buku Terbooking.
     */
    public function bookedBooks()
    {
        $this->checkAdminAccess();
        
        $bookedBooks = Borrow::with(['book', 'user'])
            ->where('status', 'booked')
            ->latest()
            ->paginate(10);

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

    /**
     * Halaman Buku yang Dipinjam untuk admin.
     */
    public function borrowedBooks()
    {
        $this->checkAdminAccess();
        
        $borrowedBooks = Borrow::with(['book', 'user'])
            ->where('status', 'borrowed')
            ->orderBy('borrow_date', 'desc')
            ->get();

        return view('admin.books.borrowed-books', compact('borrowedBooks'));
    }


    /**
     * Halaman Manajemen Denda untuk admin.
     */
    public function penaltyAdmin()
    {
        $this->checkAdminAccess();

        // 1. Total Denda Bulan Ini
        // Mengambil denda yang DIBUAT (created_at) bulan ini
        $totalPenaltiesThisMonth = Penalty::whereMonth('created_at', now()->month)
                                         ->whereYear('created_at', now()->year)
                                         ->sum('amount');
        
        // 2. Denda Tertunggak (Unpaid - KESELURUHAN)
        // Variabel ini di view Anda digunakan sebagai statistik, jadi ambil semua yang unpaid
        $overduePenalties = Penalty::where('status', 'unpaid')
                                  ->sum('amount');

        // 3. Peminjam dengan Denda
        $usersWithPenalties = Penalty::where('status', 'unpaid')
                                     ->distinct('user_id')
                                     ->count('user_id');

        // 4. Tabel Daftar Denda (Penalties)
        $penalties = Penalty::with(['user', 'borrow.book'])
                            ->orderByRaw("FIELD(status, 'unpaid', 'waived', 'paid')")
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

        // Kirim semua variabel yang dibutuhkan ke view
        return view('admin.books.penalty', compact('penalties', 'totalPenaltiesThisMonth', 'overduePenalties', 'usersWithPenalties'));
    }
    /**
     * Menandai denda sebagai 'paid' (dibayar).
     */
    public function completePenalty($id)
    {
        $this->checkAdminAccess();

        $penalty = Penalty::findOrFail($id);
        
        if ($penalty->status === 'unpaid') {
            DB::beginTransaction();
            try {
                $penalty->status = 'paid';
                $penalty->paid_date = now();
                $penalty->save();

                DB::commit();
                return redirect()->back()->with('success', 'Denda berhasil ditandai sebagai selesai.');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Gagal menyelesaikan denda: ' . $e->getMessage());
            }
        }
        
        return redirect()->back()->with('error', 'Denda sudah dibayar atau tidak valid.');
    }

    /**
     * Menghapus denda (misalnya, jika denda diabaikan/waived).
     */
    public function waivePenalty($id)
    {
        $this->checkAdminAccess();
        
        $penalty = Penalty::findOrFail($id);

        DB::beginTransaction();
        try {
            $penalty->status = 'waived';
            $penalty->notes = 'Denda dihapuskan oleh pustakawan.';
            $penalty->save();

            DB::commit();
            return redirect()->back()->with('success', 'Denda berhasil dihapuskan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus denda: ' . $e->getMessage());
        }
    }
/**
     * Menampilkan detail satu buku.
     */
    public function show($id)
    {
        // Menggunakan findOrFail untuk mengambil buku berdasarkan ID. 
        // Jika buku tidak ditemukan, Laravel akan otomatis menampilkan halaman 404.
        $book = Book::findOrFail($id);

        // Menghitung stok yang tersedia (available stock)
        $availableStock = max(0, $book->quantity - $book->borrowed - $book->booked);

        // Anda bisa menambahkan logic pengecekan atau logic lain di sini.

        // Mengirim data buku dan stok ke view
        return view('home.book-detail', compact('book', 'availableStock'));
    }
    /**
     * Konfirmasi peminjaman buku (dari booked ke borrowed).
     */
    public function confirmBorrow(Request $request, $id)
    {
        $this->checkAdminAccess();
        
        $borrow = Borrow::with('book')->findOrFail($id);
        $book = $borrow->book;

        $request->validate([
            'duration_days' => 'required|integer|min:1|max:30'
        ]);

        if ($borrow->status !== 'booked') {
            return redirect()->back()->with('error', 'Status peminjaman tidak valid untuk dikonfirmasi.');
        }

        DB::beginTransaction();
        
        try {
            if ($book->booked > 0) {
                $book->booked -= 1;
            }
            $book->borrowed += 1;
            $book->save();

            $borrow->status = 'borrowed';
            $borrow->borrow_date = now();
            
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

    /**
     * Batalkan booking.
     */
    public function cancelBooking($id)
    {
        $this->checkAdminAccess();
        
        $borrow = Borrow::findOrFail($id);
        $book = $borrow->book;
        
        if ($borrow->status !== 'booked') {
            return redirect()->back()->with('error', 'Status peminjaman tidak valid untuk dibatalkan.');
        }

        DB::beginTransaction();
        
        try {
            if ($book->booked > 0) {
                $book->booked -= 1;
                $book->save();
            }

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

 
    /**
     * Kembalikan buku.
     */
    public function returnBook($id)
    {
        $this->checkAdminAccess();

        $borrow = Borrow::with('book')->findOrFail($id);
        $book = $borrow->book;

        if ($borrow->status !== 'borrowed' && $borrow->status !== 'overdue') {
            return redirect()->back()->with('error', 'Status peminjaman tidak valid.');
        }
        
        DB::beginTransaction();
        
        try {
            $returnDate = Carbon::parse($borrow->return_date);
            $actualReturnDate = now();
            $lateDays = 0;
            $fineAmount = 0;
            
            if ($actualReturnDate->greaterThan($returnDate)) {
                $lateDays = $actualReturnDate->diffInDays($returnDate);
                $fineAmount = $lateDays * 2000;
                
                // Cek apakah denda untuk peminjaman ini sudah ada dan belum dibayar
                $existingPenalty = Penalty::where('borrow_id', $borrow->id)
                                          ->whereIn('status', ['unpaid', 'waived'])
                                          ->first();

                if (!$existingPenalty) {
                    Penalty::create([
                        'user_id' => $borrow->user_id,
                        'borrow_id' => $borrow->id,
                        'amount' => $fineAmount,
                        'reason' => 'Keterlambatan pengembalian buku selama ' . $lateDays . ' hari. (Rp2k/hari)',
                        'status' => 'unpaid',
                        // due_date denda ini bisa disesuaikan, misal 7 hari dari sekarang
                        'due_date' => $actualReturnDate->copy()->addDays(7), 
                    ]);
                } else {
                     // Jika sudah ada denda, update jumlahnya
                     $existingPenalty->update([
                        'amount' => $fineAmount,
                        'reason' => 'Keterlambatan pengembalian buku selama ' . $lateDays . ' hari. (Rp2k/hari)',
                     ]);
                }
            }
            // Logika Anda untuk return buku di bawah ini sudah benar:
            if ($book->borrowed > 0) {
                $book->borrowed -= 1;
                $book->save();
            }

            $borrow->status = 'returned';
            $borrow->actual_return_date = $actualReturnDate;
            $borrow->save();

            DB::commit();

            Log::info('Book returned: ' . $id);
            
            $message = 'Buku berhasil dikembalikan.';
            if ($lateDays > 0) {
                $message .= ' Terdapat denda sebesar Rp' . number_format($fineAmount, 0, ',', '.') . ' karena terlambat.';
            }

            return redirect()->route('admin.books.borrowed')->with('success', $message);
        
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error returning book: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengembalikan buku: ' . $e->getMessage());
        }
    }

    /**
     * Perpanjang peminjaman.
     */
    public function extendBorrow(Request $request, $id)
    {
        $this->checkAdminAccess();
        
        $request->validate([ 
            'additional_days' => 'required|integer|min:1|max:14' 
        ]);

        $borrow = Borrow::findOrFail($id);
        
        if ($borrow->status !== 'borrowed') {
            return redirect()->back()->with('error', 'Hanya peminjaman yang aktif dapat diperpanjang.');
        }

        DB::beginTransaction();
        
        try {
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
    // METHOD UNTUK ANGGOTA & DASHBOARD
    // ==============================================

 /**
 * Halaman home/dashboard dengan search functionality
 */
public function home(Request $request)
{
    $search = $request->get('search');

    if ($search) {
        // Jika ada query pencarian, REDIRECT ke route pencarian yang terpisah
        return redirect()->route('search', ['search' => $search]);
    }
    
    // Logika default home (tampilkan buku populer/terbaru, dll.)
    // Asumsi getPopularBooks ada dan mengembalikan Collection/Paginator
    $books = $this->getPopularBooks(10); 

    // --- Statistik Dashboard (TAMBAHKAN KEMBALI BAGIAN INI) ---
    // Pastikan variabel-variabel ini didefinisikan sebelum compact()
    
    // 1. Total Buku
    $totalBooks = Book::count(); 
    
    // 2. Count Buku Dipinjam (Asumsi $this->getBorrowedBooksCount() sudah ada)
    $borrowedCount = $this->getBorrowedBooksCount();

    // 3. Available Books (Hitung secara efisien di DB)
    // Jika kolom quantity, borrowed, dan booked ada di model Book
    $availableBooks = max(0, Book::sum(DB::raw('quantity - borrowed - booked')));
    
    // 4. Total User
    $totalUsers = User::count(); 

    // Aktivitas terbaru
    $recentBorrows = Borrow::with(['book', 'user'])
        ->where('status', 'borrowed')
        ->orderBy('borrow_date', 'desc')
        ->take(5)
        ->get();

    return view('home.home', compact(
        'borrowedCount', 
        'totalBooks', 
        'availableBooks', // Pastikan semua variabel di sini sudah didefinisikan
        'totalUsers',
        'recentBorrows',
        'books', 
        'search' 
    ));
}

public function search(Request $request) 
{
    // Ambil query pencarian
    $search = $request->input('search');

    if (!$search) {
        // Jika tidak ada query, redirect kembali ke home atau tampilkan view kosong
        return redirect()->route('home');
    }

    // Logika Pencarian Penuh (dipindahkan dari home())
    $books = Book::where(function($query) use ($search) {
        $query->where('title', 'like', "%{$search}%")
              ->orWhere('author', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%");
    })
    ->get()
    ->filter(function($book) {
        // Filter manual untuk buku yang available
        return ($book->quantity - $book->borrowed - $book->booked) > 0;
    });

    // Tampilkan view hasil pencarian
    // View ini akan menggunakan layout app.blade.php dan menampilkan hasil
    return view('home.search', [ // Asumsikan nama view Anda adalah search.blade.php
        'books' => $books,
        'query' => $search,
    ]);
}

    /**
     * Method untuk mendapatkan data buku yang dipinjam (untuk count di dashboard)
     */
    public function getBorrowedBooks()
    {
        try {
            return Borrow::where('status', 'borrowed')->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Method untuk mendapatkan jumlah buku yang dipinjam (lebih efisien)
     */
    public function getBorrowedBooksCount()
    {
        try {
            return Borrow::where('status', 'borrowed')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Booking buku untuk anggota.
     */
    public function booking($id, Request $request)
    {
        $validated = $request->validate([
            'duration' => 'required|integer|min:1|max:30'
        ]);

        $book = Book::findOrFail($id);
        $user = Auth::user();

        $availableStock = $book->quantity - ($book->borrowed + $book->booked);
        if ($availableStock <= 0) {
            return back()->with('error', 'Buku tidak tersedia untuk dipinjam.');
        }

        $existingBorrow = Borrow::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->whereIn('status', ['booked', 'borrowed'])
            ->first();

        if ($existingBorrow) {
            return back()->with('error', 'Anda sudah meminjam atau memesan buku ini.');
        }

        $duration = (int) $validated['duration'];

        $borrow = Borrow::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'requested_days' => $duration,
            'status' => 'booked',
            'return_date' => now()->addYears(10),
            'borrow_date' => now()->addYears(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $book->increment('booked');

        return redirect()->route('booked')->with([
            'success' => 'Booking Berhasil!',
            'message' => 'Buku "' . $book->title . '" berhasil dipesan! Silakan tunggu konfirmasi admin.'
        ]);
    }

    /**
     * Halaman buku yang di-booking oleh user.
     */
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

    /**
     * Riwayat peminjaman user.
     */
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

    /**
     * Denda user.
     */
public function userPenalties()
{
    $user = Auth::user();
    
    // Ambil data denda seperti biasa. 
    // Aksesor `is_overdue` dan `overdue_days` akan otomatis dihitung
    // saat objek $penalty diakses di view.
    $penalties = Penalty::with(['borrow.book'])
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    $totalUnpaid = Penalty::where('user_id', $user->id)
        ->where('status', 'unpaid')
        ->sum('amount');

    $totalPaid = Penalty::where('user_id', $user->id)
        ->where('status', 'paid')
        ->sum('amount');

    return view('home.penalty', compact('penalties', 'totalUnpaid', 'totalPaid'));
}

/**
 * Cek dan buat penalty real-time untuk user dengan update amount yang benar
 */
private function checkAndCreateRealTimePenalties($userId)
{
    try {
        $overdueBorrows = Borrow::with(['user', 'book'])
            ->where('user_id', $userId)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->where('return_date', '<', now())
            ->get();

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($overdueBorrows as $borrow) {
            $lateDays = now()->diffInDays(Carbon::parse($borrow->return_date));
            $fineAmount = $lateDays * 2000;

            // Cek apakah sudah ada penalty untuk borrow ini
            $existingPenalty = Penalty::where('borrow_id', $borrow->id)
                                     ->where('reason', 'late_return')
                                     ->first();

            if (!$existingPenalty) {
                // Buat penalty baru
                Penalty::create([
                    'user_id' => $borrow->user_id,
                    'borrow_id' => $borrow->id,
                    'amount' => $fineAmount,
                    'reason' => 'late_return',
                    'status' => 'unpaid',
                    'due_date' => now()->addDays(1), // Batas bayar "Segera"
                    'notes' => 'Denda keterlambatan pengembalian buku. Terlambat ' . $lateDays . ' hari. Rp 2.000/hari.'
                ]);
                $createdCount++;
                
                // Update status borrow jadi overdue
                if ($borrow->status === 'borrowed') {
                    $borrow->status = 'overdue';
                    $borrow->save();
                }
                
                Log::info("Created penalty for user {$userId}, borrow {$borrow->id}, amount: Rp {$fineAmount}");
            } else {
                // PERBAIKAN: Selalu update amount untuk denda yang masih unpaid
                if ($existingPenalty->status === 'unpaid' && $existingPenalty->amount != $fineAmount) {
                    $existingPenalty->amount = $fineAmount;
                    $existingPenalty->notes = 'Denda keterlambatan pengembalian buku. Terlambat ' . $lateDays . ' hari. Rp 2.000/hari.';
                    $existingPenalty->save();
                    $updatedCount++;
                    Log::info("Updated penalty for borrow {$borrow->id}, new amount: Rp {$fineAmount}");
                }
            }
        }
        
        Log::info("Real-time penalties processed for user {$userId}: {$createdCount} created, {$updatedCount} updated");
        return ['created' => $createdCount, 'updated' => $updatedCount];
        
    } catch (\Exception $e) {
        Log::error("Error creating real-time penalties for user {$userId}: " . $e->getMessage());
        return ['created' => 0, 'updated' => 0];
    }
}


    /**
     * Mendapatkan buku populer berdasarkan jumlah peminjaman.
     */
    public static function getPopularBooks($limit = 6)
    {
        return Book::orderBy('borrowed', 'desc')
                    ->orderBy('booked', 'desc')
                    ->take($limit)
                    ->get();
    }

    /**
     * Mendapatkan statistik buku untuk dashboard.
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
     * Mendapatkan aktivitas terbaru user.
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
     * Export data buku ke CSV.
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
            
            fputcsv($file, ['ID', 'Judul', 'Penulis', 'Kategori', 'Jumlah', 'Dipinjam', 'Dipesan', 'Deskripsi']);
            
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
     * Generate laporan peminjaman.
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
     * Otomatis update status overdue & buat denda.
     * Akan dijalankan oleh Laravel Scheduler.
     */
    public function updateOverdueStatusAndCreatePenalties()
    {
        $overdueBorrows = Borrow::where('status', 'borrowed')
                                ->where('return_date', '<', now())
                                ->get();
        
        $createdPenaltiesCount = 0;

        foreach ($overdueBorrows as $borrow) {
            DB::beginTransaction();
            try {
                $borrow->status = 'overdue';
                $borrow->save();

                $existingPenalty = Penalty::where('borrow_id', $borrow->id)->first();
                if (!$existingPenalty) {
                    $lateDays = now()->diffInDays($borrow->return_date);
                    $fineAmount = $lateDays * 2000;
                    
                    Penalty::create([
                        'user_id' => $borrow->user_id,
                        'borrow_id' => $borrow->id,
                        'amount' => $fineAmount,
                        'reason' => 'Keterlambatan pengembalian buku otomatis.',
                        'status' => 'unpaid',
                        'due_date' => now()->addDays(7),
                    ]);
                    $createdPenaltiesCount++;
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error processing overdue borrow for ID ' . $borrow->id . ': ' . $e->getMessage());
            }
        }
        
        Log::info('Updated ' . $overdueBorrows->count() . ' borrows to overdue and created ' . $createdPenaltiesCount . ' penalties.');
        return $overdueBorrows->count();
    }
}