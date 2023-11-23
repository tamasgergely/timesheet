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

class TeamLeaderProjectPageTest extends TestCase
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
        ]);

        $this->user->teams()->attach($this->team->id);
    }

    public function test_team_leader_can_access_projects_page()
    {   
        $response = $this->actingAs($this->user)->get('/projects');

        $response->assertStatus(200);
    }

    public function test_team_leader_only_sees_own_clients_projects()
    {
        // Own clients projects
        Project::factory()
                ->for(Client::factory()->state(['user_id' => $this->user->id]))
                ->create([
                    'user_id' => $this->user->id,
                    'name' => 'Own project'
                ]);

        // Other project
        $user = User::factory()->create();
        Project::factory()
                ->has(Client::factory(['user_id' => $user->id])->count(1))
                ->create([
                    'user_id' => $user->id,
                    'name' => 'Others project'
                ]);

        $response = $this->actingAs($this->user)->get('/projects');

        $response->assertInertia(function (Assert $page) {
            $page->component('Projects/Index')
                 ->has('projects.data', 1)
                 ->where('projects.data.0.name', 'Own project');
        });
    }

    public function test_team_leader_can_access_create_project_page()
    {
        $response = $this->actingAs($this->user)->get('/projects/create');

        $response->assertStatus(200);
    }

    public function test_team_leader_can_create_new_project_for_own_client()
    {

        $client = Client::factory()->create(['user_id' => $this->user->id]);

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

    public function test_team_leader_can_not_create_new_project_for_another_users_client()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->post('/projects', [
            'client' => $client->id,
            'description' => 'Test project description',
            'name' => 'Test project',
            'active' => 1
        ]);
        $response->assertStatus(403);
    }

    public function test_team_leader_can_delete_own_project()
    {
        $project = Project::factory()
            ->for(Client::factory()->state(['user_id' => $this->user->id]))
            ->has(Timer::factory()->count(1))
            ->create(['user_id' => $this->user->id]);

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

    public function test_team_leader_can_not_delete_another_users_project()
    {
        $project = Project::factory()
            ->for(Client::factory())
            ->create();

        $response = $this->actingAs($this->user)->delete('/projects/' . $project->id);

        $response->assertStatus(403);
    }
}