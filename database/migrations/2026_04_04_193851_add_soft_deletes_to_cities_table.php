<?php
// database/migrations/2025_01_XX_000001_add_soft_deletes_to_cities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->softDeletes(); // Adds deleted_at column
        });
    }

    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};