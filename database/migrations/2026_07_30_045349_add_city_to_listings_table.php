<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table
                ->string('city', 120)
                ->nullable()
                ->after('location')
                ->index();
        });

        DB::table('listings')
            ->select([
                'id',
                'location',
            ])
            ->orderBy('id')
            ->chunkById(100, function ($listings): void {
                foreach ($listings as $listing) {
                    $location = trim(
                        (string) $listing->location
                    );

                    if (
                        $location === ''
                        || mb_strtolower($location)
                            === 'адрес не указан'
                    ) {
                        continue;
                    }

                    $city = null;

                    if (
                        preg_match(
                            '/(?:^|,\s*)г\.?\s*([^,]+)/ui',
                            $location,
                            $matches
                        )
                    ) {
                        $city = trim($matches[1]);
                    }

                    if ($city === null || $city === '') {
                        continue;
                    }

                    DB::table('listings')
                        ->where('id', $listing->id)
                        ->update([
                            'city' => $city,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex(['city']);
            $table->dropColumn('city');
        });
    }
};
