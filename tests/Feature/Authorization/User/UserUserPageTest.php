<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;

use Database\Seeders\RoleSeeder;
use function Symfony\Component\String\b;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserUserPageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->user = User::factory()->create(['role_id' => Role::USER]);
    }

    public function test_user_can_not_access_users_page()
    {
        $response = $this->actingAs($this->user)->get('/users');

        $response->assertStatus(403);
    }

    public function test_user_can_not_access_create_user_page()
    {
        $response = $this->actingAs($this->user)->get('/users/create');

        $response->assertStatus(403);
    }

    public function test_user_can_not_create_new_user()
    {
        $response = $this->actingAs($this->user)->post('/users',[
            'name' => 'Test user',
            'email' => 'test@user.com',
            'role_id' => 1,
            'password' => 'password',
            'password_confirmation' => 'password',
            'avatar' => null
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_not_edit_another_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->user)->get('/users/' . $user->id . '/edit');

        $response->assertStatus(403);
    }

    public function test_user_can_not_update_another_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->user)->put('/users/' . $user->id,[
            'name' => 'Test user', 
            'email' => 'test@user.com'
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_not_delete_another_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->user)->delete('/users/' . $user->id);

        $response->assertStatus(403);
    }

    public function test_user_can_edit_own_profile()
    {
        $response = $this->actingAs($this->user)->get('/users/' . $this->user->id . '/edit');

        $response->assertStatus(200);

    }

    public function test_user_can_update_own_profile()
    {
        $response = $this->actingAs($this->user)->put('/users/' . $this->user->id,[
            'name' => 'Test user', 
            'email' => 'test@user.com'
        ]);

        $response->assertSessionHas('success');
        $response->assertStatus(302);
    }
}