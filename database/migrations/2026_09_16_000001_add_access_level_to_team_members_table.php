<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            // 'full' = akses penuh (pasangan, keluarga inti, WO)
            // 'read_only' = hanya bisa melihat (keluarga jauh, tamu undangan tertentu)
            $table->string('access_level')->default('full')->after('role');
            $table->string('relation_label')->nullable()->after('access_level');
            // contoh: 'Pasangan', 'Ibu', 'Wedding Organizer', dll.
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn(['access_level', 'relation_label']);
        });
    }
};
