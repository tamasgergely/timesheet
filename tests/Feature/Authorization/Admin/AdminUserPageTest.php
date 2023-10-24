<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Team;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Symfony\Component\String\b;
use Illuminate\Foundation\Testing\WithFaker;
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

    public function test_admin_can_edit_any_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->user)->get('/users/' . $user->id . '/edit');

        $response->assertStatus(200);
    }

    public function test_admin_can_update_any_user()
    {
        $user = User::factory()->create();

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

    public function test_admin_can_delete_any_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->user)->delete('/users/' . $user->id);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
        $response->assertRedirect('/users');
    }
}
