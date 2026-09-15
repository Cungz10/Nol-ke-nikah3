<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\WeddingProject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeddingProject>
 */
class WeddingProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'partner_one_name' => fake()->firstName(),
            'partner_two_name' => fake()->firstName(),
            'target_date' => now()->addMonths(8)->toDateString(),
            'budget_total' => fake()->numberBetween(50_000_000, 500_000_000),
            'guest_count' => fake()->numberBetween(100, 1000),
            'city' => fake()->city(),
            'religion' => fake()->randomElement(['islam', 'kristen', 'katolik', 'hindu', 'budha']),
            'tradition' => fake()->randomElement(['jawa', 'sunda', 'batak', 'minang', 'bali', null]),
            'status' => 'active',
        ];
    }

    public function withoutTargetDate(): static
    {
        return $this->state(fn () => ['target_date' => null]);
    }
}
