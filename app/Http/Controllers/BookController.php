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

    // ==============================================
    // PERBAIKAN SISTEM PENALTY
    // ==============================================


/**
 * Halaman Manajemen Denda untuk admin - MENGGUNAKAN SISTEM YANG SAMA DENGAN ANGGOTA
 */
public function penaltyAdmin()
{
    $this->checkAdminAccess();

    // PROSES OTOMATIS: Gunakan sistem yang sama dengan anggota
    $this->processAllUsersOverdueBorrows();

    // 1. TOTAL DENDA BULAN INI (dibuat bulan ini, semua status)
    $totalPenaltiesThisMonth = Penalty::whereYear('created_at', now()->year)
                                     ->whereMonth('created_at', now()->month)
                                     ->sum('amount');

    // 2. DENDA BELUM DIBAYAR (unpaid, semua bulan)
    $overduePenalties = Penalty::where('status', 'unpaid')
                              ->sum('amount');

    // 3. PEMINJAM DENGAN DENDA (hitung distinct user yang punya denda unpaid)
    $usersWithPenalties = Penalty::where('status', 'unpaid')
                                 ->distinct('user_id')
                                 ->count('user_id');

    // 4. TABEL DAFTAR DENDA
    $penalties = Penalty::with(['user', 'borrow.book'])
                        ->orderByRaw("FIELD(status, 'unpaid', 'waived', 'paid')")
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

    return view('admin.books.penalty', compact(
        'penalties', 
        'totalPenaltiesThisMonth', 
        'overduePenalties', 
        'usersWithPenalties'
    ));
}

/**
 * PROSES SEMUA PEMINJAMAN OVERDUE UNTUK SEMUA USER - DENGAN PERHITUNGAN MUTLAK
 */
