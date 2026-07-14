<?php

namespace Database\Factories;

use App\Models\BudgetPlan;
use App\Models\BudgetItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetItemFactory extends Factory
{
    protected $model = BudgetItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $unitCost = $this->faker->randomFloat(2, 100000, 5000000);
        
        return [
            'budget_plan_id' => BudgetPlan::factory(),
            'category' => $this->faker->randomElement(['human_resource', 'infrastructure', 'tools', 'operational', 'contingency', 'other']),
            'description' => $this->faker->sentence(),
            'quantity' => $quantity,
            'unit' => $this->faker->randomElement(['Bulan', 'Orang', 'Unit', 'Paket']),
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost,
            'notes' => $this->faker->sentence(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
