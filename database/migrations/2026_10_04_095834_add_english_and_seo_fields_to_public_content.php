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
        foreach (['services', 'case_studies'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->longText('description_en')->nullable();
                $table->json('seo_title')->nullable();
                $table->json('seo_description')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['services', 'case_studies'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn(['description_en', 'seo_title', 'seo_description']);
            });
        }
    }
};
