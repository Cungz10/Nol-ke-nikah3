<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\VendorCategory;
use App\Models\WeddingProject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'wedding_project_id' => WeddingProject::factory(),
            'vendor_category_id' => VendorCategory::first()?->id ?? 1,
            'name' => fake()->company(),
            'contact_person' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'instagram' => fake()->optional()->userName(),
            'website' => fake()->optional()->url(),
            'quote_amount_idr' => fake()->numberBetween(5_000_000, 100_000_000),
            'deal_amount_idr' => fake()->numberBetween(5_000_000, 100_000_000),
            'status' => fake()->randomElement(['research', 'negotiation', 'booked', 'dp_paid', 'fully_paid']),
            'notes' => fake()->optional()->sentence(),
            'follow_up_date' => fake()->optional()->dateTimeBetween('now', '+3 months'),
        ];
    }
}
