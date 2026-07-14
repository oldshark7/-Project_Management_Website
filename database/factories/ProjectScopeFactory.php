<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectScope>
 */
class ProjectScopeFactory extends Factory
{
    protected $model = ProjectScope::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'objective' => $this->faker->paragraph(),
            'scope_description' => $this->faker->paragraph(),
            'in_scope' => "- " . implode("\n- ", $this->faker->sentences(3)),
            'out_of_scope' => "- " . implode("\n- ", $this->faker->sentences(3)),
            'main_requirements' => $this->faker->paragraph(),
            'deliverables' => $this->faker->paragraph(),
            'acceptance_criteria' => $this->faker->paragraph(),
            'assumptions' => $this->faker->paragraph(),
            'constraints' => $this->faker->paragraph(),
            'notes' => $this->faker->sentence(),
            'status' => 'draft',
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
