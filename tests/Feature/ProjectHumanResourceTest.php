<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectScope;
use App\Models\User;
use App\Models\WbsItem;
use App\Models\TimelineItem;
use App\Models\BudgetPlan;
use App\Models\HumanResourcePlan;
use App\Models\HumanResourceItem;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectHumanResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest redirection on HR routes.
     */
    public function test_guests_cannot_access_human_resource(): void
    {
        $project = Project::factory()->create(['status' => 'planning']);
        $hrPlan = HumanResourcePlan::factory()->create(['project_id' => $project->id]);
        $hrItem = HumanResourceItem::factory()->create(['human_resource_plan_id' => $hrPlan->id]);

        $this->get(route('project-planning.human-resource.index'))->assertRedirect('/login');
        $this->get(route('projects.human-resource.show', $project->id))->assertRedirect('/login');
        $this->get(route('projects.human-resource.create', $project->id))->assertRedirect('/login');
        $this->post(route('projects.human-resource.store', $project->id))->assertRedirect('/login');
        $this->get(route('projects.human-resource.edit', $project->id))->assertRedirect('/login');
        $this->put(route('projects.human-resource.update', $project->id))->assertRedirect('/login');
        $this->post(route('projects.human-resource.items.add', $project->id))->assertRedirect('/login');
        $this->put(route('projects.human-resource.items.update', [$project->id, $hrItem->id]))->assertRedirect('/login');
        $this->delete(route('projects.human-resource.items.delete', [$project->id, $hrItem->id]))->assertRedirect('/login');
        $this->post(route('projects.human-resource.finalize', $project->id))->assertRedirect('/login');
    }

    /**
     * Test Project Manager is blocked from all HR Planning routes.
     */
    public function test_project_manager_is_blocked_from_human_resource(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        $hrPlan = HumanResourcePlan::factory()->create(['project_id' => $project->id]);
        $hrItem = HumanResourceItem::factory()->create(['human_resource_plan_id' => $hrPlan->id]);

        $this->actingAs($pm)->get(route('project-planning.human-resource.index'))->assertStatus(403);
        $this->actingAs($pm)->get(route('projects.human-resource.show', $project->id))->assertStatus(403);
        $this->actingAs($pm)->get(route('projects.human-resource.create', $project->id))->assertStatus(403);
        $this->actingAs($pm)->post(route('projects.human-resource.store', $project->id))->assertStatus(403);
        $this->actingAs($pm)->get(route('projects.human-resource.edit', $project->id))->assertStatus(403);
        $this->actingAs($pm)->put(route('projects.human-resource.update', $project->id))->assertStatus(403);
        $this->actingAs($pm)->post(route('projects.human-resource.items.add', $project->id))->assertStatus(403);
        $this->actingAs($pm)->put(route('projects.human-resource.items.update', [$project->id, $hrItem->id]))->assertStatus(403);
        $this->actingAs($pm)->delete(route('projects.human-resource.items.delete', [$project->id, $hrItem->id]))->assertStatus(403);
        $this->actingAs($pm)->post(route('projects.human-resource.finalize', $project->id))->assertStatus(403);
    }

    /**
     * Test Manager is restricted to read-only access on HR Planning.
     */
    public function test_manager_restricted_to_view_only_human_resource(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['status' => 'planning']);
        
        $scope = ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        TimelineItem::factory()->create(['project_id' => $project->id, 'wbs_item_id' => $wbs->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);

        $hrPlan = HumanResourcePlan::factory()->create(['project_id' => $project->id]);
        $hrItem = HumanResourceItem::factory()->create(['human_resource_plan_id' => $hrPlan->id]);

        // Manager can see index and show detail
        $this->actingAs($manager)->get(route('project-planning.human-resource.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('projects.human-resource.show', $project->id))->assertStatus(200);

        // Manager blocked from write operations (403)
        $this->actingAs($manager)->get(route('projects.human-resource.create', $project->id))->assertStatus(403);
        $this->actingAs($manager)->post(route('projects.human-resource.store', $project->id))->assertStatus(403);
        $this->actingAs($manager)->get(route('projects.human-resource.edit', $project->id))->assertStatus(403);
        $this->actingAs($manager)->put(route('projects.human-resource.update', $project->id))->assertStatus(403);
        $this->actingAs($manager)->post(route('projects.human-resource.items.add', $project->id))->assertStatus(403);
        $this->actingAs($manager)->put(route('projects.human-resource.items.update', [$project->id, $hrItem->id]))->assertStatus(403);
        $this->actingAs($manager)->delete(route('projects.human-resource.items.delete', [$project->id, $hrItem->id]))->assertStatus(403);
        $this->actingAs($manager)->post(route('projects.human-resource.finalize', $project->id))->assertStatus(403);
    }

    /**
     * Test PMO has full CRUD access to HR Plan and its items.
     */
    public function test_pmo_can_crud_and_finalize_human_resource(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $project = Project::factory()->create(['status' => 'planning']);
        
        $scope = ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        TimelineItem::factory()->create(['project_id' => $project->id, 'wbs_item_id' => $wbs->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);

        // 1. Inisialisasi HR Plan
        $this->actingAs($pmo)->get(route('projects.human-resource.create', $project->id))->assertStatus(200);
        $response = $this->actingAs($pmo)->post(route('projects.human-resource.store', $project->id), [
            'notes' => 'Catatan SDM',
        ]);
        $response->assertRedirect(route('projects.human-resource.edit', $project->id));
        $this->assertDatabaseHas('human_resource_plans', [
            'project_id' => $project->id,
            'notes' => 'Catatan SDM',
            'status' => 'draft',
        ]);

        $hrPlan = HumanResourcePlan::where('project_id', $project->id)->first();

        // 2. Add HR item
        $response = $this->actingAs($pmo)->post(route('projects.human-resource.items.add', $project->id), [
            'role_name' => 'Backend Engineer',
            'required_skill' => 'Laravel, PHP',
            'job_description' => 'Membangun REST API',
            'wbs_item_id' => $wbs->id,
            'person_in_charge' => 'Budi',
            'workload_percentage' => 80,
            'estimated_work_days' => 15,
            'quantity' => 2,
            'notes' => 'Dev lead',
        ]);
        $response->assertRedirect(route('projects.human-resource.edit', $project->id));
        
        $this->assertDatabaseHas('human_resource_items', [
            'human_resource_plan_id' => $hrPlan->id,
            'role_name' => 'Backend Engineer',
            'wbs_item_id' => $wbs->id,
            'person_in_charge' => 'Budi',
            'workload_percentage' => 80,
            'estimated_work_days' => 15,
            'quantity' => 2,
        ]);

        $item = HumanResourceItem::where('human_resource_plan_id', $hrPlan->id)->first();

        // 3. Update HR item
        $response = $this->actingAs($pmo)->put(route('projects.human-resource.items.update', [$project->id, $item->id]), [
            'role_name' => 'Lead Backend Engineer',
            'required_skill' => 'Laravel, PHP, Docker',
            'job_description' => 'Membangun REST API & Devops',
            'wbs_item_id' => $wbs->id,
            'person_in_charge' => 'Budi Raharjo',
            'workload_percentage' => 100,
            'estimated_work_days' => 20,
            'quantity' => 1,
            'notes' => 'Dev lead updated',
        ]);
        $response->assertRedirect(route('projects.human-resource.edit', $project->id));
        $this->assertDatabaseHas('human_resource_items', [
            'id' => $item->id,
            'role_name' => 'Lead Backend Engineer',
            'person_in_charge' => 'Budi Raharjo',
            'workload_percentage' => 100,
            'quantity' => 1,
        ]);

        // 4. Update general notes
        $response = $this->actingAs($pmo)->put(route('projects.human-resource.update', $project->id), [
            'notes' => 'Catatan SDM Terkini',
        ]);
        $this->assertEquals('Catatan SDM Terkini', $hrPlan->fresh()->notes);

        // 5. Finalize HR Plan
        $response = $this->actingAs($pmo)->post(route('projects.human-resource.finalize', $project->id));
        $response->assertRedirect(route('projects.human-resource.show', $project->id));
        $this->assertEquals('finalized', $hrPlan->fresh()->status);
    }

    /**
     * Test PMO cannot finalize empty HR Plan (without items).
     */
    public function test_pmo_cannot_finalize_empty_human_resource(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $project = Project::factory()->create(['status' => 'planning']);
        
        ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        TimelineItem::factory()->create(['project_id' => $project->id, 'wbs_item_id' => $wbs->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);

        $hrPlan = HumanResourcePlan::factory()->create(['project_id' => $project->id, 'status' => 'draft']);

        // Finalize while items count is 0 should redirect back to edit page with error
        $response = $this->actingAs($pmo)->post(route('projects.human-resource.finalize', $project->id));
        $response->assertRedirect(route('projects.human-resource.edit', $project->id));
        $response->assertSessionHas('error');
        $this->assertEquals('draft', $hrPlan->fresh()->status);
    }

    /**
     * Test finalized HR Plan locks all write actions.
     */
    public function test_finalized_human_resource_locks_write_actions(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $project = Project::factory()->create(['status' => 'planning']);
        
        ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        TimelineItem::factory()->create(['project_id' => $project->id, 'wbs_item_id' => $wbs->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);

        $hrPlan = HumanResourcePlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $item = HumanResourceItem::factory()->create(['human_resource_plan_id' => $hrPlan->id]);

        // Attempting to edit/update/delete should throw 403
        $this->actingAs($pmo)->get(route('projects.human-resource.edit', $project->id))->assertStatus(403);
        $this->actingAs($pmo)->put(route('projects.human-resource.update', $project->id), ['notes' => 'Hacker'])->assertStatus(403);
        
        $this->actingAs($pmo)->post(route('projects.human-resource.items.add', $project->id), [
            'role_name' => 'Tester',
            'required_skill' => 'Selenium',
            'job_description' => 'E2E Testing',
            'quantity' => 1,
        ])->assertStatus(403);

        $this->actingAs($pmo)->put(route('projects.human-resource.items.update', [$project->id, $item->id]), [
            'role_name' => 'Tester',
            'required_skill' => 'Selenium',
            'job_description' => 'E2E Testing',
            'quantity' => 2,
        ])->assertStatus(403);

        $this->actingAs($pmo)->delete(route('projects.human-resource.items.delete', [$project->id, $item->id]))->assertStatus(403);
    }

    /**
     * Test predecessor guards check on HR routes (Scope, WBS, Timeline, and Budget must be finalized).
     */
    public function test_human_resource_blocked_if_budget_not_finalized(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $project = Project::factory()->create(['status' => 'planning']);
        
        // Scope, WBS, and Timeline finalized, but Budget is NOT finalized (draft)
        ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        WbsItem::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::where('project_id', $project->id)->first();
        TimelineItem::factory()->create(['project_id' => $project->id, 'wbs_item_id' => $wbs->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project->id, 'status' => 'draft']);

        // Accessing HR routes should abort with 403
        $this->actingAs($pmo)->get(route('projects.human-resource.create', $project->id))->assertStatus(403);
        $this->actingAs($pmo)->post(route('projects.human-resource.store', $project->id), [])->assertStatus(403);
    }

    /**
     * Test validation that linked WBS item must belong to the same project.
     */
    public function test_linked_wbs_item_must_belong_to_same_project(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $project1 = Project::factory()->create(['status' => 'planning']);
        $project2 = Project::factory()->create(['status' => 'planning']);

        ProjectScope::factory()->create(['project_id' => $project1->id, 'status' => 'finalized']);
        $wbs1 = WbsItem::factory()->create(['project_id' => $project1->id, 'status' => 'finalized']);
        TimelineItem::factory()->create(['project_id' => $project1->id, 'wbs_item_id' => $wbs1->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project1->id, 'status' => 'finalized']);

        $hrPlan = HumanResourcePlan::factory()->create(['project_id' => $project1->id, 'status' => 'draft']);

        // WBS item from project2
        $wbs2 = WbsItem::factory()->create(['project_id' => $project2->id]);

        // Attempting to add item linked to WBS item of another project should fail validation
        $response = $this->actingAs($pmo)->post(route('projects.human-resource.items.add', $project1->id), [
            'role_name' => 'Backend Engineer',
            'required_skill' => 'Laravel, PHP',
            'job_description' => 'Membangun REST API',
            'wbs_item_id' => $wbs2->id, // invalid
            'quantity' => 1,
        ]);
        $response->assertSessionHasErrors(['wbs_item_id']);
    }

    /**
     * Test PMO can assign team member if workload is sufficient.
     */
    public function test_pmo_can_assign_team_member_if_workload_sufficient(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $project = Project::factory()->create(['status' => 'planning']);
        ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        TimelineItem::factory()->create(['project_id' => $project->id, 'wbs_item_id' => $wbs->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $hrPlan = HumanResourcePlan::factory()->create(['project_id' => $project->id, 'status' => 'draft']);

        $member = TeamMember::create([
            'name' => 'Kresna',
            'role_name' => 'Fullstack Developer',
            'skills' => 'Laravel, React',
            'default_capacity_percentage' => 100,
            'is_active' => true,
        ]);

        $response = $this->actingAs($pmo)->post(route('projects.human-resource.items.add', $project->id), [
            'role_name' => 'Developer',
            'required_skill' => 'Laravel',
            'job_description' => 'Coding',
            'team_member_id' => $member->id,
            'workload_percentage' => 40,
            'quantity' => 1,
        ]);

        $response->assertRedirect(route('projects.human-resource.edit', $project->id));
        $this->assertDatabaseHas('human_resource_items', [
            'team_member_id' => $member->id,
            'workload_percentage' => 40,
            'person_in_charge' => 'Kresna',
        ]);

        $this->assertEquals(40, $member->fresh()->current_workload_percentage);
    }

    /**
     * Test PMO cannot assign team member if workload exceeds capacity.
     */
    public function test_pmo_cannot_assign_team_member_if_workload_exceeds_capacity(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $project = Project::factory()->create(['status' => 'planning']);
        ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        TimelineItem::factory()->create(['project_id' => $project->id, 'wbs_item_id' => $wbs->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $hrPlan = HumanResourcePlan::factory()->create(['project_id' => $project->id, 'status' => 'draft']);

        $member = TeamMember::create([
            'name' => 'Kayla',
            'role_name' => 'Fullstack Developer',
            'skills' => 'Laravel, React',
            'default_capacity_percentage' => 100,
            'is_active' => true,
        ]);

        // First assignment
        HumanResourceItem::create([
            'human_resource_plan_id' => $hrPlan->id,
            'role_name' => 'Dev',
            'required_skill' => 'PHP',
            'job_description' => 'Coding',
            'team_member_id' => $member->id,
            'workload_percentage' => 60,
            'quantity' => 1,
            'person_in_charge' => 'Kayla',
        ]);

        // Second assignment that exceeds 100% capacity (60 + 50 = 110)
        $response = $this->actingAs($pmo)->post(route('projects.human-resource.items.add', $project->id), [
            'role_name' => 'Dev 2',
            'required_skill' => 'JS',
            'job_description' => 'React coding',
            'team_member_id' => $member->id,
            'workload_percentage' => 50,
            'quantity' => 1,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(60, $member->fresh()->current_workload_percentage);
    }

    /**
     * Test edit HR item does not double count workload.
     */
    public function test_edit_hr_item_does_not_double_count_workload(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $project = Project::factory()->create(['status' => 'planning']);
        ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        TimelineItem::factory()->create(['project_id' => $project->id, 'wbs_item_id' => $wbs->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $hrPlan = HumanResourcePlan::factory()->create(['project_id' => $project->id, 'status' => 'draft']);

        $member = TeamMember::create([
            'name' => 'Fahmi',
            'role_name' => 'Fullstack Developer',
            'skills' => 'Laravel, React',
            'default_capacity_percentage' => 100,
            'is_active' => true,
        ]);

        $item = HumanResourceItem::create([
            'human_resource_plan_id' => $hrPlan->id,
            'role_name' => 'Dev',
            'required_skill' => 'PHP',
            'job_description' => 'Coding',
            'team_member_id' => $member->id,
            'workload_percentage' => 80,
            'quantity' => 1,
            'person_in_charge' => 'Fahmi',
        ]);

        // Edit the same item to increase to 90%. Excluding itself, 0 + 90 = 90 <= 100, so it should be allowed!
        $response = $this->actingAs($pmo)->put(route('projects.human-resource.items.update', [$project->id, $item->id]), [
            'role_name' => 'Dev',
            'required_skill' => 'PHP',
            'job_description' => 'Coding',
            'team_member_id' => $member->id,
            'workload_percentage' => 90,
            'quantity' => 1,
        ]);

        $response->assertRedirect(route('projects.human-resource.edit', $project->id));
        $this->assertEquals(90, $member->fresh()->current_workload_percentage);
    }
}
