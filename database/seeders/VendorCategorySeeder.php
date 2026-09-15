<?php

namespace Database\Seeders;

use App\Models\VendorCategory;
use Illuminate\Database\Seeder;

class VendorCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Venue / Tempat', 'slug' => 'venue', 'icon' => 'building', 'default_percentage' => 20],
            ['name' => 'Catering / Konsumsi', 'slug' => 'catering', 'icon' => 'utensils', 'default_percentage' => 35],
            ['name' => 'Wedding Organizer', 'slug' => 'wo', 'icon' => 'users', 'default_percentage' => 10],
            ['name' => 'Dekorasi', 'slug' => 'dekorasi', 'icon' => 'sparkles', 'default_percentage' => 12],
            ['name' => 'Dokumentasi (Foto & Video)', 'slug' => 'dokumentasi', 'icon' => 'camera', 'default_percentage' => 8],
            ['name' => 'MUA & Attire / Busana', 'slug' => 'mua-attire', 'icon' => 'shirt', 'default_percentage' => 6],
            ['name' => 'Undangan & Souvenir', 'slug' => 'undangan-souvenir', 'icon' => 'mail', 'default_percentage' => 3],
            ['name' => 'Entertainment & Sound', 'slug' => 'entertainment', 'icon' => 'music', 'default_percentage' => 3],
            ['name' => 'Transportasi & Logistik', 'slug' => 'transportasi', 'icon' => 'car', 'default_percentage' => 1],
            ['name' => 'Cincin & Mahar', 'slug' => 'cincin-mahar', 'icon' => 'gem', 'default_percentage' => 2],
        ];

        foreach ($categories as $cat) {
            VendorCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
