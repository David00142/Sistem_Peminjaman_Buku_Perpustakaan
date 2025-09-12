<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AvailableController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// Welcome Page (Redirect if already logged in)
Route::get('/', function () {
    return Auth::check() ? redirect()->route('home') : view('welcome');
})->name('welcome');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
});

// Logout Route
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Available Books Page
Route::get('/available', [AvailableController::class, 'index'])->name('available');

// Booked Books Page
Route::get('/booked', [BookController::class, 'userBookedBooks'])->name('booked')->middleware('auth');

// Borrowed user Books Page
Route::get('/borrowed-books', [BookController::class, 'userBorrowHistory'])->name('borrowed-books')->middleware('auth');

// Halaman Denda untuk user
Route::get('/penalty', [BookController::class, 'userPenalties'])->name('penalty')->middleware('auth');

// Home Route (protected by auth middleware)
Route::get('/home', function () {
    return view('home.home');
})->name('home')->middleware('auth');

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

// History Routes
Route::get('/history', [HistoryController::class, 'show'])->name('history.show')->middleware('auth');

// Book Routes - Public
Route::get('books/{id}', [BookController::class, 'show'])->name('book.show');

// Book Routes - Protected (Booking)
Route::middleware('auth')->group(function () {
    Route::post('/books/{id}/booking', [BookController::class, 'booking'])
    ->name('book.booking');
    Route::get('/book-search', [BookController::class, 'searchBooks'])->name('book.search');
    Route::get('/books/category/{category}', [BookController::class, 'getBooksByCategory'])->name('book.category');
});



// ===================
// ADMIN ROUTES
// ===================
Route::middleware('auth')->group(function () {

    // Halaman Admin Index
    Route::get('/admin', function () {
        $user = Auth::user();

        // Blokir anggota biasa
        if (!$user || ($user->role !== 'admin' && $user->role !== 'pustakawan')) {
            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman admin.');
        }

        $users = User::all(); // Ambil semua user
        return view('admin.index', compact('users'));
    })->name('admin.index');

    // Route untuk BookController - Admin
    Route::prefix('admin/books')->group(function () {
        // Menampilkan form untuk tambah buku dan daftar buku
        Route::get('/create', [BookController::class, 'create'])->name('admin.books.create');
        
        // Menampilkan form edit buku
        Route::get('/edit/{id}', [BookController::class, 'edit'])->name('admin.books.edit');
        
        // Menyimpan buku baru
        Route::post('/store', [BookController::class, 'store'])->name('admin.books.store');
        
        // Update buku yang sudah ada
        Route::put('/update/{id}', [BookController::class, 'update'])->name('admin.books.update');
        
        // Hapus buku
        Route::delete('/destroy/{id}', [BookController::class, 'destroy'])->name('admin.books.destroy');
        
        // Halaman Buku Terbooking
        Route::get('/booked', [BookController::class, 'bookedBooks'])->name('admin.books.booked');
        
        // Halaman Buku yang Dipinjam
        Route::get('/borrowed', [BookController::class, 'borrowedBooks'])->name('admin.books.borrowed');
        
        // Konfirmasi peminjaman (dari booked ke borrowed)
        Route::post('/confirm-borrow/{id}', [BookController::class, 'confirmBorrow'])->name('admin.books.confirm-borrow');
        
        // Batalkan booking
        Route::delete('/cancel-booking/{id}', [BookController::class, 'cancelBooking'])->name('admin.books.cancel-booking');
        
        // Kembalikan buku
        Route::post('/return-book/{id}', [BookController::class, 'returnBook'])->name('admin.books.return-book');
        
        // Perpanjang peminjaman
        Route::post('/extend-borrow/{id}', [BookController::class, 'extendBorrow'])->name('admin.books.extend-borrow');
        
        // Export data buku
        Route::get('/export', [BookController::class, 'exportBooks'])->name('admin.books.export');
        
        // Generate laporan
        Route::get('/report', [BookController::class, 'generateReport'])->name('admin.books.report');
    });


// Halaman Denda
Route::get('/admin/penalty', function () {
    $user = Auth::user();
    
    // Blokir anggota biasa
    if (!$user || ($user->role !== 'admin' && $user->role !== 'pustakawan')) {
        return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman admin.');
    }
    
    return view('admin.books.penalty');
})->name('admin.penalty');

    // Admin User Management Routes
    Route::prefix('admin')->group(function () {
        // Update Role User
        Route::post('/update-role/{id}', [AdminController::class, 'updateRole'])->name('admin.updateRole');

        // Update User (including kelas and role)
        Route::put('/update-user/{id}', [AdminController::class, 'updateUser'])->name('admin.updateUser');

        // Hapus User
        Route::delete('/delete-user/{id}', [AdminController::class, 'destroy'])->name('admin.deleteUser');
        
        // User management page
        Route::get('/users', [AdminController::class, 'index'])->name('admin.users.index');
    });
});


// ===================
// UTILITY ROUTES
// ===================
Route::middleware('auth')->group(function () {
    // Get user's borrowed books (API-like endpoint)
    Route::get('/api/borrowed-books', [BookController::class, 'getBorrowedBooks'])->name('api.borrowed-books');
    
    // Get popular books
    Route::get('/api/popular-books', function () {
        $bookController = new BookController();
        return response()->json($bookController->getPopularBooks());
    })->name('api.popular-books');
    
    // Get book stats
    Route::get('/api/book-stats', function () {
        $bookController = new BookController();
        return response()->json($bookController->getBookStats());
    })->name('api.book-stats');
});

// Fallback route
Route::fallback(function () {
    return redirect()->route('welcome');
});