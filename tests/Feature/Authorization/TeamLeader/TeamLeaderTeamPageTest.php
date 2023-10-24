<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;

use App\Models\Client;
use Database\Seeders\RoleSeeder;
use function Symfony\Component\String\b;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

class TeamLeaderTeamPageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $leader;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->user = User::factory()->create(['role_id' => Role::TEAM_LEADER]);
    }

    public function test_team_leader_can_access_teams_page()
    {   
        $response = $this->actingAs($this->user)->get('/teams');

        $response->assertStatus(200);
    }

    public function test_team_leader_only_sees_own_teams()
    {
        $user = User::factory()->create([
            'role_id' => Role::TEAM_LEADER
        ]);

        Team::create([
            'name' => 'Own team',
            'leader_id' => $this->user->id
        ]);

        Team::create([
            'name' => 'Other team',
            'leader_id' => $user->id
        ]);
    
        $response = $this->actingAs($this->user)->get('/teams');

        $response->assertInertia(function (Assert $page) {
            $page->component('Teams/Index')
                 ->has('teams.data', 1)
                 ->where('teams.data.0.name', 'Own team');
        });
    }

    public function test_team_leader_can_access_create_team_page()
    {
        $response = $this->actingAs($this->user)->get('/teams/create');

        $response->assertStatus(200);
    }

    public function test_team_leader_can_create_new_team()
    {
        $response = $this->actingAs($this->user)->post('/teams',[
            'name' => 'Test team',
        ]);

        $this->assertDatabaseHas('teams', [
            'name' => 'Test team',
            'leader_id' => $this->user->id
        ]);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/teams');
    }    

    public function test_team_leader_can_not_edit_another_team()
    {
        $user = User::factory()->create([
            'role_id' => Role::TEAM_LEADER
        ]);

        $team = Team::create([
            'name' => 'Other team',
            'leader_id' => $user->id
        ]);

        $response = $this->actingAs($this->user)->get('/teams/' . $team->id . '/edit');

        $response->assertStatus(403);
    }

    public function test_team_leader_can_not_update_another_team()
    {
        $user = User::factory()->create([
            'role_id' => Role::TEAM_LEADER
        ]);

        $team = Team::create([
            'name' => 'Other team',
            'leader_id' => $user->id
        ]);

        $response = $this->actingAs($this->user)->put('/teams/' . $team->id,[
            'name' => 'Test team', 
        ]);

        $response->assertStatus(403);

    }

    public function test_team_leader_can_edit_own_team()
    {
        $team = Team::create([
            'name' => 'Own team',
            'leader_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)->get('/teams/' . $team->id . '/edit');

        $response->assertStatus(200);
    }

    public function test_team_leader_can_update_own_team()
    {
        $team = Team::create([
            'name' => 'Own team',
            'leader_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)->put('/teams/' . $team->id, [
            'name' => 'Own team modified',
        ]);

        $team = $team->fresh();

        $this->assertEquals('Own team modified', $team->name);
        $this->assertEquals($team->leader_id, $this->user->id);

        $response->assertStatus(302);
        $response->assertRedirect('/teams');

    }

    public function test_team_leader_can_not_delete_a_team_other_than_his_own()
    {
        $user = User::factory()->create([
            'role_id' => Role::TEAM_LEADER
        ]);

        $team = Team::create([
            'name' => 'Other team',
            'leader_id' => $user->id
        ]);

        $response = $this->actingAs($this->user)->delete('/teams/' . $team->id);

        $response->assertStatus(403);
    }

    public function test_team_leader_can_delete_his_own_team()
    {
        $team = Team::create([
            'name' => 'Other team',
            'leader_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)->delete('/teams/' . $team->id);

        $this->assertDatabaseCount('teams', 0);
        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/teams');

    }
}