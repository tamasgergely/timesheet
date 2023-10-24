<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\Storage;
use function Symfony\Component\String\b;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AvatarUploadTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role_id' => Role::ADMIN]);
    }

    public function test_user_can_update_avatar_image()
    {
        Storage::fake('public');

        $avatar = UploadedFile::fake()->image('avatar.jpg')->size(100);

        $response = $this->actingAs($this->user)->post('/users/upload-profile-image', [
            'user_id' => $this->user->id,
            'avatar' => $avatar
        ]);

        $this->user = $this->user->fresh();

        $this->assertEquals($this->user->avatar, $avatar->hashName());

        Storage::disk('public')->assertExists('/images/' . $avatar->hashName());
    }
}