<?php

namespace Tests\Feature;

use App\Enums\ModerationStatus;
use App\Jobs\ModerateProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile/edit');

        $response->assertOk();
    }

    public function test_changed_name_is_sent_to_moderation(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'name' => 'Иван Иванов',
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile/edit')
            ->put('/profile', [
                'name' => 'Пётр Петров',
                'email' => 'test@example.com',
                'phone' => '+79991234567',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile/edit');

        $user->refresh();

        $this->assertSame('Иван Иванов', $user->name);
        $this->assertSame('Пётр Петров', $user->pending_name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('+79991234567', $user->phone);
        $this->assertSame(
            ModerationStatus::PendingModeration,
            $user->moderation_status
        );

        Queue::assertPushed(
            ModerateProfile::class,
            fn (ModerateProfile $job) => $job->userId === $user->id
        );
    }

    public function test_unchanged_name_does_not_start_moderation(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile/edit')
            ->put('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '+79991234567',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile/edit');

        $user->refresh();

        $this->assertNull($user->pending_name);
        $this->assertNotNull($user->email_verified_at);

        Queue::assertNotPushed(ModerateProfile::class);
    }
}
