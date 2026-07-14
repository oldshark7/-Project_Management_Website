<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectCharter>
 */
class ProjectCharterFactory extends Factory
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
            'project_purpose' => $this->faker->paragraph(),
            'business_case' => $this->faker->paragraph(),
            'project_objectives' => $this->faker->paragraph(),
            'scope_summary' => $this->faker->paragraph(),
            'success_criteria' => $this->faker->paragraph(),
            'assumptions' => $this->faker->paragraph(),
            'constraints' => $this->faker->paragraph(),
            'stakeholder_summary' => $this->faker->paragraph(),
            'milestone_summary' => $this->faker->paragraph(),
            'budget_summary' => $this->faker->randomFloat(2, 10000000, 500000000),
            'status' => 'draft',
            'feedback_notes' => null,
            'ai_suggestions' => null,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
