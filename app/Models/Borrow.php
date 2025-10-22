<?php
// app/Models/Borrow.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'book_id', 
        'booking_date',      // Tambahkan untuk fitur booking
        'expiry_date',        // Tambahkan untuk batas waktu pengambilan
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

    // Hitung sisa hari untuk peminjaman
    public function getRemainingDaysAttribute()
    {
        if ($this->return_date) {
            return now()->diffInDays($this->return_date, false);
        }
        return null;
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