private function processAllUsersOverdueBorrows()
{
    try {
        $overdueBorrows = Borrow::with(['user', 'book'])
                               ->whereIn('status', ['borrowed', 'overdue'])
                               ->where('return_date', '<', now())
                               ->get();

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($overdueBorrows as $borrow) {
            DB::beginTransaction();

            try {
                // Update status menjadi overdue jika masih borrowed
                if ($borrow->status === 'borrowed') {
                    $borrow->status = 'overdue';
                    $borrow->save();
                }

                // PERBAIKAN: Hitung hari keterlambatan dengan cara MUTLAK
                $returnDate = Carbon::parse($borrow->return_date)->startOfDay();
                $today = now()->startOfDay();
                
                // Pastikan tanggal return sudah lewat dari hari ini
                if ($today->greaterThan($returnDate)) {
                    // PERBAIKAN: Gunakan perhitungan hari absolut
                    $lateDays = $this->calculateAbsoluteLateDays($returnDate, $today);
                    
                    // PERBAIKAN: Gunakan 2.000 per hari
                    $fineAmount = $lateDays * 2000;

                    // DEBUG: Log perhitungan
                    Log::info("Penalty Calculation - Borrow ID: {$borrow->id}, Return Date: {$returnDate}, Today: {$today}, Late Days: {$lateDays}, Amount: {$fineAmount}");

                    // Cek apakah sudah ada denda untuk borrow ini
                    $existingPenalty = Penalty::where('borrow_id', $borrow->id)
                                             ->where('reason', 'late_return')
                                             ->first();

                    if (!$existingPenalty) {
                        // Buat denda baru
                        Penalty::create([
                            'user_id' => $borrow->user_id,
                            'borrow_id' => $borrow->id,
                            'amount' => $fineAmount,
                            'reason' => 'late_return',
                            'status' => 'unpaid',
                            'due_date' => now()->addDays(1),
                            'notes' => 'Terlambat ' . $lateDays . ' hari. Rp 2.000/hari.'
                        ]);
                        $createdCount++;
                    } else {
                        // Update denda yang sudah ada hanya jika jumlah berubah
                        if ($existingPenalty->status === 'unpaid' && $existingPenalty->amount != $fineAmount) {
                            $existingPenalty->amount = $fineAmount;
                            $existingPenalty->notes = 'Terlambat ' . $lateDays . ' hari. Rp 2.000/hari.';
                            $existingPenalty->save();
                            $updatedCount++;
                        }
                    }
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error processing overdue borrow ID {$borrow->id}: " . $e->getMessage());
            }
        }

        Log::info("Admin Penalty: Processed {$createdCount} created, {$updatedCount} updated");
        return ['created' => $createdCount, 'updated' => $updatedCount];

    } catch (\Exception $e) {
        Log::error('Error processing overdue borrows: ' . $e->getMessage());
        return ['created' => 0, 'updated' => 0];
    }
}

/**
 * Hitung hari keterlambatan secara MUTLAK (tanpa koma, absolut)
 */
private function calculateAbsoluteLateDays($returnDate, $actualDate = null)
{
    $returnDate = Carbon::parse($returnDate)->startOfDay();
    $actualDate = $actualDate ? Carbon::parse($actualDate)->startOfDay() : now()->startOfDay();
    
    // Jika masih dalam hari yang sama atau sebelum, tidak ada denda
    if ($actualDate->lte($returnDate)) {
        return 0;
    }
    
    // PERBAIKAN: Gunakan diffInDays() yang memberikan hasil integer absolut
    $lateDays = $returnDate->diffInDays($actualDate);
    
    // Pastikan hasilnya integer dan minimal 1
    return max(1, (int)$lateDays);
}

/**
 * Get detail penalty untuk modal dengan perhitungan MUTLAK
 */
public function getPenaltyDetail($id)
{
    $this->checkAdminAccess();

    $penalty = Penalty::with(['user', 'borrow.book'])
        ->findOrFail($id);

    // PERBAIKAN: Hitung hari keterlambatan dengan cara MUTLAK
    $lateDays = 0;
    if ($penalty->reason === 'late_return' && $penalty->borrow && $penalty->borrow->return_date) {
        $dueDate = Carbon::parse($penalty->borrow->return_date)->startOfDay();
        
        if ($penalty->borrow->actual_return_date) {
            $actualReturnDate = Carbon::parse($penalty->borrow->actual_return_date)->startOfDay();
        } else {
            $actualReturnDate = now()->startOfDay();
        }
        
        if ($actualReturnDate->greaterThan($dueDate)) {
            // PERBAIKAN: Gunakan perhitungan hari absolut
            $lateDays = $this->calculateAbsoluteLateDays($dueDate, $actualReturnDate);
        }
    }

    return response()->json([
        'user_name' => $penalty->user->name ?? 'User Tidak Ditemukan',
        'user_email' => $penalty->user->email ?? '-',
        'book_title' => $penalty->borrow->book->title ?? 'Buku Tidak Ditemukan',
        'book_author' => $penalty->borrow->book->author ?? '-',
        'borrow_date' => optional($penalty->borrow->borrow_date)->format('d M Y') ?? '-',
        'return_date' => optional($penalty->borrow->return_date)->format('d M Y') ?? '-',
        'late_days' => $lateDays,
        'amount' => $penalty->amount,
        'reason' => $penalty->reason === 'late_return' ? 'Keterlambatan Pengembalian' : 
                   ($penalty->reason === 'damaged' ? 'Buku Rusak' : 
                   ($penalty->reason === 'lost' ? 'Buku Hilang' : $penalty->reason)),
        'notes' => $penalty->notes,
        'status' => $penalty->status
    ]);
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
        $book = Book::findOrFail($id);
        $availableStock = max(0, $book->quantity - $book->borrowed - $book->booked);

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
 * Kembalikan buku dengan sistem denda otomatis yang benar.
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
        $returnDate = Carbon::parse($borrow->return_date)->startOfDay();
        $actualReturnDate = now()->startOfDay();
        $lateDays = 0;
        $fineAmount = 0;
        
        // Hitung keterlambatan dengan benar - MUTLAK
        if ($actualReturnDate->greaterThan($returnDate)) {
            // PERBAIKAN: Gunakan perhitungan hari absolut
            $lateDays = $this->calculateAbsoluteLateDays($returnDate, $actualReturnDate);
            // PERBAIKAN: Gunakan 2.000 per hari
            $fineAmount = $lateDays * 2000;
            
            // Cek apakah denda untuk peminjaman ini sudah ada
            $existingPenalty = Penalty::where('borrow_id', $borrow->id)
                                      ->where('reason', 'late_return')
                                      ->first();

            if (!$existingPenalty) {
                // Buat denda baru
                Penalty::create([
                    'user_id' => $borrow->user_id,
                    'borrow_id' => $borrow->id,
                    'amount' => $fineAmount,
                    'reason' => 'late_return',
                    'status' => 'unpaid',
                    'due_date' => $actualReturnDate->copy()->addDays(7),
                    'notes' => 'Keterlambatan pengembalian buku selama ' . $lateDays . ' hari. (Rp 2.000/hari)'
                ]);
            } else {
                // Update denda yang sudah ada
                $existingPenalty->update([
                    'amount' => $fineAmount,
                    'notes' => 'Keterlambatan pengembalian buku selama ' . $lateDays . ' hari. (Rp 2.000/hari)',
                ]);
            }
        }

        // Update status buku
        if ($book->borrowed > 0) {
            $book->borrowed -= 1;
            $book->save();
        }

        $borrow->status = 'returned';
        $borrow->actual_return_date = now(); // Simpan dengan timestamp lengkap
        $borrow->save();

        DB::commit();

        Log::info('Book returned: ' . $id);
        
        $message = 'Buku berhasil dikembalikan.';
        if ($lateDays > 0) {
            $message .= ' Terdapat denda keterlambatan sebesar Rp' . number_format($fineAmount, 0, ',', '.') . ' (' . $lateDays . ' hari).';
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
            return redirect()->route('search', ['search' => $search]);
        }
        
        $books = $this->getPopularBooks(10); 

        // Statistik Dashboard
        $totalBooks = Book::count(); 
        $borrowedCount = $this->getBorrowedBooksCount();
        $availableBooks = max(0, Book::sum(DB::raw('quantity - borrowed - booked')));
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
            'availableBooks',
            'totalUsers',
            'recentBorrows',
            'books', 
            'search' 
        ));
    }

    public function search(Request $request) 
    {
        $search = $request->input('search');

        if (!$search) {
            return redirect()->route('home');
        }

        $books = Book::where(function($query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
        })
        ->get()
        ->filter(function($book) {
            return ($book->quantity - $book->borrowed - $book->booked) > 0;
        });

        return view('home.search', [
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
 * Denda user dengan perhitungan real-time
 */
public function userPenalties()
{
    $user = Auth::user();
    
    // PROSES OTOMATIS: Cek dan buat penalty untuk peminjaman yang overdue
    $this->checkAndCreateRealTimePenalties($user->id);
    
    $penalties = Penalty::with(['borrow.book'])
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    // PERBAIKAN: Hitung total unpaid dengan benar
    $totalUnpaid = 0;
    
    foreach ($penalties as $penalty) {
        if ($penalty->status === 'unpaid') {
            // Untuk penalty late_return, gunakan amount dari database (sudah di-update oleh sistem)
            if ($penalty->reason === 'late_return') {
                $totalUnpaid += $penalty->amount;
            } else {
                $totalUnpaid += $penalty->amount;
            }
        }
    }

    // Alternatif lebih sederhana:
    // $totalUnpaid = Penalty::where('user_id', $user->id)
    //     ->where('status', 'unpaid')
    //     ->sum('amount');

    $totalPaid = Penalty::where('user_id', $user->id)
        ->where('status', 'paid')
        ->sum('amount');

    return view('home.penalty', compact('penalties', 'totalUnpaid', 'totalPaid'));
}
/**
 * Cek dan buat penalty real-time untuk user dengan perhitungan MUTLAK
 */
private function checkAndCreateRealTimePenalties($userId)
{
    try {
        $overdueBorrows = Borrow::with(['user', 'book'])
            ->where('user_id', $userId)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->where('return_date', '<', now())
            ->get();

        foreach ($overdueBorrows as $borrow) {
            // PERBAIKAN: Hitung hari keterlambatan dengan cara MUTLAK
            $returnDate = Carbon::parse($borrow->return_date)->startOfDay();
            $today = now()->startOfDay();
            
            if ($today->greaterThan($returnDate)) {
                // PERBAIKAN: Gunakan perhitungan hari absolut
                $lateDays = $this->calculateAbsoluteLateDays($returnDate, $today);
                // PERBAIKAN: Gunakan 2.000 per hari
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
                        'due_date' => now()->addDays(1),
                        'notes' => 'Terlambat ' . $lateDays . ' hari. Rp 2.000/hari.'
                    ]);
                    
                    // Update status borrow jadi overdue
                    if ($borrow->status === 'borrowed') {
                        $borrow->status = 'overdue';
                        $borrow->save();
                    }
                    
                    Log::info("Created real-time penalty for user {$userId}, borrow {$borrow->id}, late days: {$lateDays}");
                } else {
                    // Update penalty yang sudah ada
                    if ($existingPenalty->amount != $fineAmount) {
                        $existingPenalty->amount = $fineAmount;
                        $existingPenalty->notes = 'Terlambat ' . $lateDays . ' hari. Rp 2.000/hari.';
                        $existingPenalty->save();
                    }
                }
            }
        }
        
        return $overdueBorrows->count();
        
    } catch (\Exception $e) {
        Log::error("Error creating real-time penalties for user {$userId}: " . $e->getMessage());
        return 0;
    }
}
    /**
     * Update denda keterlambatan untuk user tertentu
     */
