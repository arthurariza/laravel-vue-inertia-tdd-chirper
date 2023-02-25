<?php

namespace Tests\Feature;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ChirpsTest extends TestCase
{
    use RefreshDatabase;

    public function test_chirps_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/chirps');

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

    public function test_index_shows_all_chirps(): void
    {
        $user = $this->login();

        Chirp::factory(3)->create(['user_id' => $user->id]);

        $this
            ->get('/chirps')
            ->assertInertia(fn(AssertableInertia $page) => $page
                ->has('chirps', 3)
            );
    }

    public function test_chirp_can_be_updated(): void
    {
        $user = $this->login();

        $chirp = Chirp::factory()->create(['user_id' => $user->id]);

        $response = $this
            ->put("/chirps/{$chirp->id}", [
                'message' => 'New Message',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/chirps');

        $chirp->refresh();

        $this->assertSame('New Message', $chirp->message);
    }

    public function test_chirp_can_be_updated_by_creator(): void
    {
        $this->login();

        $chirp = Chirp::factory()->create();

        $response = $this
            ->put("/chirps/{$chirp->id}", [
                'message' => 'New Message',
            ]);


        $response
            ->assertForbidden();

        $chirp->refresh();

        $this->assertSame($chirp->message, $chirp->message);
    }
}
