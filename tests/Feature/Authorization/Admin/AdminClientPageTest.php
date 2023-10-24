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
use function Symfony\Component\String\b;

use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminClientPageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $team;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->user = User::factory()->create(['role_id' => Role::ADMIN]);

        $this->team = Team::factory()->create(['name' => 'Own team']);
    }

    public function test_admin_can_access_clients_page()
    {
        $response = $this->actingAs($this->user)->get('/clients');

        $response->assertStatus(200);
    }

    public function test_admin_can_see_any_clients()
    {
        Client::factory()->count(5)->create();

        $response = $this->actingAs($this->user)->get('/clients');

        $response->assertInertia(function (Assert $page) {
            $page->component('Clients/Index')
                 ->has('clients.data', 5);
        });
    }

    public function test_admin_can_create_new_client_without_website()
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

    public function test_admin_can_create_new_client_with_website()
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

    public function test_admin_can_edit_any_client()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->get('/clients/' . $client->id . '/edit');

        $response->assertStatus(200);
    }

    public function test_admin_can_update_any_client()
    {
        $client = Client::factory()
                    ->has(Website::factory()->count(1))
                    ->create();

        $response = $this->actingAs($this->user)->put('/clients/' . $client->id, [
            'name' => 'Test client',
            'active' => 1,
            'domain' => 'http://www.test.com'
        ]);

        $client = $client->fresh();

        $this->assertEquals('Test client', $client->name);
        $this->assertEquals($client->active, 1);
        $this->assertEquals($client->websites()->first()->domain, 'http://www.test.com');

        $response->assertStatus(302);
        $response->assertRedirect('/clients');
        $response->assertSessionHas('success');
    }

    public function test_admin_can_delete_any_client()
    {
        $client = Client::factory()
                    ->has(Website::factory()->count(1))
                    ->has(Project::factory()->count(1))
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

    public function test_admin_can_create_a_website_for_any_client()
    {
        $client = Client::factory()->create();

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

    public function test_admin_can_update_any_website()
    {
        $website = Website::factory()->create();

        $response = $this->actingAs($this->user)->put('/websites/' . $website->id, [
            'domain' => 'http://www.test.com'
        ]);

        $website = $website->fresh();

        $this->assertEquals('http://www.test.com', $website->domain);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/clients');
    }

    public function test_admin_can_delete_any_website()
    {
        $website = Website::factory()
                        ->has(Project::factory()
                            ->count(1)
                            ->has(Timer::factory()->count(1)))

                        ->create();

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
}
