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
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
});

// Logout Route
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth'); // Ensure logout works for authenticated users

// Available Books Page
Route::get('/available', [AvailableController::class, 'index'])->name('available');

// Home Route (protected by auth middleware)
Route::get('/home', function () {
    return view('home.home');
})->name('home')->middleware('auth');

// Profile Routes
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show')->middleware('auth');

// History Routes
Route::get('/history', [HistoryController::class, 'show'])->name('history.show')->middleware('auth');

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

    // Route untuk BookController
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
        
        // Konfirmasi peminjaman
        Route::post('/confirm-borrow/{id}', [BookController::class, 'confirmBorrow'])->name('admin.books.confirm-borrow');
        
        // Batalkan booking
        Route::delete('/cancel-booking/{id}', [BookController::class, 'cancelBooking'])->name('admin.books.cancel-booking');
        
        // Kembalikan buku
        Route::post('/return-book/{id}', [BookController::class, 'returnBook'])->name('admin.books.return-book');
        
        // Perpanjang peminjaman
        Route::post('/extend-borrow/{id}', [BookController::class, 'extendBorrow'])->name('admin.books.extend-borrow');
    });

    // Update Role User
    Route::post('/admin/update-role/{id}', [AdminController::class, 'updateRole'])->name('admin.updateRole');

    // Update User (including kelas and role)
    Route::put('/admin/update-user/{id}', [AdminController::class, 'updateUser'])->name('admin.updateUser');

    // Hapus User
    Route::delete('/admin/delete-user/{id}', [AdminController::class, 'destroy'])->name('admin.deleteUser');
});

Route::get('books/{id}', [BookController::class, 'show'])->name('book.show');
