<?php
// app/Models/Book.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

    class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'author', 
        'description', 
        'quantity', 
        'image', 
        'category', 
        'borrowed', 
        'booked'
    ];

    protected $appends = ['image_url'];

    /**
     * Accessor untuk URL gambar lengkap
     */
    public function getImageUrlAttribute()
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }
        
        // Return placeholder image dari external service
        return 'https://via.placeholder.com/300x400/3B82F6/FFFFFF?text=No+Image';
    }

    /**
     * Method untuk mengecek apakah gambar ada di storage
     */
    public function hasImage()
    {
        return $this->image && Storage::disk('public')->exists($this->image);
    }
    /**
     * Method untuk mendapatkan stok yang tersedia
     */
    public function getAvailableStockAttribute()
    {
        return $this->quantity - $this->borrowed - $this->booked;
    }

    /**
     * Scope query untuk buku yang memiliki stok tersedia
     */
    public function scopeAvailable($query)
    {
        return $query->whereRaw('quantity > (borrowed + booked)');
    }

    /**
     * Scope query untuk buku yang dipinjam
     */
    public function scopeBorrowed($query)
    {
        return $query->where('borrowed', '>', 0);
    }

    /**
     * Scope query untuk buku yang dipesan
     */
    public function scopeBooked($query)
    {
        return $query->where('booked', '>', 0);
    }

    /**
     * Hubungan dengan model Borrow (jika ada)
     */
    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }

    /**
     * Boot method untuk model
     */
    protected static function boot()
    {
        parent::boot();

        // Event ketika buku dihapus
        static::deleting(function ($book) {
            // Hapus gambar jika ada
            if ($book->image && Storage::disk('public')->exists($book->image)) {
                Storage::disk('public')->delete($book->image);
            }
        });
    }
}