<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['services', 'service_categories'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('image_path')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['services', 'service_categories'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }
    }
};
