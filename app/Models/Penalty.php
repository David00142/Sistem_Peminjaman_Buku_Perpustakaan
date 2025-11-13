<?php
// app/Models/Penalty.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Penalty extends Model
{
    use HasFactory;

    // ... (property dan relasi tidak diubah)

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function borrow()
    {
        return $this->belongsTo(Borrow::class);
    }

    public function book()
    {
        return $this->hasOneThrough(Book::class, Borrow::class, 'id', 'id', 'borrow_id', 'book_id');
    }

    /**
     * ✅ PERBAIKAN: Hitung denda harian menggunakan floatDiffInDays() dan ceil() 
     * untuk memastikan pembulatan ke atas (1 detik terlambat = 1 hari denda).
     */
    public function calculateDailyPenalty()
    {
        $borrow = $this->borrow;
        
        // HANYA hitung ulang jika ini denda keterlambatan dan status belum dibayar
        if (!$borrow || $this->status !== 'unpaid' || $this->reason !== 'late_return') {
            return $this->amount;
        }

        // Tanggal jatuh tempo pengembalian (termasuk waktu)
        $dueDate = Carbon::parse($borrow->return_date);
        
        // Tanggal pengembalian aktual (termasuk waktu) atau waktu saat ini
        $actualDate = $borrow->actual_return_date
            ? Carbon::parse($borrow->actual_return_date)
            : now();
        
        // Cek apakah tanggal aktual lebih lambat dari jatuh tempo
        if ($actualDate->greaterThan($dueDate)) {
            // Hitung selisih dalam hari pecahan (float)
            $floatLateDays = $actualDate->floatDiffInDays($dueDate);
            
            // Bulatkan ke atas (ceil) untuk mendapatkan hari denda. 
            // 0.001 hari = 1 hari; 1.001 hari = 2 hari.
            $lateDays = (int) ceil($floatLateDays);
        } else {
            // Jika dikembalikan tepat waktu atau lebih awal
            $lateDays = 0;
        }
        
        return $lateDays * 2000; // Rp 2.000 per hari
    }

    // Update amount secara otomatis berdasarkan keterlambatan (Tidak diubah, hanya memanggil yang diperbaiki)
    public function updatePenaltyAmount()
    {
        if ($this->status === 'unpaid' && $this->reason === 'late_return') {
            $newAmount = $this->calculateDailyPenalty();
            
            if (number_format($newAmount, 2) != number_format($this->amount, 2)) {
                $this->amount = $newAmount;
                $this->save();
            }
        }
    }

    // Cek apakah denda sudah jatuh tempo (TIDAK ADA PERUBAHAN)
    public function getIsOverdueAttribute()
    {
        // Perbandingan ini untuk due_date pembayaran denda (bukan pengembalian buku)
        return now()->greaterThan($this->due_date) && $this->status === 'unpaid';
    }

    // Hitung hari keterlambatan pembayaran (TIDAK ADA PERUBAHAN)
    public function getOverdueDaysAttribute()
    {
        if ($this->is_overdue) {
            // Tetap menggunakan diffInDays() karena ini adalah hari kalender pembayaran
            return now()->diffInDays($this->due_date); 
        }
        return 0;
    }

    /**
     * ✅ PERBAIKAN: Hitung hari keterlambatan pengembalian (untuk tampilan) 
     * Menggunakan logika yang sama dengan calculateDailyPenalty untuk konsistensi.
     */
    public function getLateDaysAttribute()
    {
        $borrow = $this->borrow;
        if (!$borrow) return 0;

        $dueDate = Carbon::parse($borrow->return_date);
        $actualReturnDate = $borrow->actual_return_date 
            ? Carbon::parse($borrow->actual_return_date) 
            : now();
            
        if ($actualReturnDate->greaterThan($dueDate)) {
            $floatLateDays = $actualReturnDate->floatDiffInDays($dueDate);
            // Pembulatan ke atas untuk keterlambatan
            return (int) ceil($floatLateDays);
        }

        return 0;
    }
    
    // ... (Sisa Scope, tidak perlu diubah)
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeWaived($query)
    {
        return $query->where('status', 'waived');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeLateReturns($query)
    {
        return $query->where('reason', 'late_return');
    }
}