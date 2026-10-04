<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('additional_services', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('additional_service_service', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('additional_service_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('price')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['service_id', 'additional_service_id']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->json('quote_snapshot')->nullable();
        });

        foreach (['view', 'create', 'update', 'delete'] as $action) {
            DB::table('permissions')->insertOrIgnore(['resource' => 'additional_services', 'action' => $action]);
        }
    }

    public function down(): void
    {
        DB::table('permissions')->where('resource', 'additional_services')->delete();
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('quote_snapshot');
        });
        Schema::dropIfExists('additional_service_service');
        Schema::dropIfExists('additional_services');
    }
};
