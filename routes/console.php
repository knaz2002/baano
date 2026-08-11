<?php

use App\Models\Listing;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('premium:expire', function () {
    $count = Listing::query()
        ->where('is_premium', true)
        ->whereNotNull('premium_until')
        ->where('premium_until', '<=', now())
        ->update([
            'is_premium' => false,
            'premium_until' => null,
        ]);

    $this->info("Снято с премиум размещения: {$count}");
})->purpose('Снимает объявления с истёкшего премиум размещения');

Schedule::command('premium:expire')
    ->everyMinute()
    ->withoutOverlapping();
