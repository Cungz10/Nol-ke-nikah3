<?php

use App\Models\BudgetAllocation;
use App\Models\Vendor;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(WeddingProject::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Vendor::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(BudgetAllocation::class)->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->unsignedBigInteger('amount_idr');
            $table->string('payment_type')->default('dp'); // dp, installment, full, manual_expense
            $table->date('payment_date');
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
