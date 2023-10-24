<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;

use Database\Seeders\RoleSeeder;
use function Symfony\Component\String\b;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTeamPageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $leader;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->user = User::factory()->create(['role_id' => Role::ADMIN]);
    }

    public function test_admin_can_access_teams_page()
    {
        $response = $this->actingAs($this->user)->get('/teams');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_create_team_page()
    {
        $response = $this->actingAs($this->user)->get('/teams/create');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_new_team()
    {
        $user = User::factory()->create(['role_id' => Role::TEAM_LEADER]);

        $response = $this->actingAs($this->user)->post('/teams', [
            'name' => 'Test team',
            'leader_id' => $user->id
        ]);

        $this->assertDatabaseHas('teams', [
            'name' => 'Test team',
            'leader_id' => $user->id
        ]);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/teams');
    }

    public function test_admin_can_edit_any_team()
    {
        $team = Team::factory()->create();

        $response = $this->actingAs($this->user)->get('/teams/' . $team->id . '/edit');

        $response->assertStatus(200);
    }

    public function test_admin_can_update_any_team()
    {
        $user = User::factory()->create(['role_id' => Role::TEAM_LEADER]);

        $team = Team::factory()->create();

        $response = $this->actingAs($this->user)->put('/teams/' . $team->id, [
            'name' => 'Test team modified',
            'leader_id' => $user->id
        ]);

        $team = $team->fresh();

        $this->assertEquals('Test team modified', $team->name);
        $this->assertEquals($user->id, $team->leader_id);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/teams');
    }

    public function test_admin_can_delete_any_team()
    {
        $team = Team::factory()->create();

        $response = $this->actingAs($this->user)->delete('/teams/' . $team->id);

        $this->assertDatabaseCount('teams', 0);
        $this->assertDatabaseMissing('teams', [
            'name' => 'Test team',
            'leader_id' => $team->leader_id
        ]);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/teams');
    }
}
