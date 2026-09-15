<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');        // created, updated, deleted
            $table->string('resource_type'); // vendor, budget_allocation, payment, task, document
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->string('description')->nullable();
            $table->json('properties')->nullable();   // snapshot perubahan
            $table->timestamps();

            $table->index(['wedding_project_id', 'resource_type']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
