<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectScope;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectWbsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest redirection on WBS routes.
     */
    public function test_guests_cannot_access_wbs(): void
    {
        $project = Project::factory()->create(['status' => 'planning']);
        $wbsItem = WbsItem::factory()->create(['project_id' => $project->id]);

        $this->get(route('project-planning.wbs.index'))->assertRedirect('/login');
        $this->get(route('projects.wbs.show', $project->id))->assertRedirect('/login');
        $this->get(route('projects.wbs.create', $project->id))->assertRedirect('/login');
        $this->post(route('projects.wbs.store', $project->id))->assertRedirect('/login');
        $this->get(route('projects.wbs.edit', [$project->id, $wbsItem->id]))->assertRedirect('/login');
        $this->put(route('projects.wbs.update', [$project->id, $wbsItem->id]))->assertRedirect('/login');
        $this->delete(route('projects.wbs.destroy', [$project->id, $wbsItem->id]))->assertRedirect('/login');
        $this->post(route('projects.wbs.finalize', $project->id))->assertRedirect('/login');
    }

    /**
     * Test PMO has full access to CRUD and finalize WBS.
     */
    public function test_pmo_can_crud_and_finalize_wbs(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $manager = User::factory()->create(['role' => 'Manager']);

        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        $scope = ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);

        // 1. PMO can view create form
        $this->actingAs($pmo)->get(route('projects.wbs.create', $project->id))
            ->assertStatus(200);

        // 2. PMO can store WbsItem
        $response = $this->actingAs($pmo)->post(route('projects.wbs.store', $project->id), [
            'title' => 'Tugas A',
            'description' => 'Deskripsi Tugas A',
            'deliverable' => 'SRS Doc',
            'priority' => 'high',
            'estimated_duration_days' => 5,
        ]);
        $response->assertRedirect(route('projects.wbs.show', $project->id));
        $this->assertDatabaseHas('wbs_items', [
            'project_id' => $project->id,
            'title' => 'Tugas A',
            'status' => 'draft',
        ]);

        $itemA = WbsItem::where('project_id', $project->id)->first();

        // 3. PMO can store sub-task
        $response = $this->actingAs($pmo)->post(route('projects.wbs.store', $project->id), [
            'title' => 'Sub Tugas A.1',
            'description' => 'Deskripsi Sub Tugas',
            'deliverable' => 'SRS Draft',
            'priority' => 'medium',
            'estimated_duration_days' => 2,
            'parent_id' => $itemA->id,
        ]);
        $this->assertDatabaseHas('wbs_items', [
            'project_id' => $project->id,
            'title' => 'Sub Tugas A.1',
            'parent_id' => $itemA->id,
        ]);

        $subItem = WbsItem::where('title', 'Sub Tugas A.1')->first();

        // 4. PMO can edit and update
        $this->actingAs($pmo)->get(route('projects.wbs.edit', [$project->id, $subItem->id]))
            ->assertStatus(200);

        $response = $this->actingAs($pmo)->put(route('projects.wbs.update', [$project->id, $subItem->id]), [
            'title' => 'Sub Tugas A.1 Updated',
            'description' => 'Updated deskripsi',
            'priority' => 'low',
            'estimated_duration_days' => 3,
            'parent_id' => $itemA->id,
        ]);
        $response->assertRedirect(route('projects.wbs.show', $project->id));
        $this->assertDatabaseHas('wbs_items', [
            'id' => $subItem->id,
            'title' => 'Sub Tugas A.1 Updated',
            'priority' => 'low',
        ]);

        // 5. PMO can delete draft item
        $response = $this->actingAs($pmo)->delete(route('projects.wbs.destroy', [$project->id, $subItem->id]));
        $response->assertRedirect(route('projects.wbs.show', $project->id));
        $this->assertDatabaseMissing('wbs_items', ['id' => $subItem->id]);

        // 6. PMO can finalize WBS
        $response = $this->actingAs($pmo)->post(route('projects.wbs.finalize', $project->id));
        $response->assertRedirect(route('projects.wbs.show', $project->id));
        
        $this->assertEquals('finalized', $itemA->fresh()->status);
    }

    /**
     * Test Manager is restricted to read-only access.
     */
    public function test_manager_cannot_crud_or_finalize_wbs(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $manager = User::factory()->create(['role' => 'Manager']);

        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        $scope = ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbsItem = WbsItem::factory()->create(['project_id' => $project->id, 'project_scope_id' => $scope->id]);

        // Manager can view index and show
        $this->actingAs($manager)->get(route('project-planning.wbs.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('projects.wbs.show', $project->id))->assertStatus(200);

        // Manager cannot write/edit/delete/finalize (403)
        $this->actingAs($manager)->get(route('projects.wbs.create', $project->id))->assertStatus(403);
        $this->actingAs($manager)->post(route('projects.wbs.store', $project->id), [])->assertStatus(403);
        $this->actingAs($manager)->get(route('projects.wbs.edit', [$project->id, $wbsItem->id]))->assertStatus(403);
        $this->actingAs($manager)->put(route('projects.wbs.update', [$project->id, $wbsItem->id]), [])->assertStatus(403);
        $this->actingAs($manager)->delete(route('projects.wbs.destroy', [$project->id, $wbsItem->id]))->assertStatus(403);
        $this->actingAs($manager)->post(route('projects.wbs.finalize', $project->id))->assertStatus(403);
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
        $ownedWbs = WbsItem::factory()->create(['project_id' => $ownedProject->id, 'project_scope_id' => $ownedScope->id]);

        $otherProject = Project::factory()->create(['owner_id' => $pm2->id, 'status' => 'planning']);
        $otherScope = ProjectScope::factory()->create(['project_id' => $otherProject->id, 'status' => 'finalized']);
        $otherWbs = WbsItem::factory()->create(['project_id' => $otherProject->id, 'project_scope_id' => $otherScope->id]);

        // PM1 sees only owned planning project in index
        $response = $this->actingAs($pm1)->get(route('project-planning.wbs.index'));
        $response->assertStatus(200);
        $response->assertSee($ownedProject->title);
        $response->assertDontSee($otherProject->title);

        // PM1 can view owned WBS details
        $this->actingAs($pm1)->get(route('projects.wbs.show', $ownedProject->id))
            ->assertStatus(200);

        // PM1 cannot view other WBS details (403)
        $this->actingAs($pm1)->get(route('projects.wbs.show', $otherProject->id))
            ->assertStatus(403);

        // PM1 cannot write/edit/delete/finalize (403)
        $this->actingAs($pm1)->get(route('projects.wbs.create', $ownedProject->id))->assertStatus(403);
        $this->actingAs($pm1)->post(route('projects.wbs.store', $ownedProject->id), [])->assertStatus(403);
        $this->actingAs($pm1)->get(route('projects.wbs.edit', [$ownedProject->id, $ownedWbs->id]))->assertStatus(403);
        $this->actingAs($pm1)->put(route('projects.wbs.update', [$ownedProject->id, $ownedWbs->id]), [])->assertStatus(403);
        $this->actingAs($pm1)->delete(route('projects.wbs.destroy', [$ownedProject->id, $ownedWbs->id]))->assertStatus(403);
        $this->actingAs($pm1)->post(route('projects.wbs.finalize', $ownedProject->id))->assertStatus(403);
    }

    /**
     * Test parent_id validation constraints.
     */
    public function test_parent_id_validation_logic(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $projectA = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        $scopeA = ProjectScope::factory()->create(['project_id' => $projectA->id, 'status' => 'finalized']);
        
        $projectB = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        $scopeB = ProjectScope::factory()->create(['project_id' => $projectB->id, 'status' => 'finalized']);

        // Item from project B
        $itemB = WbsItem::factory()->create(['project_id' => $projectB->id, 'project_scope_id' => $scopeB->id]);

        // Attempting to set parent_id of item B in project A should fail validation
        $response = $this->actingAs($pmo)->post(route('projects.wbs.store', $projectA->id), [
            'title' => 'Invalid Parent Test',
            'description' => 'Description test',
            'priority' => 'medium',
            'parent_id' => $itemB->id, // belongs to Project B
        ]);
        $response->assertSessionHasErrors(['parent_id']);
    }

    /**
     * Test cannot modify finalized WBS.
     */
    public function test_finalized_wbs_is_immutable(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        $scope = ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbsItem = WbsItem::factory()->create([
            'project_id' => $project->id,
            'project_scope_id' => $scope->id,
            'status' => 'finalized', // finalized
        ]);

        // Cannot create items
        $this->actingAs($pmo)->get(route('projects.wbs.create', $project->id))->assertStatus(403);
        $this->actingAs($pmo)->post(route('projects.wbs.store', $project->id), [
            'title' => 'New Item',
            'description' => 'Desc',
            'priority' => 'medium',
        ])->assertStatus(403);

        // Cannot edit, update or delete
        $this->actingAs($pmo)->get(route('projects.wbs.edit', [$project->id, $wbsItem->id]))->assertStatus(403);
        $this->actingAs($pmo)->put(route('projects.wbs.update', [$project->id, $wbsItem->id]), [
            'title' => 'New Title',
            'description' => 'New Desc',
            'priority' => 'medium',
        ])->assertStatus(403);
        $this->actingAs($pmo)->delete(route('projects.wbs.destroy', [$project->id, $wbsItem->id]))->assertStatus(403);
    }

    /**
     * Test empty WBS cannot be finalized.
     */
    public function test_empty_wbs_cannot_be_finalized(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);

        $response = $this->actingAs($pmo)->post(route('projects.wbs.finalize', $project->id));
        $response->assertRedirect(route('projects.wbs.show', $project->id));
        $response->assertSessionHas('error');
    }
}
