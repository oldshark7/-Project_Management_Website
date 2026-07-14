<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectProposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectProposalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests are redirected to login.
     */
    public function test_guests_cannot_access_proposals(): void
    {
        $project = Project::factory()->create();
        
        $response = $this->get(route('projects.proposal.show', $project->id));
        $response->assertRedirect('/login');
    }

    /**
     * Test unmapped roles (e.g. IT) cannot access proposals.
     */
    public function test_it_role_cannot_access_proposals(): void
    {
        $user = User::factory()->create(['role' => 'IT']);
        $project = Project::factory()->create();

        $response = $this->actingAs($user)->get(route('projects.proposal.show', $project->id));
        $response->assertStatus(403);
    }

    /**
     * Test PM access controls and ownership isolation.
     */
    public function test_pm_proposal_ownership_isolation(): void
    {
        $pm1 = User::factory()->create(['role' => 'Project Manager']);
        $pm2 = User::factory()->create(['role' => 'Project Manager']);

        $project1 = Project::factory()->create(['owner_id' => $pm1->id, 'status' => 'approved']);
        $project2 = Project::factory()->create(['owner_id' => $pm2->id, 'status' => 'approved']);

        // PM1 can view own project's proposal show page
        $response = $this->actingAs($pm1)->get(route('projects.proposal.show', $project1->id));
        $response->assertStatus(200);

        // PM1 cannot view PM2's project's proposal show page
        $response = $this->actingAs($pm1)->get(route('projects.proposal.show', $project2->id));
        $response->assertStatus(403);

        // PM1 CANNOT view own project's proposal create form (only Manager can create)
        $response = $this->actingAs($pm1)->get(route('projects.proposal.create', $project1->id));
        $response->assertStatus(403);

        // PM1 CANNOT access PM2's proposal create form
        $response = $this->actingAs($pm1)->get(route('projects.proposal.create', $project2->id));
        $response->assertStatus(403);
    }

    /**
     * Test PMO access controls: can only view if project is in planning status.
     */
    public function test_pmo_proposal_view_restrictions(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $draftProject = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'draft']);
        $planningProject = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);

        // PMO cannot view draft project proposal
        $response = $this->actingAs($pmo)->get(route('projects.proposal.show', $draftProject->id));
        $response->assertStatus(403);

        // PMO can view planning project proposal
        $response = $this->actingAs($pmo)->get(route('projects.proposal.show', $planningProject->id));
        $response->assertStatus(200);
    }

    /**
     * Test Manager can view all proposals.
     */
    public function test_manager_can_view_all_proposals(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $project1 = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'approved']);
        $project2 = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);

        $response = $this->actingAs($manager)->get(route('projects.proposal.show', $project1->id));
        $response->assertStatus(200);

        $response = $this->actingAs($manager)->get(route('projects.proposal.show', $project2->id));
        $response->assertStatus(200);
    }

    /**
     * Test proposal creation and input validation.
     */
    public function test_proposal_creation_and_validation(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $manager = User::factory()->create(['role' => 'Manager']);
        
        // Proposal can only be created if project status is approved
        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'approved']);

        // PM cannot create proposal
        $response = $this->actingAs($pm)->post(route('projects.proposal.store', $project->id), [
            'background' => 'Latar belakang proyek inventaris',
            'action' => 'save',
        ]);
        $response->assertStatus(403);

        // Manager can create proposal
        $response = $this->actingAs($manager)->post(route('projects.proposal.store', $project->id), [
            'background' => 'Latar belakang proyek inventaris',
            'objectives' => 'Meningkatkan efisiensi 50%',
            'estimated_budget' => 15000000.50,
            'action' => 'save',
        ]);
        $response->assertRedirect(route('projects.proposal.show', $project->id));

        $this->assertDatabaseHas('project_proposals', [
            'project_id' => $project->id,
            'background' => 'Latar belakang proyek inventaris',
            'objectives' => 'Meningkatkan efisiensi 50%',
            'estimated_budget' => 15000000.50,
            'status' => 'draft',
        ]);

        // Creating proposal when it already exists should be rejected
        $response = $this->actingAs($manager)->post(route('projects.proposal.store', $project->id), [
            'background' => 'Duplikat',
            'action' => 'save',
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test validation constraints on budget.
     */
    public function test_proposal_validation_errors(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['status' => 'approved']);

        // Negative budget is invalid
        $response = $this->actingAs($manager)->post(route('projects.proposal.store', $project->id), [
            'estimated_budget' => -100,
            'action' => 'save',
        ]);
        $response->assertSessionHasErrors(['estimated_budget']);

        // String budget is invalid
        $response = $this->actingAs($manager)->post(route('projects.proposal.store', $project->id), [
            'estimated_budget' => 'sepuluh juta',
            'action' => 'save',
        ]);
        $response->assertSessionHasErrors(['estimated_budget']);
    }

    /**
     * Test status submissions and editing state constraints.
     */
    public function test_proposal_editing_state_constraints(): void
    {
        $pm = User::factory()->create(['role' => 'Project Manager']);
        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'approved']);

        // Create a proposal as draft
        $proposal = ProjectProposal::factory()->create([
            'project_id' => $project->id,
            'status' => 'draft',
        ]);

        // PM cannot edit draft proposal
        $response = $this->actingAs($pm)->get(route('projects.proposal.edit', $project->id));
        $response->assertStatus(403);

        // Manager can edit draft proposal
        $response = $this->actingAs($manager)->get(route('projects.proposal.edit', $project->id));
        $response->assertStatus(200);

        // Manager submits/finalizes proposal -> status changes to submitted
        $response = $this->actingAs($manager)->put(route('projects.proposal.update', $project->id), [
            'background' => 'Latar belakang baru',
            'action' => 'submit',
        ]);
        $response->assertRedirect(route('projects.proposal.show', $project->id));
        $this->assertEquals('submitted', $proposal->fresh()->status);

        // Manager cannot edit submitted proposal
        $response = $this->actingAs($manager)->get(route('projects.proposal.edit', $project->id));
        $response->assertStatus(403);

        $response = $this->actingAs($manager)->put(route('projects.proposal.update', $project->id), [
            'background' => 'Latar belakang ilegal',
            'action' => 'save',
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test proposal cannot be edited if the project status is not approved.
     */
    public function test_project_status_blocks_proposal_edit(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        
        // Planning project status (or draft/submitted/rejected)
        $project1 = Project::factory()->create(['status' => 'planning']);
        $proposal1 = ProjectProposal::factory()->create([
            'project_id' => $project1->id,
            'status' => 'draft',
        ]);

        // Manager cannot edit proposal because project status is planning (only approved allowed)
        $response = $this->actingAs($manager)->get(route('projects.proposal.edit', $project1->id));
        $response->assertStatus(403);

        $response = $this->actingAs($manager)->put(route('projects.proposal.update', $project1->id), [
            'background' => 'Latar belakang ilegal',
            'action' => 'save',
        ]);
        $response->assertStatus(403);

        // Project request status is submitted, not approved yet
        $project2 = Project::factory()->create(['status' => 'submitted']);
        $response = $this->actingAs($manager)->get(route('projects.proposal.create', $project2->id));
        $response->assertStatus(403);
    }

    /**
     * Test AI suggestion generation success via mock.
     */
    public function test_ai_suggestion_generation_success(): void
    {
        config(['services.openrouter.api_key' => 'fake-key']);

        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['status' => 'approved']);
        $proposal = ProjectProposal::factory()->create([
            'project_id' => $project->id,
            'status' => 'draft',
        ]);

        \Illuminate\Support\Facades\Http::fake([
            'openrouter.ai/*' => \Illuminate\Support\Facades\Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => '{"background": "Rekomendasi AI Mocking Success"}'
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($manager)->post(route('projects.proposal.generate_ai', $project->id));
        
        $response->assertRedirect();
        $this->assertEquals('{"background": "Rekomendasi AI Mocking Success"}', $proposal->fresh()->ai_suggestions);
    }

    /**
     * Test AI suggestion generation handles API errors gracefully.
     */
    public function test_ai_suggestion_generation_failure(): void
    {
        config(['services.openrouter.api_key' => 'fake-key']);

        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['status' => 'approved']);
        $proposal = ProjectProposal::factory()->create([
            'project_id' => $project->id,
            'status' => 'draft',
        ]);

        \Illuminate\Support\Facades\Http::fake([
            'openrouter.ai/*' => \Illuminate\Support\Facades\Http::response('Quota exceeded', 429)
        ]);

        $response = $this->actingAs($manager)->post(route('projects.proposal.generate_ai', $project->id));
        
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Gagal mendapatkan rekomendasi AI. Silakan coba lagi atau periksa konfigurasi API.');
        $this->assertNull($proposal->fresh()->ai_suggestions);
    }

    /**
     * Test AI suggestion generation fails early if API key is missing.
     */
    public function test_ai_suggestion_generation_missing_api_key(): void
    {
        config(['services.openrouter.api_key' => '']);

        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['status' => 'approved']);
        $proposal = ProjectProposal::factory()->create([
            'project_id' => $project->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($manager)->post(route('projects.proposal.generate_ai', $project->id));
        
        $response->assertRedirect();
        $response->assertSessionHas('error', 'API Key OpenRouter belum dikonfigurasi. Silakan periksa file .env Anda.');
        $this->assertNull($proposal->fresh()->ai_suggestions);
    }

    /**
     * Test AI suggestion role restrictions.
     */
    public function test_ai_suggestion_role_restrictions(): void
    {
        config(['services.openrouter.api_key' => 'fake-key']);

        $pm = User::factory()->create(['role' => 'Project Manager']);
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $project = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'approved']);
        $proposal = ProjectProposal::factory()->create([
            'project_id' => $project->id,
            'status' => 'draft',
        ]);

        // PM attempt
        $response = $this->actingAs($pm)->post(route('projects.proposal.generate_ai', $project->id));
        $response->assertStatus(403);

        // PMO attempt
        $response = $this->actingAs($pmo)->post(route('projects.proposal.generate_ai', $project->id));
        $response->assertStatus(403);
    }

    /**
     * Test guest cannot download proposal PDF.
     */
    public function test_guests_cannot_download_proposal_pdf(): void
    {
        $project = Project::factory()->create();
        
        $response = $this->get(route('projects.proposal.download', $project->id));
        $response->assertRedirect('/login');
    }

    /**
     * Test PM proposal PDF download ownership isolation.
     */
    public function test_pm_download_proposal_pdf_ownership_isolation(): void
    {
        $pm1 = User::factory()->create(['role' => 'Project Manager']);
        $pm2 = User::factory()->create(['role' => 'Project Manager']);

        $project1 = Project::factory()->create(['owner_id' => $pm1->id, 'status' => 'approved']);
        $project2 = Project::factory()->create(['owner_id' => $pm2->id, 'status' => 'approved']);

        ProjectProposal::factory()->create(['project_id' => $project1->id]);
        ProjectProposal::factory()->create(['project_id' => $project2->id]);

        // PM1 can download own project's proposal PDF
        $response = $this->actingAs($pm1)->get(route('projects.proposal.download', $project1->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        // PM1 cannot download PM2's project's proposal PDF
        $response = $this->actingAs($pm1)->get(route('projects.proposal.download', $project2->id));
        $response->assertStatus(403);
    }

    /**
     * Test PMO proposal PDF download restrictions.
     */
    public function test_pmo_download_proposal_pdf_restrictions(): void
    {
        $pmo = User::factory()->create(['role' => 'Project Management Officer']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $draftProject = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'draft']);
        $planningProject = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);

        ProjectProposal::factory()->create(['project_id' => $draftProject->id]);
        ProjectProposal::factory()->create(['project_id' => $planningProject->id]);

        // PMO cannot download draft project proposal PDF
        $response = $this->actingAs($pmo)->get(route('projects.proposal.download', $draftProject->id));
        $response->assertStatus(403);

        // PMO can download planning project proposal PDF
        $response = $this->actingAs($pmo)->get(route('projects.proposal.download', $planningProject->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test Manager can download any proposal PDF.
     */
    public function test_manager_can_download_all_proposal_pdfs(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $pm = User::factory()->create(['role' => 'Project Manager']);

        $project1 = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'approved']);
        $project2 = Project::factory()->create(['owner_id' => $pm->id, 'status' => 'planning']);

        ProjectProposal::factory()->create(['project_id' => $project1->id]);
        ProjectProposal::factory()->create(['project_id' => $project2->id]);

        $response = $this->actingAs($manager)->get(route('projects.proposal.download', $project1->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        $response = $this->actingAs($manager)->get(route('projects.proposal.download', $project2->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test download proposal PDF fails if proposal does not exist.
     */
    public function test_download_proposal_pdf_fails_if_not_found(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $project = Project::factory()->create(['status' => 'approved']);

        // Proposal does not exist
        $response = $this->actingAs($manager)->get(route('projects.proposal.download', $project->id));
        $response->assertRedirect(route('projects.show', $project->id));
        $response->assertSessionHas('error', 'Project Proposal belum dibuat.');
    }
}
