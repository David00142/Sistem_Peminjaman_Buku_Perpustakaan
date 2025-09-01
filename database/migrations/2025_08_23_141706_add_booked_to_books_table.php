<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
// database/migrations/xxxx_xx_xx_add_booked_to_books_table.php

public function up()
{
    Schema::table('books', function (Blueprint $table) {
        $table->integer('booked')->default(0); // Menambahkan kolom 'booked' dengan default 0
    });
}

public function down()
{
    Schema::table('books', function (Blueprint $table) {
        $table->dropColumn('booked'); // Menghapus kolom 'booked' jika rollback
    });
}

};