private function updateUserLatePenalties($userId)
{
    try {
        $latePenalties = Penalty::with(['borrow'])
                               ->where('user_id', $userId)
                               ->where('reason', 'late_return')
                               ->where('status', 'unpaid')
                               ->get();

        foreach ($latePenalties as $penalty) {
            $borrow = $penalty->borrow;
            if (!$borrow) continue;

            $dueDate = Carbon::parse($borrow->return_date);
            $actualReturnDate = $borrow->actual_return_date 
                ? Carbon::parse($borrow->actual_return_date) 
                : now();

            $lateDays = max(0, $actualReturnDate->diffInDays($dueDate));
            $newAmount = $lateDays * 2000;

            if ($newAmount != $penalty->amount) {
                $penalty->amount = $newAmount;
                $penalty->save();
            }
        }

    } catch (\Exception $e) {
        Log::error("Error updating user {$userId} late penalties: " . $e->getMessage());
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

    // /**
    //  * Generate laporan peminjaman.
    //  */
    // public function generateReport(Request $request)
    // {
    //     $this->checkAdminAccess();
        
    //     $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
    //     $endDate = $request->input('end_date', now()->format('Y-m-d'));
        
    //     $reportData = Borrow::with(['user', 'book'])
    //                         ->whereBetween('created_at', [$startDate, $endDate])
    //                         ->orderBy('created_at', 'desc')
    //                         ->get();
        
    //     return view('admin.reports.borrow-report', compact('reportData', 'startDate', 'endDate'));
    // }

    /**
     * Otomatis update status overdue & buat denda.
     * Akan dijalankan oleh Laravel Scheduler.
     */
/**
 * Otomatis update status overdue & buat denda - UNTUK SCHEDULER
 */
public function updateOverdueStatusAndCreatePenalties()
{
    $result = $this->processAllUsersOverdueBorrows();
    
    Log::info('Scheduler: Processed overdue borrows for all users', [
        'penalties_created' => $result['created'],
        'penalties_updated' => $result['updated']
    ]);
    
    return $result['created'] + $result['updated'];
}
}