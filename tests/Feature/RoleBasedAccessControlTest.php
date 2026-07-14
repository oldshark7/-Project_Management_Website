<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessControlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test unauthenticated users are redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/project-initiation');
        $response->assertRedirect('/login');

        $response = $this->get('/project-planning');
        $response->assertRedirect('/login');
    }

    /**
     * Test Project Manager access rights.
     */
    public function test_project_manager_access(): void
    {
        $user = User::factory()->create([
            'role' => 'Project Manager',
        ]);

        $response = $this->actingAs($user)->get('/project-initiation');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/project-planning');
        $response->assertStatus(403);
    }

    /**
     * Test Manager access rights.
     */
    public function test_manager_access(): void
    {
        $user = User::factory()->create([
            'role' => 'Manager',
        ]);

        $response = $this->actingAs($user)->get('/project-initiation');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/project-planning');
        $response->assertStatus(200);
    }

    /**
     * Test PMO access rights.
     */
    public function test_pmo_access(): void
    {
        // Test with full role name
        $user = User::factory()->create([
            'role' => 'Project Management Officer',
        ]);

        $response = $this->actingAs($user)->get('/project-initiation');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/project-planning');
        $response->assertStatus(200);

        // Test with alias
        $userAlias = User::factory()->create([
            'role' => 'PMO',
        ]);

        $response = $this->actingAs($userAlias)->get('/project-planning');
        $response->assertStatus(200);
    }

    /**
     * Test unauthorized role access rights.
     */
    public function test_unauthorized_role_access(): void
    {
        $user = User::factory()->create([
            'role' => 'IT',
        ]);

        $response = $this->actingAs($user)->get('/project-initiation');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/project-planning');
        $response->assertStatus(403);
    }
}
