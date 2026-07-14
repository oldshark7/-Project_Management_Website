<?php

namespace Database\Factories;

use App\Models\RiskManagementPlan;
use App\Models\RiskItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RiskItemFactory extends Factory
{
    protected $model = RiskItem::class;

    public function definition(): array
    {
        return [
            'risk_management_plan_id' => RiskManagementPlan::factory(),
            'risk_title' => $this->faker->sentence(3),
            'risk_description' => $this->faker->paragraph(),
            'risk_cause' => $this->faker->sentence(),
            'impact' => $this->faker->paragraph(),
            'probability' => $this->faker->randomElement(['low', 'medium', 'high']),
            'severity' => $this->faker->randomElement(['low', 'medium', 'high']),
            'mitigation_plan' => $this->faker->paragraph(),
            'contingency_plan' => $this->faker->paragraph(),
            'risk_owner' => $this->faker->name(),
            'status' => 'open',
            'notes' => $this->faker->sentence(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
