<?php
// database/migrations/2025_01_XX_000002_add_soft_deletes_to_drivers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};