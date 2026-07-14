<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectCharter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProjectCharterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests are redirected to login.
     */
    public function test_guests_cannot_access_charters(): void
    {
        $project = Project::factory()->create();
        
        $response = $this->get(route('projects.charter.show', $project->id));
        $response->assertRedirect('/login');
    }

    /**
     * Test unmapped roles (e.g. IT) cannot access charters.
     */
    public function test_it_role_cannot_access_charters(): void
    {
        $user = User::factory()->create(['role' => 'IT']);
        $project = Project::factory()->create();

        $response = $this->actingAs($user)->get(route('projects.charter.show', $project->id));
        $response->assertStatus(403);
    }

    /**
     * Test PM access controls and ownership isolation.
     */
    public function test_pm_charter_ownership_isolation(): void
    {
        $pm1 = User::factory()->create(['role' => 'Project Manager']);
        $pm2 = User::factory()->create(['role' => 'Project Manager']);

        $project1 = Project::factory()->create(['owner_id' => $pm1->id, 'status' => 'approved']);
        $project2 = Project::factory()->create(['owner_id' => $pm2->id, 'status' => 'approved']);

        // PM1 can view own project's charter show page
        $response = $this->actingAs($pm1)->get(route('projects.charter.show', $project1->id));
        $response->assertStatus(200);

        // PM1 cannot view PM2's project's charter show page
        $response = $this->actingAs($pm1)->get(route('projects.charter.show', $project2->id));
        $response->assertStatus(403);

        // PM1 CANNOT view own project's charter create form (only Manager can create)
        $response = $this->actingAs($pm1)->get(route('projects.charter.create', $project1->id));
        $response->assertStatus(403);

        // PM1 CANNOT access PM2's charter create form
        $response = $this->actingAs($pm1)->get(route('projects.charter.create', $project2->id));
        $response->assertStatus(403);
    }

    /**
     * Test PMO access controls: can only view if project is in planning status.
     */
    public function test_pmo_charter_view_restrictions(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $draftProject = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'draft']);
        $planningProject = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);

        // PMO cannot view draft project charter
        $response = $this->actingAs($pmo)->get(route('projects.charter.show', $draftProject->id));
        $response->assertStatus(403);

        // PMO can view planning project charter
        $response = $this->actingAs($pmo)->get(route('projects.charter.show', $planningProject->id));
        $response->assertStatus(200);
    }

    /**
     * Test Manager can view all charters.
     */
    public function test_manager_can_view_all_charters(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $project1 = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'approved']);
        $project2 = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);

        $response = $this->actingAs($manager)->get(route('projects.charter.show', $project1->id));
        $response->assertStatus(200);

        $response = $this->actingAs($manager)->get(route('projects.charter.show', $project2->id));
        $response->assertStatus(200);
    }

    /**
     * Test charter creation and input validation.
     */
    public function test_charter_creation_and_validation(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'approved']);

        // PM cannot create charter
        $response = $this->actingAs($pm)->post(route('projects.charter.store', $project->id), [
            'project_purpose' => 'Tujuan Proyek A',
            'action' => 'save',
        ]);
        $response->assertStatus(403);

        // Manager can create charter
        $response = $this->actingAs($manager)->post(route('projects.charter.store', $project->id), [
            'project_purpose' => 'Tujuan Proyek A',
            'business_case' => 'Bisnis Kasus A',
            'budget_summary' => 50000000.00,
            'action' => 'save',
        ]);
        $response->assertRedirect(route('projects.charter.show', $project->id));

        $this->assertDatabaseHas('project_charters', [
            'project_id' => $project->id,
            'project_purpose' => 'Tujuan Proyek A',
            'business_case' => 'Bisnis Kasus A',
            'budget_summary' => 50000000.00,
            'status' => 'draft',
        ]);

        // Creating charter when it already exists should be rejected
        $response = $this->actingAs($manager)->post(route('projects.charter.store', $project->id), [
            'project_purpose' => 'Duplikat',
            'action' => 'save',
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test validation constraints on budget.
     */
    public function test_charter_validation_errors(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['status' => 'approved']);

        // Negative budget is invalid
        $response = $this->actingAs($manager)->post(route('projects.charter.store', $project->id), [
            'budget_summary' => -500,
            'action' => 'save',
        ]);
        $response->assertSessionHasErrors(['budget_summary']);

        // String budget is invalid
        $response = $this->actingAs($manager)->post(route('projects.charter.store', $project->id), [
            'budget_summary' => 'mahal sekali',
            'action' => 'save',
        ]);
        $response->assertSessionHasErrors(['budget_summary']);
    }

    /**
     * Test status submissions and editing state constraints.
     */
    public function test_charter_editing_state_constraints(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'approved']);

        // Create a charter as draft
        $charter = ProjectCharter::factory()->create([
            'project_id' => $project->id,
            'status' => 'draft',
        ]);

        // PM cannot edit draft charter
        $response = $this->actingAs($pm)->get(route('projects.charter.edit', $project->id));
        $response->assertStatus(403);

        // Manager can edit draft charter
        $response = $this->actingAs($manager)->get(route('projects.charter.edit', $project->id));
        $response->assertStatus(200);

        // Manager submits/finalizes charter -> status changes to submitted
        $response = $this->actingAs($manager)->put(route('projects.charter.update', $project->id), [
            'project_purpose' => 'Tujuan terupdate',
            'action' => 'submit',
        ]);
        $response->assertRedirect(route('projects.charter.show', $project->id));
        $this->assertEquals('submitted', $charter->fresh()->status);

        // Manager cannot edit submitted charter
        $response = $this->actingAs($manager)->get(route('projects.charter.edit', $project->id));
        $response->assertStatus(403);

        $response = $this->actingAs($manager)->put(route('projects.charter.update', $project->id), [
            'project_purpose' => 'Illegal edit',
            'action' => 'save',
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test charter cannot be edited if the project status is not approved.
     */
    public function test_project_status_blocks_charter_edit(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        
        // Planning project status
        $project1 = Project::factory()->create(['status' => 'planning']);
        $charter1 = ProjectCharter::factory()->create([
            'project_id' => $project1->id,
            'status' => 'draft',
        ]);

        // Manager cannot edit charter because project status is planning
        $response = $this->actingAs($manager)->get(route('projects.charter.edit', $project1->id));
        $response->assertStatus(403);

        $response = $this->actingAs($manager)->put(route('projects.charter.update', $project1->id), [
            'project_purpose' => 'Illegal edit',
            'action' => 'save',
        ]);
        $response->assertStatus(403);

        // Project request status is submitted, not approved yet
        $project2 = Project::factory()->create(['status' => 'submitted']);
        $response = $this->actingAs($manager)->get(route('projects.charter.create', $project2->id));
        $response->assertStatus(403);
    }

    /**
     * Test AI suggestion generation success via mock.
     */
    public function test_ai_suggestion_generation_success(): void
    {
        // Set fake env config
        config(['services.openrouter.api_key' => 'fake-key']);

        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['status' => 'approved']);
        $charter = ProjectCharter::factory()->create([
            'project_id' => $project->id,
            'status' => 'draft',
        ]);

        // Mock OpenRouter response
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Rekomendasi AI Mocking Success'
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($manager)->post(route('projects.charter.generate_ai', $project->id));
        
        $response->assertRedirect();
        $this->assertEquals('Rekomendasi AI Mocking Success', $charter->fresh()->ai_suggestions);
    }

    /**
     * Test AI suggestion generation handles API errors gracefully without crashing.
     */
    public function test_ai_suggestion_generation_failure(): void
    {
        config(['services.openrouter.api_key' => 'fake-key']);

        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['status' => 'approved']);
        $charter = ProjectCharter::factory()->create([
            'project_id' => $project->id,
            'status' => 'draft',
        ]);

        // Mock failure response
        Http::fake([
            'openrouter.ai/*' => Http::response('Quota exceeded', 429)
        ]);

        $response = $this->actingAs($manager)->post(route('projects.charter.generate_ai', $project->id));
        
        // Assert redirect instead of crashing
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Gagal mendapatkan rekomendasi AI. Silakan coba lagi atau periksa konfigurasi API.');
        $this->assertNull($charter->fresh()->ai_suggestions);
    }

    /**
     * Test AI suggestion generation fails early if the API key is not configured.
     */
    public function test_ai_suggestion_generation_missing_api_key(): void
    {
        // Unset api key
        config(['services.openrouter.api_key' => '']);

        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['status' => 'approved']);
        $charter = ProjectCharter::factory()->create([
            'project_id' => $project->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($manager)->post(route('projects.charter.generate_ai', $project->id));
        
        $response->assertRedirect();
        $response->assertSessionHas('error', 'API Key OpenRouter belum dikonfigurasi. Silakan periksa file .env Anda.');
        $this->assertNull($charter->fresh()->ai_suggestions);
    }

    /**
     * Test AI suggestion restrictions: PM and PMO cannot generate AI.
     */
    public function test_ai_suggestion_role_restrictions(): void
    {
        config(['services.openrouter.api_key' => 'fake-key']);

        $pm = User::factory()->create(['role' => 'Project Manager']);
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);

        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'approved']);
        $charter = ProjectCharter::factory()->create([
            'project_id' => $project->id,
            'status' => 'draft',
        ]);

        // PM attempt
        $response = $this->actingAs($pm)->post(route('projects.charter.generate_ai', $project->id));
        $response->assertStatus(403);

        // PMO attempt
        $response = $this->actingAs($pmo)->post(route('projects.charter.generate_ai', $project->id));
        $response->assertStatus(403);
    }

    /**
     * Test guest cannot download charter PDF.
     */
    public function test_guests_cannot_download_charter_pdf(): void
    {
        $project = Project::factory()->create();
        
        $response = $this->get(route('projects.charter.download', $project->id));
        $response->assertRedirect('/login');
    }

    /**
     * Test PM charter PDF download ownership isolation.
     */
    public function test_pm_download_charter_pdf_ownership_isolation(): void
    {
        $pm1 = User::factory()->create(['role' => 'Project Manager']);
        $pm2 = User::factory()->create(['role' => 'Project Manager']);

        $project1 = Project::factory()->create(['owner_id' => $pm1->id, 'status' => 'approved']);
        $project2 = Project::factory()->create(['owner_id' => $pm2->id, 'status' => 'approved']);

        ProjectCharter::factory()->create(['project_id' => $project1->id]);
        ProjectCharter::factory()->create(['project_id' => $project2->id]);

        // PM1 can download own project's charter PDF
        $response = $this->actingAs($pm1)->get(route('projects.charter.download', $project1->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        // PM1 cannot download PM2's project's charter PDF
        $response = $this->actingAs($pm1)->get(route('projects.charter.download', $project2->id));
        $response->assertStatus(403);
    }

    /**
     * Test PMO charter PDF download restrictions.
     */
    public function test_pmo_download_charter_pdf_restrictions(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $draftProject = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'draft']);
        $planningProject = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);

        ProjectCharter::factory()->create(['project_id' => $draftProject->id]);
        ProjectCharter::factory()->create(['project_id' => $planningProject->id]);

        // PMO cannot download draft project charter PDF
        $response = $this->actingAs($pmo)->get(route('projects.charter.download', $draftProject->id));
        $response->assertStatus(403);

        // PMO can download planning project charter PDF
        $response = $this->actingAs($pmo)->get(route('projects.charter.download', $planningProject->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test Manager can download any charter PDF.
     */
    public function test_manager_can_download_all_charter_pdfs(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $project1 = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'approved']);
        $project2 = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);

        ProjectCharter::factory()->create(['project_id' => $project1->id]);
        ProjectCharter::factory()->create(['project_id' => $project2->id]);

        $response = $this->actingAs($manager)->get(route('projects.charter.download', $project1->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        $response = $this->actingAs($manager)->get(route('projects.charter.download', $project2->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test download charter PDF fails if charter does not exist.
     */
    public function test_download_charter_pdf_fails_if_not_found(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['status' => 'approved']);

        // Charter does not exist
        $response = $this->actingAs($manager)->get(route('projects.charter.download', $project->id));
        $response->assertRedirect(route('projects.show', $project->id));
        $response->assertSessionHas('error', 'Project Charter belum dibuat.');
    }
}
