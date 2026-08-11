<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('recipient_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->uuid('token')->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_id', 'used_at']);
            $table->unique(['listing_id', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_invites');
    }
};
