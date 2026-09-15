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
        Schema::create('timeline_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_project_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->unsignedSmallInteger('sort_order');
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->timestamps();

            $table->unique(['wedding_project_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_phases');
    }
};
