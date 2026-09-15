<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TimelinePhase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'timeline_phase_id' => TimelinePhase::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'sort_order' => fake()->numberBetween(1, 20),
            'is_critical' => fake()->boolean(30),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+12 months'),
            'status' => fake()->randomElement(['todo', 'in_progress', 'completed']),
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'status' => 'todo',
            'due_date' => now()->subDays(5)->toDateString(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }
}
