<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_checklists', function (Blueprint $table) {
            $table->id();
            // null = berlaku untuk semua agama (dokumen umum)
            $table->string('religion')->nullable()->index();
            // null = tidak spesifik adat tertentu
            $table->string('tradition')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            // Versi konten — update saat ada perubahan regulasi
            $table->string('version')->default('1.0');
            $table->text('version_note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['religion', 'tradition', 'title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_checklists');
    }
};
