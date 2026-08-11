<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_conversation_from_listing(): void
    {
        $sender = User::factory()->create();
        $owner = User::factory()->create();

        $category = Category::create([
            'name' => 'Тестовая категория',
            'slug' => 'test-category',
        ]);

        $listing = Listing::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Тестовое объявление',
            'description' => 'Описание объявления',
            'price' => 1000,
            'price_type' => 'fixed',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($sender)
            ->post(route('message-user', $owner), [
                'listing_id' => $listing->id,
            ]);

        $conversation = Conversation::first();

        $this->assertNotNull($conversation);
        $this->assertSame($listing->id, $conversation->listing_id);

        $response->assertRedirect(
            route('dashboard.messages.show', [
                'conversation' => $conversation->id,
            ])
        );
    }
}
