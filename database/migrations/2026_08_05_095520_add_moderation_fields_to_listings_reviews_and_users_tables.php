<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('moderation_status', 32)
                ->default('approved')
                ->index();
            $table->text('moderation_reason')->nullable();
            $table->timestamp('moderated_at')->nullable();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->string('moderation_status', 32)
                ->default('approved')
                ->index();
            $table->text('moderation_reason')->nullable();
            $table->timestamp('moderated_at')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('moderation_status', 32)
                ->default('approved')
                ->index();
            $table->string('pending_name')->nullable();
            $table->text('moderation_reason')->nullable();
            $table->timestamp('moderated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'moderation_status',
                'moderation_reason',
                'moderated_at',
            ]);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn([
                'moderation_status',
                'moderation_reason',
                'moderated_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'moderation_status',
                'pending_name',
                'moderation_reason',
                'moderated_at',
            ]);
        });
    }
};
