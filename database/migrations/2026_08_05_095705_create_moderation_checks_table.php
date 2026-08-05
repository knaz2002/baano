<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moderation_checks', function (Blueprint $table) {
            $table->id();

            $table->string('moderatable_type');
            $table->unsignedBigInteger('moderatable_id');

            $table->string('content_type', 32);
            $table->string('content_reference')->nullable();
            $table->char('content_hash', 64);
            $table->json('content_snapshot')->nullable();

            $table->string('provider', 64);
            $table->string('model', 128)->nullable();
            $table->string('status', 32)->default('pending')->index();

            $table->json('categories')->nullable();
            $table->json('scores')->nullable();
            $table->text('reason')->nullable();

            $table->timestamp('checked_at')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(
                ['moderatable_type', 'moderatable_id'],
                'moderation_checks_moderatable_index'
            );

            $table->index(
                ['content_hash', 'provider'],
                'moderation_checks_hash_provider_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_checks');
    }
};
