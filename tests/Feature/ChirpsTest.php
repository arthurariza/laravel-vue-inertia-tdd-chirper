<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChirpsTest extends TestCase
{
    use RefreshDatabase;

    public function test_chirps_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_chirps_page_is_displayed_to_authenticated_users(): void
    {
        $response = $this
            ->get('/profile');

        $response->assertRedirect('/login');
    }

    public function test_user_can_create_chirps(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/chirps', [
                'message' => 'Test message'
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/chirps');

        $this->assertDatabaseHas('chirps', [
            'message' => 'Test message'
        ]);
    }
}
