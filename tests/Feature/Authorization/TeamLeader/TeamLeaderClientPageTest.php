<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;

use App\Models\Timer;
use App\Models\Client;
use App\Models\Project;
use App\Models\Website;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeamLeaderClientPageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $team;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->user = User::factory()->create(['role_id' => Role::TEAM_LEADER]);

        $this->team = Team::factory()->create([
            'name' => 'Own team',
            'leader_id' => $this->user->id
            ]
        );

        $this->user->teams()->attach($this->team->id);
    }

    public function test_team_leader_can_access_clients_page()
    {
        $response = $this->actingAs($this->user)->get('/clients');

        $response->assertStatus(200);
    }

    public function test_team_leader_only_sees_own_clients_and_own_teams_clients()
    {
        // Team leader's client
        Client::factory()->create([
            'name' => 'Own client',
            'user_id' => $this->user->id
        ]);

        // Client of the team leader's team
        $teamMate = User::factory()->create();
        $teamMate->teams()->attach($this->team->id);

        Client::factory()->create([
            'name' => 'Team client',
            'user_id' => $teamMate->id,
            'team_id' => $this->team->id
        ]);

        // Other Clients
        Client::factory()->count(2)->create();
        
        // Same team mate but different team
        Client::factory()->create([
            'name' => 'Test client',
            'user_id' => $teamMate->id,
            'team_id' => 2
        ]);

        $response = $this->actingAs($this->user)->get('/clients');

        $response->assertInertia(function (Assert $page) {
            $page->component('Clients/Index')
                 ->has('clients.data', 2)
                 ->where('clients.data.0.name', 'Own client')
                 ->where('clients.data.1.name', 'Team client');
        });
    }

    public function test_team_leader_can_access_create_client_page()
    {
        $response = $this->actingAs($this->user)->get('/clients/create');

        $response->assertStatus(200);
    }

    public function test_team_leader_can_create_new_client_with_website()
    {
        $response = $this->actingAs($this->user)->post('/clients', [
            'name' => 'Test client',
            'domain' => 'http://www.test.com',
            'active' => 1,
            'team_id' => $this->team->id
        ]);

        $this->assertDatabaseCount('clients', 1);

        $this->assertDatabaseHas('clients', [
            'user_id' => $this->user->id,
            'name' => 'Test client',
            'active' => 1,
            'team_id' => $this->team->id
        ]);

        $this->assertDatabaseHas('websites', [
            'client_id' => Client::first()->id,
            'domain' => 'http://www.test.com'
        ]);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/clients');
    }

    public function test_team_leader_can_create_new_client_without_website()
    {
        $response = $this->actingAs($this->user)->post('/clients', [
            'name' => 'Test client',
            'active' => 1,
            'team_id' => $this->team->id
        ]);

        $this->assertDatabaseCount('clients', 1);

        $this->assertDatabaseHas('clients', [
            'user_id' => $this->user->id,
            'name' => 'Test client',
            'active' => 1,
            'team_id' => $this->team->id
        ]);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/clients');
    }

    public function test_team_leader_can_not_edit_another_users_client()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->get('/clients/' . $client->id . '/edit');

        $response->assertStatus(403);
    }

    public function test_team_leader_can_edit_own_client()
    {
        $client = Client::factory(['user_id' => $this->user->id])->create();

        $response = $this->actingAs($this->user)->get('/clients/' . $client->id . '/edit');

        $response->assertStatus(200);
    }

    public function test_team_leader_can_edit_own_teams_clients()
    {
        // Client of the team leader's team
        $teamMate = User::factory()->create();
        $teamMate->teams()->attach($this->team->id);
       
        $client = Client::factory()->create([
            'name' => 'Team client',
            'user_id' => $teamMate->id,
            'team_id' => $this->team->id
        ]);

        $response = $this->actingAs($this->user)->get('/clients/' . $client->id . '/edit');

        $response->assertStatus(200);
    }

    public function test_team_leader_can_not_update_another_users_client()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->put('/clients/' . $client->id, [
            'name' => 'Test client',
            'active' => 1,
            'domain' => 'http://www.test.com'
        ]);

        $response->assertStatus(403);
    }

    public function test_team_leader_can_update_own_client_with_website()
    {
        $client = Client::factory(['user_id' => $this->user->id])
                    ->has(Website::factory()->count(1))
                    ->create();

        $response = $this->actingAs($this->user)->put('/clients/' . $client->id, [
            'name' => 'Test client',
            'active' => 1,
            'domain' => 'http://www.test.com'
        ]);

        $client = $client->fresh();

        $this->assertEquals('Test client', $client->name);
        $this->assertEquals($this->user->id, $client->user_id);
        $this->assertEquals('http://www.test.com', $client->websites()->first()->domain);
        
        $response->assertStatus(302);
        $response->assertRedirect('/clients');
        $response->assertSessionHas('success');
    }

    public function test_team_leader_can_update_own_client_without_website()
    {
        $client = Client::factory(['user_id' => $this->user->id])
                    ->has(Website::factory()->count(1))
                    ->create();

        $response = $this->actingAs($this->user)->put('/clients/' . $client->id, [
            'name' => 'Test client',
            'active' => 1
        ]);

        $client = $client->fresh();

        $this->assertEquals('Test client', $client->name);
        $this->assertEquals($this->user->id, $client->user_id);
        
        $response->assertStatus(302);
        $response->assertRedirect('/clients');
        $response->assertSessionHas('success');
    }

    public function test_team_leader_can_update_own_teams_clients_with_website()
    {
        // Client of the team leader's team
        $teamMate = User::factory()->create();
        $teamMate->teams()->attach($this->team->id);
               
        $client = Client::factory()
                    ->has(Website::factory()->count(1))
                    ->create([
                        'name' => 'Team client',
                        'user_id' => $teamMate->id,
                        'team_id' => $this->team->id
                    ]);

        $response = $this->actingAs($this->user)->put('/clients/' . $client->id, [
            'name' => 'Test client',
            'active' => 1,
            'domain' => 'http://www.test.com'
        ]);

        $client = $client->fresh();

        $this->assertEquals('Test client', $client->name);
        $this->assertEquals($teamMate->id, $client->user_id);
        $this->assertEquals('http://www.test.com', $client->websites()->first()->domain);
        
        $response->assertStatus(302);
        $response->assertRedirect('/clients');
        $response->assertSessionHas('success');

        
    }

    public function test_team_leader_can_update_own_teams_clients_without_website()
    {
        // Client of the team leader's team
        $teamMate = User::factory()->create();
        $teamMate->teams()->attach($this->team->id);
               
        $client = Client::factory()
                    ->has(Website::factory()->count(1))
                    ->create([
                        'name' => 'Team client',
                        'user_id' => $teamMate->id,
                        'team_id' => $this->team->id
                    ]);

        $response = $this->actingAs($this->user)->put('/clients/' . $client->id, [
            'name' => 'Test client',
            'active' => 1
        ]);

        $client = $client->fresh();

        $this->assertEquals('Test client', $client->name);
        $this->assertEquals($teamMate->id, $client->user_id);
        
        $response->assertStatus(302);
        $response->assertRedirect('/clients');
        $response->assertSessionHas('success');

        
    }

    public function test_team_leader_can_not_delete_another_users_client()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->delete('/clients/' . $client->id);

        $response->assertStatus(403);
    }

    public function test_team_leader_can_delete_own_client()
    {
        $client = Client::factory(['user_id' => $this->user->id])
                    ->has(Website::factory()->count(2))
                    ->has(Project::factory()->count(2))
                    ->create();

        $websites = $client->websites;
        $projects = $client->projects;

        $response = $this->actingAs($this->user)->delete('/clients/' . $client->id);

        $client = $client->fresh();
        
        $this->assertTrue($client->trashed());

        foreach ($websites as $website) {
            $website = $website->fresh();
            $this->assertTrue($website->trashed());
        };
        
        foreach ($projects as $project) {
            $project = $project->fresh();
            $this->assertTrue($project->trashed());
        };

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/clients');
    }

    public function test_team_leader_can_delete_own_teams_clients()
    {
        // Client of the team leader's team
        $teamMate = User::factory()->create();
        $teamMate->teams()->attach($this->team->id);
               
        $client = Client::factory()
                    ->has(Website::factory()->count(1))
                    ->has(Project::factory()->count(2))
                    ->create([
                        'name' => 'Team client',
                        'user_id' => $teamMate->id,
                        'team_id' => $this->team->id
                    ]);

        $websites = $client->websites;
        $projects = $client->projects;

        $response = $this->actingAs($this->user)->delete('/clients/' . $client->id);

        $client = $client->fresh();
        
        $this->assertTrue($client->trashed());

        foreach ($websites as $website) {
            $website = $website->fresh();
            $this->assertTrue($website->trashed());
        };
        
        foreach ($projects as $project) {
            $project = $project->fresh();
            $this->assertTrue($project->trashed());
        };

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/clients');

    }

    public function test_team_leader_can_create_a_website_for_own_client()
    {
        $client = Client::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->post('/websites', [
            'domain' => 'http://www.test.com',
            'client_id' => $client->id
        ]);

        $this->assertDatabaseHas('websites', [
            'domain' => 'http://www.test.com',
            'client_id' => $client->id,
            'user_id' => $this->user->id
        ]);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/clients');
    }

    public function test_team_leader_can_create_a_website_for_teammates_client()
    {
        $client = Client::factory()->create(['team_id' => $this->team->id]);

        $response = $this->actingAs($this->user)->post('/websites', [
            'domain' => 'http://www.test.com',
            'client_id' => $client->id
        ]);

        $this->assertDatabaseHas('websites', [
            'domain' => 'http://www.test.com',
            'client_id' => $client->id,
            'user_id' => $this->user->id
        ]);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/clients');
    }

    public function test_team_leader_can_not_create_a_website_for_not_own_client()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->post('/websites', [
            'domain' => 'http://www.test.com',
            'client_id' => $client->id
        ]);

        $response->assertStatus(403);
    }

    public function test_team_leader_can_update_own_website()
    {
        $website = Website::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->put('/websites/' . $website->id, [
            'domain' => 'http://www.test.com'
        ]);

        $website = $website->fresh();

        $this->assertEquals('http://www.test.com', $website->domain);
        $this->assertEquals($this->user->id, $website->user_id);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/clients');
    }

    public function test_team_leader_can_update_website_created_by_team_member()
    {
        // Client of the team leader's team
        $teamMate = User::factory()->create();
        $teamMate->teams()->attach($this->team->id);
                
        $client = Client::factory()
                    ->has(Website::factory(['user_id' => $teamMate->id])->count(1))
                    ->create([
                        'name' => 'Team client',
                        'user_id' => $teamMate->id,
                        'team_id' => $this->team->id
                    ]);
        
        $website = $client->websites()->first();
        
        $response = $this->actingAs($this->user)->put('/websites/' . $website->id, [
            'domain' => 'http://www.test.com'
        ]);

        $website = $website->fresh();

        $this->assertEquals('http://www.test.com', $website->domain);
        $this->assertEquals($teamMate->id, $website->user_id);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/clients');
    }

    public function test_team_leader_can_not_update_website_created_by_another_user()
    {
        $website = Website::factory()->create();

        $response = $this->actingAs($this->user)->put('/websites/' . $website->id, [
            'domain' => 'http://www.test.com'
        ]);

        $response->assertStatus(403);
    }

    public function test_team_leader_can_delete_own_website()
    {
        $website = Website::factory()
                    ->has(Project::factory()
                        ->count(1)
                        ->has(Timer::factory()->count(1)))

                    ->create([
                        'user_id' => $this->user->id,
                        'domain' => 'http://www.test.com'
                    ]);

        $projects = $website->projects;

        $timers = [];
        $projects->each(function ($project) use (&$timers) {
            $timers = $project->timers;
        });

        $response = $this->actingAs($this->user)->delete('/websites/' . $website->id);

        $website = $website->fresh();

        $this->assertTrue($website->trashed());

        foreach ($projects as $project) {
            $project = $project->fresh();
            $this->assertTrue($project->trashed());
        };

        foreach ($timers as $timer) {
            $timer = $timer->fresh();
            $this->assertTrue($timer->trashed());
        };

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/clients');
    }

    public function test_team_leader_can_delete_website_created_by_team_member()
    {
        // Client of the team leader's team
        $teamMate = User::factory()->create();
        $teamMate->teams()->attach($this->team->id);
        
        $client = Client::factory(['team_id' => $this->team->id])
                    ->has(Website::factory([
                        'user_id' => $teamMate->id,
                        'domain' => 'http://www.test.com'
                    ])->count(1))
                        
                        ->has(Project::factory()->count(1))
                            ->has(Timer::factory()->count(1))
                    ->create(['user_id' => $teamMate->id]);

        $website = $client->websites()->first();

        $projects = $website->projects;

        $timers = [];
        $projects->each(function ($project) use (&$timers) {
            $timers = $project->timers;
        });

        $response = $this->actingAs($this->user)->delete('/websites/' . $website->id);

        $website = $website->fresh();

        $this->assertTrue($website->trashed());

        foreach ($projects as $project) {
            $project = $project->fresh();
            $this->assertTrue($project->trashed());
        };

        foreach ($timers as $timer) {
            $timer = $timer->fresh();
            $this->assertTrue($timer->trashed());
        };

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/clients');
    }

    public function test_team_leader_can_not_delete_website_created_by_another_user()
    {
        $website = Website::factory()->create();

        $response = $this->actingAs($this->user)->delete('/websites/' . $website->id);

        $response->assertStatus(403);
    }

}
