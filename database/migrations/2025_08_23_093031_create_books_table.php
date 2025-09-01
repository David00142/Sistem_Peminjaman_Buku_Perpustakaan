<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul buku
            $table->string('author'); // Penulis buku
            $table->text('description'); // Deskripsi buku
            $table->integer('quantity'); // Jumlah buku tersedia
            $table->string('image')->nullable(); // Gambar buku (opsional)
            $table->string('category'); // Kategori buku
            $table->integer('booked')->default(0); // Jumlah buku yang telah dipesan (default 0)
            $table->integer('borrowed')->default(0); // Jumlah buku yang telah dipinjam (default 0)
            $table->timestamps(); // Menambahkan created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books'); // Menghapus tabel jika rollback
    }
}
