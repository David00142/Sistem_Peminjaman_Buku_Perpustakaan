<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_borrows_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBorrowsTable extends Migration
{
    public function up()
    {
        Schema::create('borrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->date('borrow_date');
            $table->date('return_date');
            $table->date('actual_return_date')->nullable();
            $table->enum('status', ['booked', 'borrowed', 'returned', 'overdue'])->default('booked');
            $table->integer('extension_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Index untuk performa query
            $table->index('user_id');
            $table->index('book_id');
            $table->index('status');
            $table->index('borrow_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('borrows');
    }
}