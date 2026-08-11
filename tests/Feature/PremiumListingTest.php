<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PremiumListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_premium_expiration_date_is_set_from_selected_days(): void
    {
        Carbon::setTestNow('2026-08-10 12:00:00');

        $listing = $this->createListing([
            'is_premium' => true,
            'premium_days' => 7,
        ]);

        $this->assertTrue($listing->is_premium);
        $this->assertSame(7, $listing->premium_days);
        $this->assertTrue(
            $listing->premium_until->equalTo(now()->addDays(7))
        );
    }

    public function test_active_premium_is_not_removed_before_expiration(): void
    {
        Carbon::setTestNow('2026-08-10 12:00:00');

        $listing = $this->createListing([
            'is_premium' => true,
            'premium_days' => 3,
        ]);

        Carbon::setTestNow('2026-08-12 12:00:00');

        $this->artisan('premium:expire')->assertSuccessful();

        $listing->refresh();

        $this->assertTrue($listing->is_premium);
        $this->assertNotNull($listing->premium_until);
    }

    public function test_expired_premium_is_removed_automatically(): void
    {
        Carbon::setTestNow('2026-08-10 12:00:00');

        $listing = $this->createListing([
            'is_premium' => true,
            'premium_days' => 1,
        ]);

        Carbon::setTestNow('2026-08-11 12:01:00');

        $this->artisan('premium:expire')->assertSuccessful();

        $listing->refresh();

        $this->assertFalse($listing->is_premium);
        $this->assertNull($listing->premium_until);
        $this->assertSame(1, $listing->premium_days);
    }

    private function createListing(array $attributes = []): Listing
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Тестовая категория',
            'slug' => 'test-category-' . uniqid(),
        ]);

        return Listing::create(array_merge([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Тестовое объявление',
            'description' => 'Описание тестового объявления',
            'price' => 1000,
            'price_type' => 'fixed',
            'status' => 'active',
            'is_active' => true,
        ], $attributes));
    }
}
