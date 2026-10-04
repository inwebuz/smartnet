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
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('service_categories')->restrictOnDelete();
            $table->string('slug')->unique();
            $table->json('title');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('service_category_id')->nullable()->constrained()->restrictOnDelete();
        });
        Schema::create('technologies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('kind')->default('other');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('service_technology', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained()->cascadeOnDelete();
            $table->primary(['service_id', 'technology_id']);
        });
        Schema::create('case_study_technology', function (Blueprint $table) {
            $table->foreignId('case_study_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained()->cascadeOnDelete();
            $table->primary(['case_study_id', 'technology_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_study_technology');
        Schema::dropIfExists('service_technology');
        Schema::dropIfExists('technologies');
        Schema::table('services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_category_id');
        });
        Schema::dropIfExists('service_categories');
    }
};
