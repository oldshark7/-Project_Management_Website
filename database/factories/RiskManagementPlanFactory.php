<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\RiskManagementPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RiskManagementPlanFactory extends Factory
{
    protected $model = RiskManagementPlan::class;

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
