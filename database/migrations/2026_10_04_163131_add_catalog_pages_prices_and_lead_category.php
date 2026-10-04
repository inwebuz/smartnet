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
        Schema::table('service_categories', function (Blueprint $table) {
            $table->json('summary')->nullable();
            $table->json('description')->nullable();
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->unsignedBigInteger('price_from')->nullable();
            $table->string('price_currency', 3)->default('UZS');
            $table->boolean('is_published')->default(false);
        });
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedBigInteger('price_from')->nullable();
            $table->string('price_currency', 3)->default('UZS');
        });
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('service_category_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_category_id');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['price_from', 'price_currency']);
        });
        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropColumn(['summary', 'description', 'seo_title', 'seo_description', 'price_from', 'price_currency', 'is_published']);
        });
    }
};
