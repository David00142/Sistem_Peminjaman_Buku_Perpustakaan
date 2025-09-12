<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_penalties_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenaltiesTable extends Migration
{
    public function up()
    {
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('borrow_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2); // jumlah denda
            $table->string('reason'); // alasan denda (terlambat, rusak, hilang)
            $table->enum('status', ['unpaid', 'paid', 'waived'])->default('unpaid');
            $table->date('due_date'); // batas pembayaran
            $table->date('paid_date')->nullable(); // tanggal pembayaran
            $table->text('notes')->nullable(); // catatan
            $table->timestamps();
            
            // Index untuk performa query
            $table->index('user_id');
            $table->index('borrow_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('penalties');
    }
}