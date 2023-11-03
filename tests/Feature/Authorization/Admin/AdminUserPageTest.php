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
use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\Storage;
use function Symfony\Component\String\b;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminUserPageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->user = User::factory()->create(['role_id' => Role::ADMIN]);
    }

    public function test_admin_can_access_users_page()
    {
        $response = $this->actingAs($this->user)->get('/users');

        $response->assertStatus(200);
    }

    public function test_admin_can_see_any_users()
    {
        User::factory()->create(['role_id' => Role::TEAM_LEADER]);
        User::factory()->create(['role_id' => Role::USER]);

        $response = $this->actingAs($this->user)->get('/users');

        $response->assertInertia(function (Assert $page) {
            $page->component('Users/Index')
                 ->has('users.data', 3);
        });
    }

    public function test_admin_can_access_create_user_page()
    {
        $response = $this->actingAs($this->user)->get('/users/create');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_new_user()
    {
        Storage::fake('public');

        $avatar = UploadedFile::fake()->image('avatar.jpg')->size(100);

        $response = $this->actingAs($this->user)->post('/users', [
            'name' => 'Test user',
            'email' => 'test@user.com',
            'role_id' => 1,
            'password' => 'password',
            'password_confirmation' => 'password',
            'avatar' => $avatar
        ]);

        $this->assertDatabaseCount('users', 2);

        $this->assertDatabaseHas('users', [
            'name' => 'Test user',
            'email' => 'test@user.com',
            'role_id' => 1,
            'avatar' => $avatar->hashName()
        ]);

        Storage::disk('public')->assertExists('/images/' . $avatar->hashName());

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/users');
    }

    public function test_admin_can_update_user()
    {
        $user = User::factory()->create(['role_id' => Role::USER]);

        $leader = User::factory()->create(['role_id' => Role::TEAM_LEADER]);

        $team = Team::create([
            'name' => 'Test team',
            'leader_id' => $leader->id
        ]);

        $response = $this->actingAs($this->user)->put('/users/' . $user->id, [
            'name' => 'Test user',
            'email' => 'test@user.com',
            'role_id' => 3,
            'teams' => [['label' => $team->name, 'value' => $team->id]]
        ]);

        $user = $user->fresh();

        $this->assertEquals('Test user', $user->name);
        $this->assertEquals('test@user.com', $user->email);
        $this->assertEquals(3, $user->role_id);

        $this->assertDatabaseHas('team_user', [
            'user_id' => $user->id,
            'team_id' => $team->id
        ]);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/users');
    }

    public function test_admin_can_update_teamleader()
    {
        $user = User::factory()->create(['role_id' => Role::TEAM_LEADER]);

        $leader = User::factory()->create(['role_id' => Role::TEAM_LEADER]);

        $team = Team::create([
            'name' => 'Test team',
            'leader_id' => $leader->id
        ]);

        $response = $this->actingAs($this->user)->put('/users/' . $user->id, [
            'name' => 'Test user',
            'email' => 'test@user.com',
            'role_id' => 2,
            'teams' => [['label' => $team->name, 'value' => $team->id]]
        ]);

        $user = $user->fresh();

        $this->assertEquals('Test user', $user->name);
        $this->assertEquals('test@user.com', $user->email);
        $this->assertEquals(2, $user->role_id);

        $this->assertDatabaseHas('team_user', [
            'user_id' => $user->id,
            'team_id' => $team->id
        ]);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/users');
    }

    public function test_admin_can_update_admin()
    {
        $user = User::factory()->create(['role_id' => Role::ADMIN]);

        $leader = User::factory()->create(['role_id' => Role::TEAM_LEADER]);

        $team = Team::create([
            'name' => 'Test team',
            'leader_id' => $leader->id
        ]);

        $response = $this->actingAs($this->user)->put('/users/' . $user->id, [
            'name' => 'Test user',
            'email' => 'test@user.com',
            'role_id' => 1,
            'teams' => [['label' => $team->name, 'value' => $team->id]]
        ]);

        $user = $user->fresh();

        $this->assertEquals('Test user', $user->name);
        $this->assertEquals('test@user.com', $user->email);
        $this->assertEquals(1, $user->role_id);

        $this->assertDatabaseHas('team_user', [
            'user_id' => $user->id,
            'team_id' => $team->id
        ]);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/users');
    }

    public function test_admin_can_delete_user()
    {
        $user = User::factory()
                    ->has(Client::factory()->count(1))
                    ->has(Website::factory()->count(1))
                    ->has(Project::factory()
                        ->has(Timer::factory()->count(1))
                    )
                    ->create(['role_id' => Role::USER]);

        $clients = $user->clients;
        $websites = $user->websites;
        $projects = $user->projects;
        $timers = $user->timers;
        
        $response = $this->actingAs($this->user)->delete('/users/' . $user->id);

        $user = $user->fresh();

        $this->assertTrue($user->trashed());

        foreach ($clients as $client) {
            $client = $client->fresh();
            $this->assertTrue($client->trashed());
        };

        foreach ($websites as $website) {
            $website = $website->fresh();
            $this->assertTrue($website->trashed());
        };

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
        $response->assertRedirect('/users');
    }

    public function test_admin_can_delete_teamleader()
    {
        $user = User::factory()
                    ->has(Client::factory()->count(1))
                    ->has(Website::factory()->count(1))
                    ->has(Project::factory()
                        ->has(Timer::factory()->count(1))
                    )
                    ->create(['role_id' => Role::TEAM_LEADER]);

        $clients = $user->clients;
        $websites = $user->websites;
        $projects = $user->projects;
        $timers = $user->timers;
        
        $response = $this->actingAs($this->user)->delete('/users/' . $user->id);

        $user = $user->fresh();

        $this->assertTrue($user->trashed());

        foreach ($clients as $client) {
            $client = $client->fresh();
            $this->assertTrue($client->trashed());
        };

        foreach ($websites as $website) {
            $website = $website->fresh();
            $this->assertTrue($website->trashed());
        };

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
        $response->assertRedirect('/users');
    }

    public function test_admin_can_delete_admin()
    {
        $user = User::factory()
                    ->has(Client::factory()->count(1))
                    ->has(Website::factory()->count(1))
                    ->has(Project::factory()
                        ->has(Timer::factory()->count(1))
                    )
                    ->create(['role_id' => Role::ADMIN]);

        $clients = $user->clients;
        $websites = $user->websites;
        $projects = $user->projects;
        $timers = $user->timers;
        
        $response = $this->actingAs($this->user)->delete('/users/' . $user->id);

        $user = $user->fresh();

        $this->assertTrue($user->trashed());

        foreach ($clients as $client) {
            $client = $client->fresh();
            $this->assertTrue($client->trashed());
        };

        foreach ($websites as $website) {
            $website = $website->fresh();
            $this->assertTrue($website->trashed());
        };

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
        $response->assertRedirect('/users');
    }
}
