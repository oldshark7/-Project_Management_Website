<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectScope;
use App\Models\User;
use App\Models\WbsItem;
use App\Models\TimelineItem;
use App\Models\BudgetPlan;
use App\Models\HumanResourcePlan;
use App\Models\RiskManagementPlan;
use App\Models\RiskItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectRiskManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest redirection on Risk routes.
     */
    public function test_guests_cannot_access_risk_management(): void
    {
        $project = Project::factory()->create(['status' => 'planning']);
        $riskPlan = RiskManagementPlan::factory()->create(['project_id' => $project->id]);
        $riskItem = RiskItem::factory()->create(['risk_management_plan_id' => $riskPlan->id]);

        $this->get(route('project-planning.risk-management.index'))->assertRedirect('/login');
        $this->get(route('projects.risk-management.show', $project->id))->assertRedirect('/login');
        $this->get(route('projects.risk-management.create', $project->id))->assertRedirect('/login');
        $this->post(route('projects.risk-management.store', $project->id))->assertRedirect('/login');
        $this->get(route('projects.risk-management.edit', $project->id))->assertRedirect('/login');
        $this->put(route('projects.risk-management.update', $project->id))->assertRedirect('/login');
        $this->post(route('projects.risk-management.items.add', $project->id))->assertRedirect('/login');
        $this->put(route('projects.risk-management.items.update', [$project->id, $riskItem->id]))->assertRedirect('/login');
        $this->delete(route('projects.risk-management.items.delete', [$project->id, $riskItem->id]))->assertRedirect('/login');
        $this->post(route('projects.risk-management.generate_ai', $project->id))->assertRedirect('/login');
        $this->post(route('projects.risk-management.finalize', $project->id))->assertRedirect('/login');
    }

    /**
     * Test Project Manager is blocked from all Risk Management routes.
     */
    public function test_project_manager_is_blocked_from_risk_management(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);
        $riskPlan = RiskManagementPlan::factory()->create(['project_id' => $project->id]);
        $riskItem = RiskItem::factory()->create(['risk_management_plan_id' => $riskPlan->id]);

        $this->actingAs($pm)->get(route('project-planning.risk-management.index'))->assertStatus(403);
        $this->actingAs($pm)->get(route('projects.risk-management.show', $project->id))->assertStatus(403);
        $this->actingAs($pm)->get(route('projects.risk-management.create', $project->id))->assertStatus(403);
        $this->actingAs($pm)->post(route('projects.risk-management.store', $project->id))->assertStatus(403);
        $this->actingAs($pm)->get(route('projects.risk-management.edit', $project->id))->assertStatus(403);
        $this->actingAs($pm)->put(route('projects.risk-management.update', $project->id))->assertStatus(403);
        $this->actingAs($pm)->post(route('projects.risk-management.items.add', $project->id))->assertStatus(403);
        $this->actingAs($pm)->put(route('projects.risk-management.items.update', [$project->id, $riskItem->id]))->assertStatus(403);
        $this->actingAs($pm)->delete(route('projects.risk-management.items.delete', [$project->id, $riskItem->id]))->assertStatus(403);
        $this->actingAs($pm)->post(route('projects.risk-management.generate_ai', $project->id))->assertStatus(403);
        $this->actingAs($pm)->post(route('projects.risk-management.finalize', $project->id))->assertStatus(403);
    }

    /**
     * Test Manager is restricted to read-only access on Risk Management.
     */
    public function test_manager_restricted_to_view_only_risk_management(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['status' => 'planning']);
        
        // Finalize predecessors
        ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        TimelineItem::factory()->create(['project_id' => $project->id, 'wbs_item_id' => $wbs->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        HumanResourcePlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);

        $riskPlan = RiskManagementPlan::factory()->create(['project_id' => $project->id]);
        $riskItem = RiskItem::factory()->create(['risk_management_plan_id' => $riskPlan->id]);

        // Manager can see index and show detail
        $this->actingAs($manager)->get(route('project-planning.risk-management.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('projects.risk-management.show', $project->id))->assertStatus(200);

        // Manager blocked from write operations (403)
        $this->actingAs($manager)->get(route('projects.risk-management.create', $project->id))->assertStatus(403);
        $this->actingAs($manager)->post(route('projects.risk-management.store', $project->id))->assertStatus(403);
        $this->actingAs($manager)->get(route('projects.risk-management.edit', $project->id))->assertStatus(403);
        $this->actingAs($manager)->put(route('projects.risk-management.update', $project->id))->assertStatus(403);
        $this->actingAs($manager)->post(route('projects.risk-management.items.add', $project->id))->assertStatus(403);
        $this->actingAs($manager)->put(route('projects.risk-management.items.update', [$project->id, $riskItem->id]))->assertStatus(403);
        $this->actingAs($manager)->delete(route('projects.risk-management.items.delete', [$project->id, $riskItem->id]))->assertStatus(403);
        $this->actingAs($manager)->post(route('projects.risk-management.generate_ai', $project->id))->assertStatus(403);
        $this->actingAs($manager)->post(route('projects.risk-management.finalize', $project->id))->assertStatus(403);
    }

    /**
     * Test PMO has full CRUD access to Risk Plan and its items.
     */
    public function test_pmo_can_crud_and_finalize_risk_management(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $project = Project::factory()->create(['status' => 'planning']);
        
        // Finalize predecessors
        ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        TimelineItem::factory()->create(['project_id' => $project->id, 'wbs_item_id' => $wbs->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        HumanResourcePlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);

        // 1. Initialize Risk Plan
        $this->actingAs($pmo)->get(route('projects.risk-management.create', $project->id))->assertStatus(200);
        $response = $this->actingAs($pmo)->post(route('projects.risk-management.store', $project->id), [
            'notes' => 'Catatan Manajemen Risiko',
        ]);
        $response->assertRedirect(route('projects.risk-management.edit', $project->id));
        $this->assertDatabaseHas('risk_management_plans', [
            'project_id' => $project->id,
            'notes' => 'Catatan Manajemen Risiko',
            'status' => 'draft',
        ]);

        $riskPlan = RiskManagementPlan::where('project_id', $project->id)->first();

        // 2. Add Risk Item
        $response = $this->actingAs($pmo)->post(route('projects.risk-management.items.add', $project->id), [
            'risk_title' => 'Server Crash',
            'risk_description' => 'Server dev crash sewaktu demo.',
            'risk_cause' => 'Beban uji berlebih.',
            'impact' => 'Demo tertunda.',
            'probability' => 'medium',
            'severity' => 'high',
            'mitigation_plan' => 'Lakukan load test pra-demo.',
            'contingency_plan' => 'Backup server cadangan.',
            'risk_owner' => 'DevOps',
            'related_wbs_item_id' => $wbs->id,
            'status' => 'open',
            'notes' => 'Penting',
        ]);
        $response->assertRedirect(route('projects.risk-management.edit', $project->id));

        $this->assertDatabaseHas('risk_items', [
            'risk_management_plan_id' => $riskPlan->id,
            'risk_title' => 'Server Crash',
            'probability' => 'medium',
            'severity' => 'high',
            'related_wbs_item_id' => $wbs->id,
        ]);

        $item = RiskItem::where('risk_management_plan_id', $riskPlan->id)->first();

        // 3. Update Risk Item
        $response = $this->actingAs($pmo)->put(route('projects.risk-management.items.update', [$project->id, $item->id]), [
            'risk_title' => 'Server Crash Update',
            'risk_description' => 'Server dev crash sewaktu demo updated.',
            'impact' => 'Demo tertunda.',
            'probability' => 'high',
            'severity' => 'high',
            'mitigation_plan' => 'Lakukan load test pra-demo.',
            'status' => 'mitigated',
        ]);
        $response->assertRedirect(route('projects.risk-management.edit', $project->id));
        $this->assertDatabaseHas('risk_items', [
            'id' => $item->id,
            'risk_title' => 'Server Crash Update',
            'probability' => 'high',
            'status' => 'mitigated',
        ]);

        // 4. Update general notes
        $response = $this->actingAs($pmo)->put(route('projects.risk-management.update', $project->id), [
            'notes' => 'Catatan Risiko Terkini',
        ]);
        $this->assertEquals('Catatan Risiko Terkini', $riskPlan->fresh()->notes);

        // 5. Finalize Risk Plan
        $response = $this->actingAs($pmo)->post(route('projects.risk-management.finalize', $project->id));
        $response->assertRedirect(route('projects.risk-management.show', $project->id));
        $this->assertEquals('finalized', $riskPlan->fresh()->status);
    }

    /**
     * Test PMO cannot finalize empty Risk Plan.
     */
    public function test_pmo_cannot_finalize_empty_risk_management(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $project = Project::factory()->create(['status' => 'planning']);
        
        ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        TimelineItem::factory()->create(['project_id' => $project->id, 'wbs_item_id' => $wbs->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        HumanResourcePlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);

        $riskPlan = RiskManagementPlan::factory()->create(['project_id' => $project->id, 'status' => 'draft']);

        // Finalize while items count is 0 should redirect back to edit page with error
        $response = $this->actingAs($pmo)->post(route('projects.risk-management.finalize', $project->id));
        $response->assertRedirect(route('projects.risk-management.edit', $project->id));
        $response->assertSessionHas('error');
        $this->assertEquals('draft', $riskPlan->fresh()->status);
    }

    /**
     * Test finalized Risk Plan locks all write actions.
     */
    public function test_finalized_risk_management_locks_write_actions(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $project = Project::factory()->create(['status' => 'planning']);
        
        ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        TimelineItem::factory()->create(['project_id' => $project->id, 'wbs_item_id' => $wbs->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        HumanResourcePlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);

        $riskPlan = RiskManagementPlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $item = RiskItem::factory()->create(['risk_management_plan_id' => $riskPlan->id]);

        // Attempting write actions should throw 403
        $this->actingAs($pmo)->get(route('projects.risk-management.edit', $project->id))->assertStatus(403);
        $this->actingAs($pmo)->put(route('projects.risk-management.update', $project->id), ['notes' => 'Hack'])->assertStatus(403);
        
        $this->actingAs($pmo)->post(route('projects.risk-management.items.add', $project->id), [
            'risk_title' => 'Hack Risk',
            'risk_description' => 'Desc',
            'impact' => 'High',
            'probability' => 'high',
            'severity' => 'high',
            'mitigation_plan' => 'Mitigate',
        ])->assertStatus(403);

        $this->actingAs($pmo)->put(route('projects.risk-management.items.update', [$project->id, $item->id]), [
            'risk_title' => 'Update Risk',
            'risk_description' => 'Desc',
            'impact' => 'High',
            'probability' => 'high',
            'severity' => 'high',
            'mitigation_plan' => 'Mitigate',
        ])->assertStatus(403);

        $this->actingAs($pmo)->delete(route('projects.risk-management.items.delete', [$project->id, $item->id]))->assertStatus(403);
        $this->actingAs($pmo)->post(route('projects.risk-management.generate_ai', $project->id))->assertStatus(403);
    }

    /**
     * Test prerequisite guards check on Risk routes (HR Plan must be finalized).
     */
    public function test_risk_management_blocked_if_hr_not_finalized(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $project = Project::factory()->create(['status' => 'planning']);
        
        // Scope, WBS, Timeline, and Budget finalized, but HR is NOT finalized (draft)
        ProjectScope::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        $wbs = WbsItem::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        TimelineItem::factory()->create(['project_id' => $project->id, 'wbs_item_id' => $wbs->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project->id, 'status' => 'finalized']);
        HumanResourcePlan::factory()->create(['project_id' => $project->id, 'status' => 'draft']);

        // Accessing Risk routes should abort with 403
        $this->actingAs($pmo)->get(route('projects.risk-management.create', $project->id))->assertStatus(403);
        $this->actingAs($pmo)->post(route('projects.risk-management.store', $project->id), [])->assertStatus(403);
    }

    /**
     * Test validation that linked WBS item must belong to the same project.
     */
    public function test_linked_wbs_item_must_belong_to_same_project(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $project1 = Project::factory()->create(['status' => 'planning']);
        $project2 = Project::factory()->create(['status' => 'planning']);

        // Finalize project1 predecessors
        ProjectScope::factory()->create(['project_id' => $project1->id, 'status' => 'finalized']);
        $wbs1 = WbsItem::factory()->create(['project_id' => $project1->id, 'status' => 'finalized']);
        TimelineItem::factory()->create(['project_id' => $project1->id, 'wbs_item_id' => $wbs1->id, 'status' => 'finalized']);
        BudgetPlan::factory()->create(['project_id' => $project1->id, 'status' => 'finalized']);
        HumanResourcePlan::factory()->create(['project_id' => $project1->id, 'status' => 'finalized']);

        $riskPlan = RiskManagementPlan::factory()->create(['project_id' => $project1->id, 'status' => 'draft']);

        // WBS item from project2
        $wbs2 = WbsItem::factory()->create(['project_id' => $project2->id]);

        // Attempting to add item linked to WBS item of another project should fail validation
        $response = $this->actingAs($pmo)->post(route('projects.risk-management.items.add', $project1->id), [
            'risk_title' => 'Server Crash',
            'risk_description' => 'Server dev crash.',
            'impact' => 'Demo tertunda.',
            'probability' => 'medium',
            'severity' => 'high',
            'mitigation_plan' => 'Uji beban.',
            'related_wbs_item_id' => $wbs2->id, // invalid
        ]);
        $response->assertSessionHasErrors(['related_wbs_item_id']);
    }
}
