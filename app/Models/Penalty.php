<?php
// app/Models/Penalty.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Penalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'borrow_id',
        'amount',
        'reason',
        'status',
        'due_date',
        'paid_date',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke borrow
    public function borrow()
    {
        return $this->belongsTo(Borrow::class);
    }

    // Relasi ke book melalui borrow
    public function book()
    {
        return $this->hasOneThrough(Book::class, Borrow::class, 'id', 'id', 'borrow_id', 'book_id');
    }

    // Hitung denda harian otomatis berdasarkan keterlambatan
    public function calculateDailyPenalty()
    {
        $borrow = $this->borrow;
        
        if (!$borrow || $this->status !== 'unpaid') {
            return 0;
        }

        // Gunakan return_date dari borrow sebagai patokan
        $dueDate = $borrow->return_date;
        $actualReturnDate = $borrow->actual_return_date;
        
        // Jika buku sudah dikembalikan, hitung berdasarkan actual_return_date
        if ($actualReturnDate) {
            $lateDays = Carbon::parse($actualReturnDate)->diffInDays(Carbon::parse($dueDate));
        } else {
            // Jika belum dikembalikan, hitung berdasarkan hari ini
            $lateDays = now()->diffInDays(Carbon::parse($dueDate));
        }
        
        // Hanya hitung jika terlambat
        $lateDays = max(0, $lateDays);
        
        return $lateDays * 2000; // Rp 2.000 per hari
    }

    // Update amount secara otomatis berdasarkan keterlambatan
    public function updatePenaltyAmount()
    {
        if ($this->status === 'unpaid' && $this->reason === 'late_return') {
            $newAmount = $this->calculateDailyPenalty();
            if ($newAmount != $this->amount) {
                $this->amount = $newAmount;
                $this->save();
            }
        }
    }

    // Cek apakah denda sudah jatuh tempo
    public function getIsOverdueAttribute()
    {
        return now()->greaterThan($this->due_date) && $this->status === 'unpaid';
    }

    // Hitung hari keterlambatan pembayaran
    public function getOverdueDaysAttribute()
    {
        if ($this->is_overdue) {
            return now()->diffInDays($this->due_date);
        }
        return 0;
    }

    // Hitung hari keterlambatan pengembalian
    public function getLateDaysAttribute()
    {
        $borrow = $this->borrow;
        if (!$borrow) return 0;

        $dueDate = Carbon::parse($borrow->return_date);
        $actualReturnDate = $borrow->actual_return_date 
            ? Carbon::parse($borrow->actual_return_date) 
            : now();

        return max(0, $actualReturnDate->diffInDays($dueDate));
    }

    // Scope untuk denda yang belum dibayar
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    // Scope untuk denda yang sudah dibayar
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Scope untuk denda yang dihapuskan
    public function scopeWaived($query)
    {
        return $query->where('status', 'waived');
    }

    // Scope untuk denda user tertentu
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope untuk denda keterlambatan
    public function scopeLateReturns($query)
    {
        return $query->where('reason', 'late_return');
    }
}