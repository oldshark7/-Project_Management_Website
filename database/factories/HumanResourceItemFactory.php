<?php

namespace Database\Factories;

use App\Models\HumanResourcePlan;
use App\Models\HumanResourceItem;
use App\Models\WbsItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HumanResourceItemFactory extends Factory
{
    protected $model = HumanResourceItem::class;

    public function definition(): array
    {
        return [
            'human_resource_plan_id' => HumanResourcePlan::factory(),
            'wbs_item_id' => null, // default to null, set dynamically in tests
            'role_name' => $this->faker->jobTitle(),
            'required_skill' => $this->faker->words(3, true),
            'job_description' => $this->faker->paragraph(),
            'person_in_charge' => $this->faker->name(),
            'workload_percentage' => $this->faker->numberBetween(10, 100),
            'estimated_work_days' => $this->faker->numberBetween(5, 60),
            'quantity' => $this->faker->numberBetween(1, 5),
            'notes' => $this->faker->sentence(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
