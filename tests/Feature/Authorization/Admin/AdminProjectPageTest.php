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

use function Symfony\Component\String\b;

class AdminProjectPageTest extends TestCase
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

        $this->user->teams()->attach($this->team->id);
    }

    public function test_admin_can_access_projects_page()
    {   
        $response = $this->actingAs($this->user)->get('/projects');

        $response->assertStatus(200);
    }

    public function test_admin_see_users_project()
    {
        Project::factory()
                ->for(Client::factory()->create(['user_id' => User::factory(['role_id' => Role::USER])]))
                ->count(5)
                ->create();

        $response = $this->actingAs($this->user)->get('/projects');

        $response->assertInertia(function (Assert $page) {
            $page->component('Projects/Index')
                 ->has('projects.data', 5);
        });
    }

    public function test_admin_see_teamleaders_project()
    {
        Project::factory()
                ->for(Client::factory()->create(['user_id' => User::factory(['role_id' => Role::TEAM_LEADER])]))
                ->count(5)
                ->create();

        $response = $this->actingAs($this->user)->get('/projects');

        $response->assertInertia(function (Assert $page) {
            $page->component('Projects/Index')
                 ->has('projects.data', 5);
        });
    }

    public function test_admin_see_admins_project()
    {
        Project::factory()
                ->for(Client::factory()->create(['user_id' => User::factory(['role_id' => Role::ADMIN])]))
                ->count(5)
                ->create();

        $response = $this->actingAs($this->user)->get('/projects');

        $response->assertInertia(function (Assert $page) {
            $page->component('Projects/Index')
                 ->has('projects.data', 5);
        });
    }

    public function test_admin_can_access_create_project_page()
    {
        $response = $this->actingAs($this->user)->get('/projects/create');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_new_project_for_users()
    {
        $client = Client::factory()->create(['user_id' => User::factory(['role_id' => Role::USER])]);

        $response = $this->actingAs($this->user)->post('/projects', [
            'client' => $client->id,
            'description' => 'Test project description',
            'name' => 'Test project',
            'active' => 1
        ]);

        $this->assertDatabaseHas('projects', [
            'client_id' => $client->id,
            'description' => 'Test project description',
            'name' => 'Test project',
            'active' => 1
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/projects');
        $response->assertSessionHas('success');
    }

    public function test_admin_can_create_new_project_for_teamleaders()
    {
        $client = Client::factory()->create(['user_id' => User::factory(['role_id' => Role::TEAM_LEADER])]);

        $response = $this->actingAs($this->user)->post('/projects', [
            'client' => $client->id,
            'description' => 'Test project description',
            'name' => 'Test project',
            'active' => 1
        ]);

        $this->assertDatabaseHas('projects', [
            'client_id' => $client->id,
            'description' => 'Test project description',
            'name' => 'Test project',
            'active' => 1
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/projects');
        $response->assertSessionHas('success');
    }

    public function test_admin_can_create_new_project_for_admins()
    {
        $client = Client::factory()->create(['user_id' => User::factory(['role_id' => Role::ADMIN])]);

        $response = $this->actingAs($this->user)->post('/projects', [
            'client' => $client->id,
            'description' => 'Test project description',
            'name' => 'Test project',
            'active' => 1
        ]);

        $this->assertDatabaseHas('projects', [
            'client_id' => $client->id,
            'description' => 'Test project description',
            'name' => 'Test project',
            'active' => 1
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/projects');
        $response->assertSessionHas('success');
    }

    public function test_admin_can_update_users_project()
    {
        $project = Project::factory()
            ->for(Client::factory()->create(['user_id' => User::factory(['role_id' => Role::USER])]))
            ->create([
                'name' => 'Test project',
                'description' => 'Test project description',
                'active' => 1
            ]);
        
        $this->actingAs($this->user)->put('/projects/' . $project->id, [
            'client_id' => $project->client->id,
            'name' => 'Test project modified',
            'description' => 'Test project description modified',
            'active' => 1
        ]);

        $project = $project->fresh();

        $this->assertEquals('Test project modified', $project->name);
        $this->assertEquals('Test project description modified', $project->description);
        $this->assertEquals(1, $project->active);
    }

    public function test_admin_can_update_teamleaders_project()
    {
        $project = Project::factory()
            ->for(Client::factory()->create(['user_id' => User::factory(['role_id' => Role::TEAM_LEADER])]))
            ->create([
                'name' => 'Test project',
                'description' => 'Test project description',
                'active' => 1
            ]);
        
        $this->actingAs($this->user)->put('/projects/' . $project->id, [
            'client_id' => $project->client->id,
            'name' => 'Test project modified',
            'description' => 'Test project description modified',
            'active' => 1
        ]);

        $project = $project->fresh();

        $this->assertEquals('Test project modified', $project->name);
        $this->assertEquals('Test project description modified', $project->description);
        $this->assertEquals(1, $project->active);
    }

    public function test_admin_can_update_admins_project()
    {
        $project = Project::factory()
            ->for(Client::factory()->create(['user_id' => User::factory(['role_id' => Role::ADMIN])]))
            ->create([
                'name' => 'Test project',
                'description' => 'Test project description',
                'active' => 1
            ]);
        
        $this->actingAs($this->user)->put('/projects/' . $project->id, [
            'client_id' => $project->client->id,
            'name' => 'Test project modified',
            'description' => 'Test project description modified',
            'active' => 1
        ]);

        $project = $project->fresh();

        $this->assertEquals('Test project modified', $project->name);
        $this->assertEquals('Test project description modified', $project->description);
        $this->assertEquals(1, $project->active);
    }

    public function test_admin_can_delete_users_project()
    {
        $project = Project::factory()
            ->for(Client::factory(['user_id' => User::factory(['role_id' => Role::USER])]))
            ->has(Timer::factory()->count(1))
            ->create();

        $timers = $project->timers;

        $response = $this->actingAs($this->user)->delete('/projects/' . $project->id,[],[
                'http_referer' => '/projects'
            ]
        );

        $project = $project->fresh();

        $this->assertTrue($project->trashed());

        foreach ($timers as $timer) {
            $timer = $timer->fresh();
            $this->assertTrue($timer->trashed());
        };

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/projects');
    }

    public function test_admin_can_delete_teamleaders_project()
    {
        $project = Project::factory()
            ->for(Client::factory(['user_id' => User::factory(['role_id' => Role::TEAM_LEADER])]))
            ->has(Timer::factory()->count(1))
            ->create();

        $timers = $project->timers;

        $response = $this->actingAs($this->user)->delete('/projects/' . $project->id,[],[
                'http_referer' => '/projects'
            ]
        );

        $project = $project->fresh();

        $this->assertTrue($project->trashed());

        foreach ($timers as $timer) {
            $timer = $timer->fresh();
            $this->assertTrue($timer->trashed());
        };

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/projects');
    }

    public function test_admin_can_delete_admins_project()
    {
        $project = Project::factory()
            ->for(Client::factory(['user_id' => User::factory(['role_id' => Role::ADMIN])]))
            ->has(Timer::factory()->count(1))
            ->create();

        $timers = $project->timers;

        $response = $this->actingAs($this->user)->delete('/projects/' . $project->id,[],[
                'http_referer' => '/projects'
            ]
        );

        $project = $project->fresh();

        $this->assertTrue($project->trashed());

        foreach ($timers as $timer) {
            $timer = $timer->fresh();
            $this->assertTrue($timer->trashed());
        };

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/projects');
    }
}