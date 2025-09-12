<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBookingFieldsToBorrowsTable extends Migration
{
    public function up()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dateTime('booking_date')->nullable()->after('book_id');
            $table->dateTime('expiry_date')->nullable()->after('booking_date');
        });
    }

    public function down()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn(['booking_date', 'expiry_date']);
        });
    }
}