<?php

namespace Tests\Feature;

use App\Jobs\ModerateReview;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Review;
use App\Models\ReviewInvite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReviewInviteTest extends TestCase
{
    use RefreshDatabase;

    private function makeContext(): array
    {
        $owner = User::factory()->create();
        $recipient = User::factory()->create();

        $category = Category::create([
            'name' => 'Категория ' . Str::random(8),
            'slug' => 'category-' . Str::lower(Str::random(10)),
        ]);

        $listing = Listing::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Тестовое объявление',
            'description' => 'Описание тестового объявления',
            'price' => 1000,
            'price_type' => 'fixed',
            'status' => 'active',
            'is_active' => true,
        ]);

        $conversation = Conversation::getOrCreate(
            $listing->id,
            $owner->id,
            $recipient->id
        );

        return compact(
            'owner',
            'recipient',
            'category',
            'listing',
            'conversation'
        );
    }

    private function makeInvite(
        Listing $listing,
        User $owner,
        User $recipient,
        array $attributes = []
    ): ReviewInvite {
        return ReviewInvite::create(array_merge([
            'listing_id' => $listing->id,
            'owner_id' => $owner->id,
            'recipient_id' => $recipient->id,
            'token' => (string) Str::uuid(),
            'expires_at' => now()->addDays(7),
            'used_at' => null,
        ], $attributes));
    }

    public function test_owner_can_request_review_and_message_is_created(): void
    {
        $context = $this->makeContext();

        $response = $this
            ->actingAs($context['owner'])
            ->postJson(route('dashboard.review-invites.store', [
                'conversation' => $context['conversation']->id,
            ]));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Запрос на отзыв отправлен пользователю.',
            ]);

        $invite = ReviewInvite::first();

        $this->assertNotNull($invite);
        $this->assertSame($context['listing']->id, $invite->listing_id);
        $this->assertSame($context['owner']->id, $invite->owner_id);
        $this->assertSame($context['recipient']->id, $invite->recipient_id);
        $this->assertNull($invite->used_at);

        $message = Message::first();

        $this->assertNotNull($message);
        $this->assertSame(
            $context['conversation']->id,
            $message->conversation_id
        );
        $this->assertSame($context['owner']->id, $message->sender_id);
        $this->assertStringContainsString(
            'Пожалуйста, оставьте отзыв',
            $message->body
        );
        $this->assertStringContainsString(
            '/dashboard/review-invites/',
            $message->body
        );
        $this->assertStringContainsString('signature=', $message->body);

        $context['conversation']->refresh();

        $this->assertSame(
            $message->id,
            $context['conversation']->last_message_id
        );
        $this->assertNotNull($context['conversation']->last_message_at);
    }

    public function test_recipient_cannot_request_review_from_owner(): void
    {
        $context = $this->makeContext();

        $response = $this
            ->actingAs($context['recipient'])
            ->postJson(route('dashboard.review-invites.store', [
                'conversation' => $context['conversation']->id,
            ]));

        $response->assertForbidden();

        $this->assertDatabaseCount('review_invites', 0);
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_active_review_invite_cannot_be_sent_twice(): void
    {
        $context = $this->makeContext();

        $url = route('dashboard.review-invites.store', [
            'conversation' => $context['conversation']->id,
        ]);

        $this
            ->actingAs($context['owner'])
            ->postJson($url)
            ->assertOk();

        $this
            ->actingAs($context['owner'])
            ->postJson($url)
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Запрос на отзыв уже отправлен этому пользователю.',
            ]);

        $this->assertDatabaseCount('review_invites', 1);
        $this->assertDatabaseCount('messages', 1);
    }

    public function test_can_request_review_becomes_false_after_invite_is_sent(): void
    {
        $context = $this->makeContext();

        $apiUrl = route('dashboard.messages.api', [
            'conversation' => $context['conversation']->id,
        ]);

        $this
            ->actingAs($context['owner'])
            ->getJson($apiUrl)
            ->assertOk()
            ->assertJsonPath(
                'conversation.can_request_review',
                true
            );

        $this
            ->actingAs($context['owner'])
            ->postJson(route('dashboard.review-invites.store', [
                'conversation' => $context['conversation']->id,
            ]))
            ->assertOk();

        $this
            ->actingAs($context['owner'])
            ->getJson($apiUrl)
            ->assertOk()
            ->assertJsonPath(
                'conversation.can_request_review',
                false
            );
    }

    public function test_signed_link_is_available_only_to_recipient(): void
    {
        $context = $this->makeContext();

        $invite = $this->makeInvite(
            $context['listing'],
            $context['owner'],
            $context['recipient']
        );

        $url = URL::temporarySignedRoute(
            'dashboard.review-invites.show',
            now()->addHour(),
            ['reviewInvite' => $invite->token]
        );

        $this
            ->actingAs($context['recipient'])
            ->get($url)
            ->assertOk();

        $this
            ->actingAs($context['owner'])
            ->get($url)
            ->assertForbidden();

        $stranger = User::factory()->create();

        $this
            ->actingAs($stranger)
            ->get($url)
            ->assertForbidden();
    }

    public function test_unsigned_review_invite_url_is_rejected(): void
    {
        $context = $this->makeContext();

        $invite = $this->makeInvite(
            $context['listing'],
            $context['owner'],
            $context['recipient']
        );

        $url = route('dashboard.review-invites.show', [
            'reviewInvite' => $invite->token,
        ]);

        $this
            ->actingAs($context['recipient'])
            ->get($url)
            ->assertForbidden();
    }

    public function test_expired_or_used_invite_cannot_be_opened(): void
    {
        $expiredContext = $this->makeContext();

        $expiredInvite = $this->makeInvite(
            $expiredContext['listing'],
            $expiredContext['owner'],
            $expiredContext['recipient'],
            ['expires_at' => now()->subMinute()]
        );

        // Подпись ещё действительна, но само приглашение уже просрочено.
        $expiredUrl = URL::temporarySignedRoute(
            'dashboard.review-invites.show',
            now()->addHour(),
            ['reviewInvite' => $expiredInvite->token]
        );

        $this
            ->actingAs($expiredContext['recipient'])
            ->get($expiredUrl)
            ->assertStatus(410);

        $usedContext = $this->makeContext();

        $usedInvite = $this->makeInvite(
            $usedContext['listing'],
            $usedContext['owner'],
            $usedContext['recipient'],
            ['used_at' => now()]
        );

        $usedUrl = URL::temporarySignedRoute(
            'dashboard.review-invites.show',
            now()->addHour(),
            ['reviewInvite' => $usedInvite->token]
        );

        $this
            ->actingAs($usedContext['recipient'])
            ->get($usedUrl)
            ->assertStatus(410);
    }

    public function test_recipient_can_submit_review_once(): void
    {
        Bus::fake();

        $context = $this->makeContext();

        $invite = $this->makeInvite(
            $context['listing'],
            $context['owner'],
            $context['recipient']
        );

        $url = URL::temporarySignedRoute(
            'dashboard.review-invites.submit',
            now()->addHour(),
            ['reviewInvite' => $invite->token]
        );

        $response = $this
            ->actingAs($context['recipient'])
            ->post($url, [
                'rating' => 5,
                'comment' => 'Отличное взаимодействие с владельцем.',
            ]);

        $response->assertRedirect(route('dashboard.reviews'));

        $this->assertDatabaseHas('reviews', [
            'listing_id' => $context['listing']->id,
            'user_id' => $context['recipient']->id,
            'rating' => 5,
            'comment' => 'Отличное взаимодействие с владельцем.',
            'is_active' => false,
        ]);

        $invite->refresh();

        $this->assertNotNull($invite->used_at);
        $this->assertDatabaseCount('reviews', 1);

        $review = Review::first();

        $this->assertNotNull($review);

        Bus::assertDispatched(
            ModerateReview::class,
            fn (ModerateReview $job) => true
        );

        // Повторный POST той же signed-ссылкой не создаёт второй отзыв.
        $secondResponse = $this
            ->actingAs($context['recipient'])
            ->from($url)
            ->post($url, [
                'rating' => 4,
                'comment' => 'Повторная попытка.',
            ]);

        $secondResponse
            ->assertSessionHasErrors('invite');

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_existing_review_blocks_new_invite(): void
    {
        $context = $this->makeContext();

        Review::create([
            'listing_id' => $context['listing']->id,
            'user_id' => $context['recipient']->id,
            'rating' => 5,
            'comment' => 'Отзыв уже существует.',
            'is_active' => false,
            'moderation_status' => \App\Enums\ModerationStatus::PendingModeration,
        ]);

        $this
            ->actingAs($context['owner'])
            ->postJson(route('dashboard.review-invites.store', [
                'conversation' => $context['conversation']->id,
            ]))
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Этот пользователь уже оставил отзыв к объявлению.',
            ]);

        $this->assertDatabaseCount('review_invites', 0);
        $this->assertDatabaseCount('reviews', 1);
    }
}
