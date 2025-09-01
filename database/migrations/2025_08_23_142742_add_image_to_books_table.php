<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
// database/migrations/xxxx_xx_xx_add_image_to_books_table.php

public function up()
{
    Schema::table('books', function (Blueprint $table) {
        $table->string('image')->nullable(); // Menambahkan kolom image
    });
}

public function down()
{
    Schema::table('books', function (Blueprint $table) {
        $table->dropColumn('image'); // Menghapus kolom image jika rollback
    });
}

};
