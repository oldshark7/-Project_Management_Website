<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\BudgetPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetPlanFactory extends Factory
{
    protected $model = BudgetPlan::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'status' => 'draft',
            'total_budget' => 0.00,
            'notes' => $this->faker->sentence(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
