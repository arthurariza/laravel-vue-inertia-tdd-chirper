<?php

namespace Tests\Feature;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ChirpsTest extends TestCase
{
    use RefreshDatabase;

    public function test_chirps_page_is_displayed(): void
    {
        $this->login();

        $response = $this
            ->get('/chirps');

        $response->assertOk();
    }

    public function test_chirps_page_is_displayed_to_authenticated_users(): void
    {
        $response = $this
            ->get(route('chirps.index'));

        $response->assertRedirect('/login');
    }

    public function test_user_can_create_chirps(): void
    {
        $user = $this->login();

        $response = $this
            ->post(route('chirps.index'), [
                'message' => 'Test message'
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('chirps.index'));

        $this->assertDatabaseHas('chirps', [
            'message' => 'Test message'
        ]);
    }

    public function test_index_shows_all_chirps(): void
    {
        $user = $this->login();

        Chirp::factory(3)->create(['user_id' => $user->id]);

        $this
            ->get(route('chirps.index'))
            ->assertInertia(fn(AssertableInertia $page) => $page
                ->has('chirps', 3)
            );
    }

    public function test_chirps_can_be_updated(): void
    {
        $user = $this->login();

        $chirp = Chirp::factory()->create(['user_id' => $user->id]);

        $response = $this
            ->put(route('chirps.update', $chirp), [
                'message' => 'New Message',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('chirps.index'));

        $chirp->refresh();

        $this->assertSame('New Message', $chirp->message);
    }

    public function test_chirp_can_be_updated_by_creator(): void
    {
        $this->login();

        $chirp = Chirp::factory()->create();

        $response = $this
            ->put(route('chirps.update', $chirp), [
                'message' => 'New Message',
            ]);


        $response
            ->assertForbidden();

        $chirp->refresh();

        $this->assertSame($chirp->message, $chirp->message);
    }

    public function test_chirps_can_be_deleted(): void
    {
        $user = $this->login();

        $chirp = Chirp::factory()->create(['user_id' => $user->id]);

        $response = $this->delete(route('chirps.destroy', $chirp));

        $response->assertRedirect(route('chirps.index'))->assertSessionHasNoErrors();

        $this->assertModelMissing($chirp);
    }

    public function test_required_fields_are_validated(): void
    {
        $user = $this->login();

        $chirp = Chirp::factory()->make(['user_id' => $user->id]);

        $response = $this->post(route('chirps.store'), [
            'message' => $chirp->message
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_validation_messages_are_displayed(): void
    {
        $user = $this->login();

        Chirp::factory()->make(['user_id' => $user->id]);

        $response = $this->post(route('chirps.store'), []);

        $response->assertSessionHasErrors(['message']);
    }

    public function test_users_are_notified_when_chirps_are_created(): void
    {
        Notification::fake();

        $this->login();

        User::factory(2)->create();

        $this->post(route('chirps.store'), ['message' => 'Chirp']);

        Notification::assertCount(2);
    }
}
