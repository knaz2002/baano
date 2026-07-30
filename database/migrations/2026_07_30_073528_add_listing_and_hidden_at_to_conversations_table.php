<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->index(
                'user_one_id',
                'conversations_user_one_id_index'
            );
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropUnique(
                'conversations_user_one_id_user_two_id_unique'
            );
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('listing_id')
                ->after('id')
                ->constrained('listings')
                ->cascadeOnDelete();

            $table->timestamp('hidden_for_user_one_at')
                ->nullable()
                ->after('last_message_at');

            $table->timestamp('hidden_for_user_two_at')
                ->nullable()
                ->after('hidden_for_user_one_at');

            $table->unique(
                ['listing_id', 'user_one_id', 'user_two_id'],
                'conversations_listing_users_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropUnique(
                'conversations_listing_users_unique'
            );

            $table->dropForeign(['listing_id']);

            $table->dropColumn([
                'listing_id',
                'hidden_for_user_one_at',
                'hidden_for_user_two_at',
            ]);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->unique(
                ['user_one_id', 'user_two_id'],
                'conversations_user_one_id_user_two_id_unique'
            );
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(
                'conversations_user_one_id_index'
            );
        });
    }
};
