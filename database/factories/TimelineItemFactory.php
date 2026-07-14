<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\TimelineItem;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimelineItem>
 */
class TimelineItemFactory extends Factory
{
    protected $model = TimelineItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'wbs_item_id' => WbsItem::factory(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'duration_days' => 6,
            'dependency_wbs_item_id' => null,
            'is_milestone' => false,
            'milestone_name' => null,
            'notes' => $this->faker->sentence(),
            'status' => 'draft',
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
