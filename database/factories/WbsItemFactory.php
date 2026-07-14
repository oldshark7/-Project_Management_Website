<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectScope;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WbsItem>
 */
class WbsItemFactory extends Factory
{
    protected $model = WbsItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'project_scope_id' => ProjectScope::factory(),
            'parent_id' => null,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'deliverable' => $this->faker->sentence(),
            'priority' => 'medium',
            'estimated_duration_days' => 5,
            'status' => 'draft',
            'order_number' => 1,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
