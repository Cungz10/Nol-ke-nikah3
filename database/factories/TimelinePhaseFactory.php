<?php

namespace Database\Factories;

use App\Models\TimelinePhase;
use App\Models\WeddingProject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimelinePhase>
 */
class TimelinePhaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'wedding_project_id' => WeddingProject::factory(),
            'key' => fake()->slug(2),
            'name' => fake()->sentence(3),
            'sort_order' => fake()->numberBetween(1, 10),
            'starts_on' => now()->subMonths(6)->toDateString(),
            'ends_on' => now()->addMonths(2)->toDateString(),
        ];
    }
}
