<?php

namespace Tests\Feature;

use App\Models\Client;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Symfony\Component\String\b;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_clients_page_redirect_if_user_not_logged_in()
    {
        $response = $this->get('/clients');

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }
    
    public function test_clients_page_can_be_rendered_to_logged_in_user()
    {
        $response = $this->actingAs($this->user)->get('/clients');

        $response->assertStatus(200);
    }

    public function test_new_client_validation_works()
    {
        $response = $this->actingAs($this->user)->post('/clients', [
            'name' => ''
        ],
        ['http_referer' => '/clients']);

        $response->assertStatus(302);
        $response->assertRedirect('/clients');
        $response->assertSessionHasErrors(['name']);

    }

    public function test_new_client_can_be_saved()
    {
        $response = $this->actingAs($this->user)->post('/clients',[
            'name' => 'Test Client'
        ]);

        $this->assertDatabaseHas('clients', [
            'name' => 'Test Client'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/clients');
    }

    public function test_client_edit_page_can_rendered()
    {
        $client = Client::create(['name' => 'Test Client']);

        $response = $this->actingAs($this->user)->get('/clients/' . $client->id . '/edit');

        $response->assertStatus(200);
    }

    public function test_client_can_be_updated()
    {
        $client = Client::create(['name' => 'Test Client']);

        $response = $this->actingAs($this->user)->put('/clients/' . $client->id, [
            'name' => 'Test Client modified',
            'created_at' => '2023-01-01 08:00:00'
        ],
        ['http_referer' => '/clients/' . $client->id . '/edit']);

        $client = $client->fresh();

        $this->assertEquals('Test Client modified', $client->name);
        $this->assertEquals('2023-01-01 08:00:00', $client->created_at);
        $this->assertEquals(0, $client->active);

        $response->assertStatus(302);
        $response->assertRedirect('/clients/' . $client->id . '/edit');
    }

    public function test_client_can_be_deleted()
    {
        $client = Client::create(['name' => 'Test Client']);

        $response = $this->actingAs($this->user)->delete('/clients/' . $client->id);

        $response->assertStatus(302);
        $response->assertRedirect('/clients');
    }

    public function test_client_search_works()
    {
        Client::create(['name' => 'Test Client']);

        $response = $this->actingAs($this->user)->get('/clients?search=test');

        $response->assertSee('Test Client');


    }
}