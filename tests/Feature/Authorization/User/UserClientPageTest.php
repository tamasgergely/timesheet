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
use Illuminate\Contracts\Filesystem\Cloud;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserClientPageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $team;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->user = User::factory()->create(['role_id' => Role::USER]);

        $this->team = Team::factory()->create(['name' => 'Own team']);

        $this->user->teams()->attach($this->team->id);
    }

    public function test_user_can_access_clients_page()
    {
        $response = $this->actingAs($this->user)->get('/clients');

        $response->assertStatus(200);
    }

    public function test_user_only_sees_own_clients_and_teammate_clients()
    {
        // User's client
        Client::factory()->create([
            'name' => 'Own client',
            'user_id' => $this->user->id
        ]);

        // Temmate's client
        Client::factory()->create([
            'name' => 'Team client',
            'user_id' => User::factory()->create(),
            'team_id' => $this->team->id
        ]);

        // Other clients
        Client::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get('/clients');

        $response->assertInertia(function (Assert $page) {
            $page->component('Clients/Index')
                 ->has('clients.data', 2)
                 ->where('clients.data.0.name', 'Own client')
                 ->where('clients.data.1.name', 'Team client');
        });
    }

    public function test_user_can_access_create_client_page()
    {
        $response = $this->actingAs($this->user)->get('/clients/create');

        $response->assertStatus(200);
    }

    public function test_user_can_create_new_client_with_website()
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

    public function test_user_can_create_new_client_without_website()
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

    public function test_user_can_not_edit_another_users_client()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->get('/clients/ ' . $client->id . '/edit');

        $response->assertStatus(403);
    }

    public function test_user_can_not_edit_teammates_client()
    {
        $client = Client::factory()->create(['team_id' => $this->team->id]);

        $response = $this->actingAs($this->user)->get('/clients/ ' . $client->id . '/edit');

        $response->assertStatus(403);
    }

    public function test_user_can_edit_own_client()
    {
        $client = Client::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get('/clients/' . $client->id . '/edit');

        $response->assertStatus(200);
    }

    public function test_user_can_not_update_anothers_users_client()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->put('/clients/' . $client->id . '/edit', [
            'name' => 'Test client',
            'active' => 1
        ]);

        $response->assertStatus(405);
    }

    public function test_user_can_not_update_teammates_client()
    {
        $client = Client::factory()->create(['team_id' => $this->team->id]);

        $response = $this->actingAs($this->user)->put('/clients/' . $client->id . '/edit', [
            'name' => 'Test client',
            'active' => 1
        ]);

        $response->assertStatus(405);
    }

    public function test_user_can_update_own_client_with_website()
    {
        $client = Client::factory()
                    ->has(Website::factory()->count(1))
                    ->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->put('/clients/' . $client->id, [
            'name' => 'Test client',
            'active' => 1,
            'team_id' => '',
            'domain' => 'http://www.test.com'
        ]);

        $this->assertDatabaseHas('clients', [
            'name' => 'Test client',
            'active' => 1,
            'user_id' => $this->user->id,
            'team_id' => null
        ]);

        $this->assertDatabaseHas('websites', [
            'client_id' => $client->id,
            'domain' => 'http://www.test.com'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/clients');
        $response->assertSessionHas('success');
    }

    public function test_user_can_update_own_client_without_website()
    {
        $client = Client::factory()
                    ->has(Website::factory()->count(1))
                    ->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->put('/clients/' . $client->id, [
            'name' => 'Test client',
            'active' => 1,
            'team_id' => ''
        ]);

        $this->assertDatabaseHas('clients', [
            'name' => 'Test client',
            'active' => 1,
            'user_id' => $this->user->id,
            'team_id' => null
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/clients');
        $response->assertSessionHas('success');
    }

    public function test_user_can_not_delete_teammates_client()
    {
        // Temmate's client
        $client = Client::factory()->create(['team_id' => $this->team->id]);

        $response = $this->actingAs($this->user)->delete('/clients/' . $client->id);

        $response->assertStatus(403);
    }

    public function test_user_can_not_delete_another_users_client()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->delete('/clients/' . $client->id);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_client_if_client_not_in_team()
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

        $response->assertStatus(302);
        $response->assertRedirect('/clients');
        $response->assertSessionHas('success');
    }

    public function test_user_can_not_delete_own_client_if_client_is_in_team()
    {
        $client = Client::factory()
                        ->has(Website::factory()->count(2))
                        ->has(Project::factory()->count(2))
                        ->create([
                            'user_id' => $this->user->id,
                            'team_id' => $this->team->id
                        ]);

        $response = $this->actingAs($this->user)->delete('/clients/' . $client->id);

        $response->assertStatus(403);
    }

    public function test_user_can_create_a_website_for_own_client()
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

    public function test_user_can_create_a_website_for_teammates_client()
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

    public function test_user_can_not_create_a_website_for_not_own_client()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->post('/websites', [
            'domain' => 'http://www.test.com',
            'client_id' => $client->id
        ]);

        $response->assertStatus(403);
    }
    
    public function test_user_can_update_own_website()
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

    public function test_user_can_not_update_website_created_by_another_user()
    {
        $website = Website::factory()->create();

        $response = $this->actingAs($this->user)->put('/websites/' . $website->id, [
            'domain' => 'http://www.test.com'
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_website_if_website_not_belongs_to_team()
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
        $projects->each(function($project) use (&$timers){
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

    public function test_user_can_not_delete_own_website_if_website_belongs_to_team()
    {
        $website = Website::factory()
                        ->for(Client::factory()->state(['team_id' => $this->team->id]))
                        ->create([
                            'user_id' => $this->user->id,
                            'domain' => 'http://www.test.com'
                        ]);

        $response = $this->actingAs($this->user)->delete('/websites/' . $website->id);

        $response->assertStatus(403);
    }

    public function test_user_can_not_delete_website_created_by_another_user()
    {
        $website = Website::factory()->create();

        $response = $this->actingAs($this->user)->delete('/websites/' . $website->id);

        $response->assertStatus(403);
    }
}
