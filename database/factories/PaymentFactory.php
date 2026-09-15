<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\WeddingProject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'wedding_project_id' => WeddingProject::factory(),
            'vendor_id' => null,
            'budget_allocation_id' => null,
            'title' => fake()->sentence(3),
            'amount_idr' => fake()->numberBetween(500_000, 50_000_000),
            'payment_type' => fake()->randomElement(['dp', 'installment', 'full', 'manual_expense']),
            'payment_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
