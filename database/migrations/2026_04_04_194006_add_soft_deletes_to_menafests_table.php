<?php
// database/migrations/2025_01_XX_000003_add_soft_deletes_to_menafests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('menafests', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('menafests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};