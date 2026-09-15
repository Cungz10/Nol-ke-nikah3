<?php

use App\Models\VendorCategory;
use App\Models\WeddingProject;
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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(WeddingProject::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(VendorCategory::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('instagram')->nullable();
            $table->string('website')->nullable();
            $table->unsignedBigInteger('quote_amount_idr')->default(0);
            $table->unsignedBigInteger('deal_amount_idr')->default(0);
            $table->string('status')->default('research'); // research, negotiation, booked, dp_paid, fully_paid, cancelled
            $table->text('notes')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
