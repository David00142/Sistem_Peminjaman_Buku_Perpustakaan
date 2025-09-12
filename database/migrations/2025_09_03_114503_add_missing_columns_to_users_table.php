<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambah kolom kelas jika belum ada
            if (!Schema::hasColumn('users', 'kelas')) {
                $table->string('kelas')->nullable()->after('email');
            }
            
            // Tambah kolom role jika belum ada
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('anggota')->after('kelas');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Optional: drop columns jika perlu rollback
            if (Schema::hasColumn('users', 'kelas')) {
                $table->dropColumn('kelas');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};