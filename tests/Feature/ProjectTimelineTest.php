<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectScope;
use App\Models\User;
use App\Models\WbsItem;
use App\Models\TimelineItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTimelineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest redirection on Timeline routes.
     */
    public function test_guests_cannot_access_timeline(): void
    {
        $project = Project::factory()->create(['status' => 'planning']);
        $wbsItem = WbsItem::factory()->create(['project_id' => $project->id]);
        $timelineItem = TimelineItem::factory()->create([
            'project_id' => $project->id,
            'wbs_item_id' => $wbsItem->id,
        ]);

        $this->get(route('project-planning.timeline.index'))->assertRedirect('/login');
        $this->get(route('projects.timeline.show', $project->id))->assertRedirect('/login');
        $this->get(route('projects.timeline.create', $project->id))->assertRedirect('/login');
        $this->post(route('projects.timeline.store', $project->id))->assertRedirect('/login');
        $this->get(route('projects.timeline.edit', [$project->id, $timelineItem->id]))->assertRedirect('/login');
        $this->put(route('projects.timeline.update', [$project->id, $timelineItem->id]))->assertRedirect('/login');
        $this->delete(route('projects.timeline.destroy', [$project->id, $timelineItem->id]))->assertRedirect('/login');
        $this->post(route('projects.timeline.finalize', $project->id))->assertRedirect('/login');
    }

    /**
     * Test PMO has full access to CRUD and finalize Timeline.
     */
    public function test_pmo_can_crud_and_finalize_timeline(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $manager = User::factory()->create(['role' => 'Manager']);

        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        $scope = ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        
        // Finalize WBS first
        $wbs1 = WbsItem::factory()->create(['project_id' => $project->id, 'project_scope_id' => $scope->id, 'status' => 'finalized']);
        $wbs2 = WbsItem::factory()->create(['project_id' => $project->id, 'project_scope_id' => $scope->id, 'status' => 'finalized']);

        // 1. PMO can view create form
        $this->actingAs($pmo)->get(route('projects.timeline.create', $project->id))
            ->assertStatus(200);

        // 2. PMO can store TimelineItem
        $response = $this->actingAs($pmo)->post(route('projects.timeline.store', $project->id), [
            'wbs_item_id' => $wbs1->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05', // inclusive duration = 5 days
            'is_milestone' => 0,
            'notes' => 'Some note',
        ]);
        $response->assertRedirect(route('projects.timeline.show', $project->id));
        $this->assertDatabaseHas('timeline_items', [
            'project_id' => $project->id,
            'wbs_item_id' => $wbs1->id,
            'duration_days' => 5,
            'status' => 'draft',
        ]);

        $item1 = TimelineItem::where('wbs_item_id', $wbs1->id)->first();

        // 3. PMO can store timeline item with dependency
        $response = $this->actingAs($pmo)->post(route('projects.timeline.store', $project->id), [
            'wbs_item_id' => $wbs2->id,
            'start_date' => '2026-06-06', // on/after predecessor end_date (2026-06-05) -> valid
            'end_date' => '2026-06-10',
            'is_milestone' => 1,
            'milestone_name' => 'Release 1.0',
            'dependency_wbs_item_id' => $wbs1->id,
        ]);
        $this->assertDatabaseHas('timeline_items', [
            'project_id' => $project->id,
            'wbs_item_id' => $wbs2->id,
            'dependency_wbs_item_id' => $wbs1->id,
            'is_milestone' => 1,
            'milestone_name' => 'Release 1.0',
        ]);

        $item2 = TimelineItem::where('wbs_item_id', $wbs2->id)->first();

        // 4. PMO can edit and update
        $this->actingAs($pmo)->get(route('projects.timeline.edit', [$project->id, $item2->id]))
            ->assertStatus(200);

        $response = $this->actingAs($pmo)->put(route('projects.timeline.update', [$project->id, $item2->id]), [
            'wbs_item_id' => $wbs2->id,
            'start_date' => '2026-06-07',
            'end_date' => '2026-06-11',
            'is_milestone' => 0, // toggle milestone off
            'dependency_wbs_item_id' => $wbs1->id,
        ]);
        $response->assertRedirect(route('projects.timeline.show', $project->id));
        $item2 = $item2->fresh();
        $this->assertEquals('2026-06-07', $item2->start_date->format('Y-m-d'));
        $this->assertFalse($item2->is_milestone);

        // 5. PMO can delete draft item
        $response = $this->actingAs($pmo)->delete(route('projects.timeline.destroy', [$project->id, $item2->id]));
        $response->assertRedirect(route('projects.timeline.show', $project->id));
        $this->assertDatabaseMissing('timeline_items', ['id' => $item2->id]);

        // Re-store wbs2 timeline to make timeline complete for finalization
        $this->actingAs($pmo)->post(route('projects.timeline.store', $project->id), [
            'wbs_item_id' => $wbs2->id,
            'start_date' => '2026-06-06',
            'end_date' => '2026-06-10',
            'is_milestone' => 0,
        ]);

        // 6. PMO can finalize Timeline (since wbsItems count === timelineItems count)
        $response = $this->actingAs($pmo)->post(route('projects.timeline.finalize', $project->id));
        $response->assertRedirect(route('projects.timeline.show', $project->id));
        
        $this->assertEquals('finalized', $item1->fresh()->status);
    }

    /**
     * Test Manager is restricted to read-only access.
     */
    public function test_manager_cannot_crud_or_finalize_timeline(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $manager = User::factory()->create(['role' => 'Manager']);

        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        $scope = ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::factory()->create(['project_id' => $project->id, 'project_scope_id' => $scope->id, 'status' => 'finalized']);
        $timelineItem = TimelineItem::factory()->create([
            'project_id' => $project->id,
            'wbs_item_id' => $wbs->id,
        ]);

        // Manager can view index and show
        $this->actingAs($manager)->get(route('project-planning.timeline.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('projects.timeline.show', $project->id))->assertStatus(200);

        // Manager cannot write/edit/delete/finalize (403)
        $this->actingAs($manager)->get(route('projects.timeline.create', $project->id))->assertStatus(403);
        $this->actingAs($manager)->post(route('projects.timeline.store', $project->id), [])->assertStatus(403);
        $this->actingAs($manager)->get(route('projects.timeline.edit', [$project->id, $timelineItem->id]))->assertStatus(403);
        $this->actingAs($manager)->put(route('projects.timeline.update', [$project->id, $timelineItem->id]), [])->assertStatus(403);
        $this->actingAs($manager)->delete(route('projects.timeline.destroy', [$project->id, $timelineItem->id]))->assertStatus(403);
        $this->actingAs($manager)->post(route('projects.timeline.finalize', $project->id))->assertStatus(403);
    }

    /**
     * Test Project Manager read-only access is restricted to owned projects.
     */
    public function test_project_manager_permissions(): void
    {
        $pm1 = User::factory()->create(['role' => 'Project Manager']);
        $pm2 = User::factory()->create(['role' => 'Project Manager']);

        $ownedProject = Project::factory()->create(['owner_id' => $pm1->id, 'status' => 'planning']);
        $ownedScope = ProjectScope::factory()->create(['project_id' => $ownedProject->id, 'status' => 'finalized']);
        $ownedWbs = WbsItem::factory()->create(['project_id' => $ownedProject->id, 'project_scope_id' => $ownedScope->id, 'status' => 'finalized']);
        $ownedTimeline = TimelineItem::factory()->create(['project_id' => $ownedProject->id, 'wbs_item_id' => $ownedWbs->id]);

        $otherProject = Project::factory()->create(['owner_id' => $pm2->id, 'status' => 'planning']);
        $otherScope = ProjectScope::factory()->create(['project_id' => $otherProject->id, 'status' => 'finalized']);
        $otherWbs = WbsItem::factory()->create(['project_id' => $otherProject->id, 'project_scope_id' => $otherScope->id, 'status' => 'finalized']);
        $otherTimeline = TimelineItem::factory()->create(['project_id' => $otherProject->id, 'wbs_item_id' => $otherWbs->id]);

        // PM1 sees only owned planning project in index
        $response = $this->actingAs($pm1)->get(route('project-planning.timeline.index'));
        $response->assertStatus(200);
        $response->assertSee($ownedProject->title);
        $response->assertDontSee($otherProject->title);

        // PM1 can view owned timeline details
        $this->actingAs($pm1)->get(route('projects.timeline.show', $ownedProject->id))
            ->assertStatus(200);

        // PM1 cannot view other timeline details (403)
        $this->actingAs($pm1)->get(route('projects.timeline.show', $otherProject->id))
            ->assertStatus(403);

        // PM1 cannot write/edit/delete/finalize (403)
        $this->actingAs($pm1)->get(route('projects.timeline.create', $ownedProject->id))->assertStatus(403);
        $this->actingAs($pm1)->post(route('projects.timeline.store', $ownedProject->id), [])->assertStatus(403);
        $this->actingAs($pm1)->get(route('projects.timeline.edit', [$ownedProject->id, $ownedTimeline->id]))->assertStatus(403);
        $this->actingAs($pm1)->put(route('projects.timeline.update', [$ownedProject->id, $ownedTimeline->id]), [])->assertStatus(403);
        $this->actingAs($pm1)->delete(route('projects.timeline.destroy', [$ownedProject->id, $ownedTimeline->id]))->assertStatus(403);
        $this->actingAs($pm1)->post(route('projects.timeline.finalize', $ownedProject->id))->assertStatus(403);
    }

    /**
     * Test predecessor dependency logic date and project validation rules.
     */
    public function test_dependency_validation_rules(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        $scope = ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs1 = WbsItem::factory()->create(['project_id' => $project->id, 'project_scope_id' => $scope->id, 'status' => 'finalized']);
        $wbs2 = WbsItem::factory()->create(['project_id' => $project->id, 'project_scope_id' => $scope->id, 'status' => 'finalized']);

        // Create timeline for wbs1
        TimelineItem::factory()->create([
            'project_id' => $project->id,
            'wbs_item_id' => $wbs1->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
        ]);

        // 1. Dependency cannot be the same task itself
        $response = $this->actingAs($pmo)->post(route('projects.timeline.store', $project->id), [
            'wbs_item_id' => $wbs2->id,
            'start_date' => '2026-06-06',
            'end_date' => '2026-06-10',
            'is_milestone' => 0,
            'dependency_wbs_item_id' => $wbs2->id, // self dependency
        ]);
        $response->assertSessionHasErrors(['dependency_wbs_item_id']);

        // 2. Start date of dependent task cannot precede the predecessor end date (2026-06-05)
        $response = $this->actingAs($pmo)->post(route('projects.timeline.store', $project->id), [
            'wbs_item_id' => $wbs2->id,
            'start_date' => '2026-06-04', // earlier than 2026-06-05
            'end_date' => '2026-06-10',
            'is_milestone' => 0,
            'dependency_wbs_item_id' => $wbs1->id,
        ]);
        $response->assertSessionHasErrors(['dependency_wbs_item_id']);
    }

    /**
     * Test WBS mapping constraints: 1 WBS item can only have at most 1 Timeline item.
     */
    public function test_unique_wbs_item_mapping(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        $scope = ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::factory()->create(['project_id' => $project->id, 'project_scope_id' => $scope->id, 'status' => 'finalized']);

        // Create timeline for Wbs
        TimelineItem::factory()->create([
            'project_id' => $project->id,
            'wbs_item_id' => $wbs->id,
        ]);

        // Attempting to create another timeline item for same Wbs should fail
        $response = $this->actingAs($pmo)->post(route('projects.timeline.store', $project->id), [
            'wbs_item_id' => $wbs->id,
            'start_date' => '2026-06-06',
            'end_date' => '2026-06-10',
            'is_milestone' => 0,
        ]);
        $response->assertSessionHasErrors(['wbs_item_id']);
    }

    /**
     * Test cannot finalize timeline if there are still unscheduled WBS items.
     */
    public function test_cannot_finalize_timeline_with_unscheduled_wbs_items(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        $scope = ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        
        $wbs1 = WbsItem::factory()->create(['project_id' => $project->id, 'project_scope_id' => $scope->id, 'status' => 'finalized']);
        $wbs2 = WbsItem::factory()->create(['project_id' => $project->id, 'project_scope_id' => $scope->id, 'status' => 'finalized']);

        // Schedule only wbs1
        TimelineItem::factory()->create([
            'project_id' => $project->id,
            'wbs_item_id' => $wbs1->id,
            'status' => 'draft',
        ]);

        // Attempt to finalize while wbs2 is unscheduled -> should fail
        $response = $this->actingAs($pmo)->post(route('projects.timeline.finalize', $project->id));
        $response->assertRedirect(route('projects.timeline.show', $project->id));
        $response->assertSessionHas('error');
    }

    /**
     * Test advanced predecessor validation rules (parent task, descendants, circular).
     */
    public function test_dependency_advanced_validation_rules(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        $scope = ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        
        // Setup hierarchical WBS items
        // parent -> child1
        // sibling
        $parentWbs = WbsItem::factory()->create(['project_id' => $project->id, 'project_scope_id' => $scope->id, 'status' => 'finalized']);
        $childWbs = WbsItem::factory()->create(['project_id' => $project->id, 'project_scope_id' => $scope->id, 'parent_id' => $parentWbs->id, 'status' => 'finalized']);
        $siblingWbs = WbsItem::factory()->create(['project_id' => $project->id, 'project_scope_id' => $scope->id, 'status' => 'finalized']);

        // Schedule them in timeline first so they can be predecessors
        TimelineItem::factory()->create([
            'project_id' => $project->id,
            'wbs_item_id' => $parentWbs->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
        ]);
        
        TimelineItem::factory()->create([
            'project_id' => $project->id,
            'wbs_item_id' => $siblingWbs->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
        ]);

        // 1. A child task cannot choose parent task as predecessor because parent task is a summary task (has children)
        $response = $this->actingAs($pmo)->post(route('projects.timeline.store', $project->id), [
            'wbs_item_id' => $childWbs->id,
            'start_date' => '2026-06-06',
            'end_date' => '2026-06-10',
            'is_milestone' => 0,
            'dependency_wbs_item_id' => $parentWbs->id, // parent task
        ]);
        $response->assertSessionHasErrors(['dependency_wbs_item_id']);

        // 2. A task cannot choose its descendant as predecessor
        // Attempting to edit parent task timeline to depend on child task
        $parentTimeline = TimelineItem::where('wbs_item_id', $parentWbs->id)->first();
        // Schedule child task first so it can be a predecessor option
        TimelineItem::factory()->create([
            'project_id' => $project->id,
            'wbs_item_id' => $childWbs->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
        ]);

        $response = $this->actingAs($pmo)->put(route('projects.timeline.update', [$project->id, $parentTimeline->id]), [
            'wbs_item_id' => $parentWbs->id,
            'start_date' => '2026-06-06',
            'end_date' => '2026-06-10',
            'is_milestone' => 0,
            'dependency_wbs_item_id' => $childWbs->id, // child task (descendant)
        ]);
        $response->assertSessionHasErrors(['dependency_wbs_item_id']);

        // 3. Circular dependency check
        // Setup two sibling leaf tasks: siblingWbs and anotherLeafWbs
        $anotherLeafWbs = WbsItem::factory()->create(['project_id' => $project->id, 'project_scope_id' => $scope->id, 'status' => 'finalized']);
        
        // Schedule anotherLeafWbs to depend on siblingWbs: anotherLeafWbs -> siblingWbs
        $anotherTimeline = TimelineItem::factory()->create([
            'project_id' => $project->id,
            'wbs_item_id' => $anotherLeafWbs->id,
            'start_date' => '2026-06-06',
            'end_date' => '2026-06-10',
            'dependency_wbs_item_id' => $siblingWbs->id,
        ]);

        // Attempting to set siblingWbs predecessor to anotherLeafWbs: siblingWbs -> anotherLeafWbs -> siblingWbs (circular!)
        $siblingTimeline = TimelineItem::where('wbs_item_id', $siblingWbs->id)->first();
        $response = $this->actingAs($pmo)->put(route('projects.timeline.update', [$project->id, $siblingTimeline->id]), [
            'wbs_item_id' => $siblingWbs->id,
            'start_date' => '2026-06-11',
            'end_date' => '2026-06-15',
            'is_milestone' => 0,
            'dependency_wbs_item_id' => $anotherLeafWbs->id, // circular dependency
        ]);
        $response->assertSessionHasErrors(['dependency_wbs_item_id']);
    }
}
