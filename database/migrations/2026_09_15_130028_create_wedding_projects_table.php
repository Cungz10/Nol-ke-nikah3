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
        Schema::create('wedding_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('partner_one_name');
            $table->string('partner_two_name')->nullable();
            $table->date('target_date')->nullable();
            $table->unsignedBigInteger('budget_total');
            $table->unsignedInteger('guest_count')->nullable();
            $table->string('city');
            $table->string('religion')->nullable();
            $table->string('tradition')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedding_projects');
    }
};
