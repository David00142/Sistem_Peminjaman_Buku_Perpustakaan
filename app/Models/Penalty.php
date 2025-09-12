<?php
// app/Models/Penalty.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}