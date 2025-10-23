<?php
// app/Models/Borrow.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Pastikan Carbon di-import

class Borrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'book_id', 
        'booking_date',      // Tambahkan untuk fitur booking
        'expiry_date',       // Tambahkan untuk batas waktu pengambilan
        'borrow_date', 
        'return_date', 
        'actual_return_date', 
        'status', 
        'extension_count', 
        'notes',
        'requested_days',    // Pastikan ini ada
        'duration_days'      // Pastikan ini ada
    ];

    protected $casts = [
        'booking_date' => 'datetime',     
        'expiry_date' => 'datetime',        
        'borrow_date' => 'date',
        'return_date' => 'date',
        'actual_return_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
    
    // ===================================
    // RELASI BARU UNTUK MEMPERBAIKI ERROR
    // ===================================
    public function penalties()
    {
        // Satu Borrow memiliki banyak Penalty
        return $this->hasMany(Penalty::class, 'borrow_id');
    }
    // ===================================

    // Hitung sisa hari untuk peminjaman (PERBAIKAN PEMBULATAN 24 JAM)
    public function getRemainingDaysAttribute()
    {
        if (!$this->return_date || in_array($this->status, ['returned', 'overdue'])) {
            return null;
        }
        
        $dueDate = Carbon::parse($this->return_date);
        $today = now();

        // 1. Cek jika sudah terlewat (jika iya, anggap 0 atau null, atau biarkan getIsOverdueAttribute yang menangani)
        if ($today->greaterThan($dueDate)) {
            return 0; 
        }

        // 2. Konversi Batas Kembali dan Hari Ini ke awal hari (00:00:00)
        $dueDateStart = $dueDate->copy()->startOfDay();
        $todayStart = $today->copy()->startOfDay();

        // 3. Hitung selisih hari penuh
        $remainingDays = $todayStart->diffInDays($dueDateStart, false);

        // Jika hasilnya > 0 (masih ada hari penuh tersisa), kembalikan hasilnya.
        if ($remainingDays > 0) {
            return $remainingDays;
        }

        if ($dueDate->isSameDay($today) && $today->lessThan($dueDate)) {
             return 1;
        }

        return 0;
    }

    // Hitung sisa waktu untuk pengambilan booking
    public function getRemainingBookingTimeAttribute()
    {
        if ($this->expiry_date) {
            return now()->diffInHours($this->expiry_date, false);
        }
        return null;
    }

    // Cek apakah terlambat mengembalikan
    public function getIsOverdueAttribute()
    {
        return now()->greaterThan($this->return_date) && $this->status !== 'returned';
    }

    // Cek apakah booking kadaluarsa
    public function getIsBookingExpiredAttribute()
    {
        return $this->status === 'booked' && now()->greaterThan($this->expiry_date);
    }

    // Cek apakah booking masih aktif
    public function getIsBookingActiveAttribute()
    {
        return $this->status === 'booked' && now()->lessThanOrEqualTo($this->expiry_date);
    }

    // Scope untuk booking yang aktif
    public function scopeActiveBookings($query)
    {
        return $query->where('status', 'booked')
                     ->where('expiry_date', '>', now());
    }

    // Scope untuk booking yang kadaluarsa
    public function scopeExpiredBookings($query)
    {
        return $query->where('status', 'booked')
                     ->where('expiry_date', '<=', now());
    }

    // Scope untuk peminjaman aktif
    public function scopeActiveBorrows($query)
    {
        return $query->where('status', 'borrowed');
    }

    // Scope untuk riwayat yang sudah dikembalikan
    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }
}
