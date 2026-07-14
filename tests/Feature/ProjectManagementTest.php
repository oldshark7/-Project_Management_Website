<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests are redirected to login.
     */
    public function test_guests_cannot_access_projects(): void
    {
        $response = $this->get(route('projects.index'));
        $response->assertRedirect('/login');
    }

    /**
     * Test unmapped roles (e.g., IT role) cannot access projects.
     */
    public function test_it_role_cannot_access_projects(): void
    {
        $user = User::factory()->create(['role' => 'IT']);

        $response = $this->actingAs($user)->get(route('projects.index'));
        $response->assertStatus(403);
    }

    /**
     * Test list filtering for Project Manager, Manager, and PMO.
     */
    public function test_project_list_filtering_by_role(): void
    {
        $pm1 = User::factory()->create(['role' => 'Project Manager']);
        $pm2 = User::factory()->create(['role' => 'Project Manager']);
        $manager = User::factory()->create(['role' => 'Manager']);
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);

        // Create projects
        $project1 = Project::factory()->create(['owner_id' => $pm1->id, 'status' => 'draft']);
        $project2 = Project::factory()->create(['owner_id' => $pm2->id, 'status' => 'submitted']);
        $project3 = Project::factory()->create(['owner_id' => $pm1->id, 'status' => 'planning']);

        // PM1 should only see their own projects (project1 and project3)
        $response = $this->actingAs($pm1)->get(route('projects.index'));
        $response->assertStatus(200);
        $response->assertSee($project1->title);
        $response->assertSee($project3->title);
        $response->assertDontSee($project2->title);

        // Manager should see all projects
        $response = $this->actingAs($manager)->get(route('projects.index'));
        $response->assertStatus(200);
        $response->assertSee($project1->title);
        $response->assertSee($project2->title);
        $response->assertSee($project3->title);

        // PMO should only see project3 (planning status)
        $response = $this->actingAs($pmo)->get(route('projects.index'));
        $response->assertStatus(200);
        $response->assertSee($project3->title);
        $response->assertDontSee($project1->title);
        $response->assertDontSee($project2->title);
    }

    /**
     * Test project creation permissions and validation.
     */
    public function test_project_creation_and_validation(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $manager = User::factory()->create(['role' => 'Manager']);

        // Manager cannot access create form
        $response = $this->actingAs($manager)->get(route('projects.create'));
        $response->assertStatus(403);

        // PM can access create form
        $response = $this->actingAs($pm)->get(route('projects.create'));
        $response->assertStatus(200);

        // Validation error on missing title
        $response = $this->actingAs($pm)->post(route('projects.store'), [
            'description' => 'Test',
        ]);
        $response->assertSessionHasErrors(['title']);

        // Validation error on date logic
        $response = $this->actingAs($pm)->post(route('projects.store'), [
            'title' => 'Project A',
            'start_date' => '2026-05-20',
            'end_date' => '2026-05-10', // early date
        ]);
        $response->assertSessionHasErrors(['end_date']);

        // Successful creation
        $response = $this->actingAs($pm)->post(route('projects.store'), [
            'title' => 'Project Valid',
            'description' => 'Valid description',
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-20',
        ]);
        $response->assertRedirect(route('projects.index'));

        $this->assertDatabaseHas('projects', [
            'title' => 'Project Valid',
            'owner_id' => $pm->id,
            'status' => 'draft',
        ]);
    }

    /**
     * Test PM can view and edit their own projects, but not others.
     */
    public function test_project_pm_view_and_edit_isolation(): void
    {
        $pm1 = User::factory()->create(['role' => 'Project Manager']);
        $pm2 = User::factory()->create(['role' => 'Project Manager']);

        $projectOfPm1 = Project::factory()->create(['owner_id' => $pm1->id, 'status' => 'draft']);

        // PM1 can view own
        $response = $this->actingAs($pm1)->get(route('projects.show', $projectOfPm1->id));
        $response->assertStatus(200);

        // PM2 cannot view PM1's project
        $response = $this->actingAs($pm2)->get(route('projects.show', $projectOfPm1->id));
        $response->assertStatus(403);

        // PM1 can edit own
        $response = $this->actingAs($pm1)->get(route('projects.edit', $projectOfPm1->id));
        $response->assertStatus(200);

        // PM2 cannot edit PM1's project
        $response = $this->actingAs($pm2)->get(route('projects.edit', $projectOfPm1->id));
        $response->assertStatus(403);
    }

    /**
     * Test PMO access is strictly limited to planning status.
     */
    public function test_pmo_view_restrictions(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $draftProject = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'draft']);
        $planningProject = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);

        // PMO cannot view draft project details
        $response = $this->actingAs($pmo)->get(route('projects.show', $draftProject->id));
        $response->assertStatus(403);

        // PMO can view planning project details
        $response = $this->actingAs($pmo)->get(route('projects.show', $planningProject->id));
        $response->assertStatus(200);

        // PMO cannot edit any project
        $response = $this->actingAs($pmo)->get(route('projects.edit', $planningProject->id));
        $response->assertStatus(403);
    }

    /**
     * Test strict state transitions.
     */
    public function test_strict_state_transitions(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $manager = User::factory()->create(['role' => 'Manager']);

        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'draft']);

        // PM submits draft
        $response = $this->actingAs($pm)->put(route('projects.update', $project->id), [
            'title' => 'Updated Title',
            'action' => 'submit',
        ]);
        $response->assertRedirect(route('projects.index'));
        $this->assertEquals('submitted', $project->fresh()->status);

        // Manager rejects submitted project
        $response = $this->actingAs($manager)->put(route('projects.update', $project->id), [
            'status' => 'rejected',
        ]);
        $response->assertRedirect(route('projects.index'));
        $this->assertEquals('rejected', $project->fresh()->status);

        // PM submits rejected project again
        $response = $this->actingAs($pm)->put(route('projects.update', $project->id), [
            'title' => 'Fixed Title',
            'action' => 'submit',
        ]);
        $this->assertEquals('submitted', $project->fresh()->status);

        // Manager approves submitted project
        $response = $this->actingAs($manager)->put(route('projects.update', $project->id), [
            'status' => 'approved',
        ]);
        $this->assertEquals('approved', $project->fresh()->status);

        // Manager moves approved project to planning
        // First, create finalized proposal and charter
        \App\Models\ProjectProposal::factory()->create([
            'project_id' => $project->id,
            'status' => 'submitted',
        ]);
        \App\Models\ProjectCharter::factory()->create([
            'project_id' => $project->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($manager)->put(route('projects.update', $project->id), [
            'status' => 'planning',
        ]);
        $this->assertEquals('planning', $project->fresh()->status);

        // Try invalid transition: planning -> draft (Manager)
        $response = $this->actingAs($manager)->put(route('projects.update', $project->id), [
            'status' => 'draft',
        ]);
        $response->assertSessionHasErrors(['status']);
        $this->assertEquals('planning', $project->fresh()->status);
    }

    /**
     * Test deletion logic.
     */
    public function test_project_deletion_restrictions(): void
    {
        $pm1 = User::factory()->create(['role' => 'Project Manager']);
        $pm2 = User::factory()->create(['role' => 'Project Manager']);
        $manager = User::factory()->create(['role' => 'Manager']);

        $draftProject = Project::factory()->create(['owner_id' => $pm1->id, 'status' => 'draft']);
        $submittedProject = Project::factory()->create(['owner_id' => $pm1->id, 'status' => 'submitted']);

        // Non-owner cannot delete
        $response = $this->actingAs($pm2)->delete(route('projects.destroy', $draftProject->id));
        $response->assertStatus(403);

        // Manager cannot delete
        $response = $this->actingAs($manager)->delete(route('projects.destroy', $draftProject->id));
        $response->assertStatus(403);

        // Owner cannot delete if status is not draft
        $response = $this->actingAs($pm1)->delete(route('projects.destroy', $submittedProject->id));
        $response->assertStatus(403);

        // Owner can delete if status is draft
        $response = $this->actingAs($pm1)->delete(route('projects.destroy', $draftProject->id));
        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseMissing('projects', ['id' => $draftProject->id]);
    }
}
