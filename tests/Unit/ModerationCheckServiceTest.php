<?php

namespace Tests\Unit;

use App\Services\Moderation\ModerationCheckService;
use Tests\TestCase;

class ModerationCheckServiceTest extends TestCase
{
    public function test_equal_text_has_equal_hash(): void
    {
        $service = app(ModerationCheckService::class);

        $firstHash = $service->makeContentHash(
            'Сдам квартиру на длительный срок.'
        );

        $secondHash = $service->makeContentHash(
            "  Сдам   квартиру\nна длительный срок. "
        );

        $this->assertSame($firstHash, $secondHash);
    }

    public function test_array_key_order_does_not_change_hash(): void
    {
        $service = app(ModerationCheckService::class);

        $firstHash = $service->makeContentHash([
            'title' => 'Аренда квартиры',
            'description' => 'Центр города',
        ]);

        $secondHash = $service->makeContentHash([
            'description' => 'Центр города',
            'title' => 'Аренда квартиры',
        ]);

        $this->assertSame($firstHash, $secondHash);
    }

    public function test_changed_content_has_different_hash(): void
    {
        $service = app(ModerationCheckService::class);

        $firstHash = $service->makeContentHash(
            'Сдам квартиру.'
        );

        $secondHash = $service->makeContentHash(
            'Продам квартиру.'
        );

        $this->assertNotSame($firstHash, $secondHash);
    }
}
