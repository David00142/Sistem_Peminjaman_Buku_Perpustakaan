<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan kolom untuk menyimpan path foto profil.
            // Kolom ini dibuat nullable karena pengguna mungkin tidak mengunggah foto.
            // 'after('password')' menempatkan kolom ini setelah kolom password di tabel.
            $table->string('profile_photo_path', 2048)->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback (dibatalkan).
            $table->dropColumn('profile_photo_path');
        });
    }
};
