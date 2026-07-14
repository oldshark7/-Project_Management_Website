<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\HumanResourcePlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HumanResourcePlanFactory extends Factory
{
    protected $model = HumanResourcePlan::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'status' => 'draft',
            'notes' => $this->faker->sentence(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
