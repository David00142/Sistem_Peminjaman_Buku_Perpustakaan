<?php
// app/Models/Borrow.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'book_id', 'borrow_date', 'return_date', 
        'actual_return_date', 'status', 'extension_count', 'notes'
    ];

    protected $casts = [
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

    // Hitung sisa hari
    public function getRemainingDaysAttribute()
    {
        return now()->diffInDays($this->return_date, false);
    }

    // Cek apakah terlambat
    public function getIsOverdueAttribute()
    {
        return now()->greaterThan($this->return_date) && $this->status !== 'returned';
    }
}