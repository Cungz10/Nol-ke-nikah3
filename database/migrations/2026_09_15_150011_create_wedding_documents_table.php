<?php

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
        Schema::create('wedding_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(WeddingProject::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Vendor::class)->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('category')->default('general'); // legal_kua, legal_church, vendor_contract, invoice, rundown, other
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedding_documents');
    }
};
