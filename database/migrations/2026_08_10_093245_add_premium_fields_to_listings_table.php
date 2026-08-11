<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->boolean('is_premium')->default(false)->after('is_active');
            $table->unsignedTinyInteger('premium_days')->nullable()->after('is_premium');
            $table->timestamp('premium_until')->nullable()->after('premium_days');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'is_premium',
                'premium_days',
                'premium_until',
            ]);
        });
    }
};
