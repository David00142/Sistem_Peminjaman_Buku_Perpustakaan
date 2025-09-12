<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_classes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassesTable extends Migration
{
    public function up()
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: "10 TKJ 1"
            $table->string('level'); // Contoh: "10", "11", "12"
            $table->string('program'); // Contoh: "TKJ", "AK", "BID"
            $table->integer('number'); // Contoh: 1, 2, 3
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('classes');
    }
}