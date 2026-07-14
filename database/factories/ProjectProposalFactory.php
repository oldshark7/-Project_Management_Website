<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectProposal>
 */
class ProjectProposalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'background' => $this->faker->paragraph(),
            'objectives' => $this->faker->paragraph(),
            'initial_needs' => $this->faker->paragraph(),
            'project_overview' => $this->faker->paragraph(),
            'scope_overview' => $this->faker->paragraph(),
            'estimated_budget' => $this->faker->randomFloat(2, 5000000, 100000000),
            'status' => 'draft',
            'feedback_notes' => null,
            'ai_suggestions' => null,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
