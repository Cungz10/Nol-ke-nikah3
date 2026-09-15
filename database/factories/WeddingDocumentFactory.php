<?php

namespace Database\Factories;

use App\Models\WeddingDocument;
use App\Models\WeddingProject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeddingDocument>
 */
class WeddingDocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'wedding_project_id' => WeddingProject::factory(),
            'vendor_id' => null,
            'title' => fake()->sentence(3),
            'category' => fake()->randomElement(['general', 'vendor_contract', 'invoice', 'legal_kua', 'rundown']),
            'file_path' => 'wedding_documents/1/'.fake()->uuid().'.pdf',
            'file_name' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => fake()->numberBetween(50_000, 5_000_000),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
