<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDurationFieldsToBorrowsTable extends Migration
{
    public function up()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->integer('requested_days')->nullable()->after('return_date'); // Durasi yang diminta user
            $table->integer('duration_days')->nullable()->after('requested_days'); // Durasi yang disetujui admin
        });
    }

    public function down()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn(['requested_days', 'duration_days']);
        });
    }
}