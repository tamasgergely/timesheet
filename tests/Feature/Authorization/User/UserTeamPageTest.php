<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTeamPageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $team;
    protected $leader;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->user = User::factory()->create(['role_id' => Role::USER]);

        $this->leader = User::factory()->create(['role_id' => Role::TEAM_LEADER]);

        $this->team = Team::create([
            'name' => 'Test team',
            'leader_id' => $this->leader->id
        ]);
    }

    public function test_user_can_not_access_teams_page()
    {
        $response = $this->actingAs($this->user)->get('/teams');

        $response->assertStatus(403);
    }

    public function test_user_can_not_access_create_team_page()
    {
        $response = $this->actingAs($this->user)->get('/teams/create');

        $response->assertStatus(403);
    }

    public function test_user_can_not_create_new_team()
    {
        $response = $this->actingAs($this->user)->post('/teams', [
            'name' => 'Test team',
            'leader_id' => $this->leader->id
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_not_update_team()
    {
        $response = $this->actingAs($this->user)->put('/teams/' . $this->team->id, [
            'name' => 'Test team modified'
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_not_delete_team()
    {
        $response = $this->actingAs($this->user)->delete('/teams/' . $this->team->id);

        $response->assertStatus(403);
    }
}