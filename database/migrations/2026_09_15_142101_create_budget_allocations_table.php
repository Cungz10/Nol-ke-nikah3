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
        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(WeddingProject::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(VendorCategory::class)->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('percentage')->default(0);
            $table->unsignedBigInteger('allocated_amount_idr')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['wedding_project_id', 'vendor_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_allocations');
    }
};
