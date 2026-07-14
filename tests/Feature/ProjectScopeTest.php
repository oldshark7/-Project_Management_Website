<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectScope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectScopeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest redirection.
     */
    public function test_guests_cannot_access_project_scope(): void
    {
        $project = Project::factory()->create(['status' => 'planning']);

        $this->get(route('project-planning.scope.index'))->assertRedirect('/login');
        $this->get(route('projects.scope.show', $project->id))->assertRedirect('/login');
        $this->get(route('projects.scope.create', $project->id))->assertRedirect('/login');
        $this->post(route('projects.scope.store', $project->id))->assertRedirect('/login');
        $this->get(route('projects.scope.edit', $project->id))->assertRedirect('/login');
        $this->put(route('projects.scope.update', $project->id))->assertRedirect('/login');
        $this->post(route('projects.scope.finalize', $project->id))->assertRedirect('/login');
    }

    /**
     * Test Project Manager access rules.
     * PM can only view owned project scopes in planning status, cannot write/edit/finalize.
     */
    public function test_project_manager_permissions(): void
    {
        $pm1 = User::factory()->create(['role' => 'Project Manager']);
        $pm2 = User::factory()->create(['role' => 'Project Manager']);
        $manager = User::factory()->create(['role' => 'Manager']);

        $ownedProject = Project::factory()->create([
            'owner_id' => $pm1->id,
            'status' => 'planning',
        ]);
        $otherProject = Project::factory()->create([
            'owner_id' => $pm2->id,
            'status' => 'planning',
        ]);

        // Create scope for owned project
        $scope = ProjectScope::factory()->create([
            'project_id' => $ownedProject->id,
            'status' => 'draft',
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        // Create scope for other project
        $otherScope = ProjectScope::factory()->create([
            'project_id' => $otherProject->id,
            'status' => 'draft',
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        // PM1 sees only owned planning projects in index
        $response = $this->actingAs($pm1)->get(route('project-planning.scope.index'));
        $response->assertStatus(200);
        $response->assertSee($ownedProject->title);
        $response->assertDontSee($otherProject->title);

        // PM1 can view owned scope details
        $this->actingAs($pm1)->get(route('projects.scope.show', $ownedProject->id))
            ->assertStatus(200)
            ->assertSee($scope->objective);

        // PM1 cannot view other project's scope details (returns 403)
        $this->actingAs($pm1)->get(route('projects.scope.show', $otherProject->id))
            ->assertStatus(403);

        // PM1 cannot create, store, edit, update, or finalize (returns 403)
        $this->actingAs($pm1)->get(route('projects.scope.create', $ownedProject->id))->assertStatus(403);
        $this->actingAs($pm1)->post(route('projects.scope.store', $ownedProject->id), [])->assertStatus(403);
        $this->actingAs($pm1)->get(route('projects.scope.edit', $ownedProject->id))->assertStatus(403);
        $this->actingAs($pm1)->put(route('projects.scope.update', $ownedProject->id), [])->assertStatus(403);
        $this->actingAs($pm1)->post(route('projects.scope.finalize', $ownedProject->id), [])->assertStatus(403);
    }

    /**
     * Test PMO access rules.
     * PMO can only view all planning projects' scopes, cannot write/edit/finalize.
     */
    public function test_pmo_permissions(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $manager = User::factory()->create(['role' => 'Manager']);

        $project1 = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        $project2 = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);

        // Create scopes
        $scope1 = ProjectScope::factory()->create([
            'project_id' => $project1->id,
            'status' => 'finalized',
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        // PMO sees all planning projects
        $response = $this->actingAs($pmo)->get(route('project-planning.scope.index'));
        $response->assertStatus(200);
        $response->assertSee($project1->title);
        $response->assertSee($project2->title);

        // PMO can view scope details
        $this->actingAs($pmo)->get(route('projects.scope.show', $project1->id))
            ->assertStatus(200)
            ->assertSee($scope1->objective);

        // PMO cannot write/edit/finalize (returns 403)
        $this->actingAs($pmo)->get(route('projects.scope.create', $project1->id))->assertStatus(403);
        $this->actingAs($pmo)->post(route('projects.scope.store', $project1->id), [])->assertStatus(403);
        $this->actingAs($pmo)->get(route('projects.scope.edit', $project1->id))->assertStatus(403);
        $this->actingAs($pmo)->put(route('projects.scope.update', $project1->id), [])->assertStatus(403);
        $this->actingAs($pmo)->post(route('projects.scope.finalize', $project1->id), [])->assertStatus(403);
    }

    /**
     * Test Manager CRUD and finalization flow.
     */
    public function test_manager_crud_and_finalize_flow(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $manager = User::factory()->create(['role' => 'Manager']);

        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);

        // 1. Manager accesses create form
        $this->actingAs($manager)->get(route('projects.scope.create', $project->id))
            ->assertStatus(200);

        // 2. Manager stores scope as draft
        $scopeData = [
            'objective' => 'Achieve world peace through software',
            'scope_description' => 'Describe the system boundaries',
            'in_scope' => 'Features A, B, C',
            'out_of_scope' => 'Feature D',
            'main_requirements' => 'Requires Laravel 12',
            'deliverables' => 'Software deployment',
            'acceptance_criteria' => 'Pass all feature tests',
            'assumptions' => 'Server is fast enough',
            'constraints' => 'Budget is limited',
            'notes' => 'Some important notes',
            'action' => 'save',
        ];

        $response = $this->actingAs($manager)->post(route('projects.scope.store', $project->id), $scopeData);
        $response->assertRedirect(route('projects.scope.show', $project->id));
        
        $this->assertDatabaseHas('project_scopes', [
            'project_id' => $project->id,
            'objective' => 'Achieve world peace through software',
            'status' => 'draft',
            'created_by' => $manager->id,
        ]);

        // 3. Manager accesses edit form
        $this->actingAs($manager)->get(route('projects.scope.edit', $project->id))
            ->assertStatus(200);

        // 4. Manager updates scope as draft
        $updatedData = array_merge($scopeData, [
            'objective' => 'Updated Objective',
            'action' => 'save',
        ]);
        $response = $this->actingAs($manager)->put(route('projects.scope.update', $project->id), $updatedData);
        $response->assertRedirect(route('projects.scope.show', $project->id));
        
        $this->assertDatabaseHas('project_scopes', [
            'project_id' => $project->id,
            'objective' => 'Updated Objective',
            'status' => 'draft',
        ]);

        // 5. Manager finalizes the scope
        $response = $this->actingAs($manager)->post(route('projects.scope.finalize', $project->id));
        $response->assertRedirect(route('projects.scope.show', $project->id));
        
        $this->assertDatabaseHas('project_scopes', [
            'project_id' => $project->id,
            'status' => 'finalized',
        ]);

        // 6. Manager cannot edit, update, or re-finalize a finalized scope
        $this->actingAs($manager)->get(route('projects.scope.edit', $project->id))->assertStatus(403);
        
        $this->actingAs($manager)->put(route('projects.scope.update', $project->id), $updatedData)
            ->assertStatus(403);

        $this->actingAs($manager)->post(route('projects.scope.finalize', $project->id))
            ->assertStatus(403);
    }

    /**
     * Test validation constraints.
     */
    public function test_validation_constraints(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);

        // Missing required fields
        $response = $this->actingAs($manager)->post(route('projects.scope.store', $project->id), [
            'action' => 'save',
        ]);
        $response->assertSessionHasErrors([
            'objective',
            'scope_description',
            'in_scope',
            'out_of_scope',
            'deliverables',
            'acceptance_criteria',
        ]);
    }
}
