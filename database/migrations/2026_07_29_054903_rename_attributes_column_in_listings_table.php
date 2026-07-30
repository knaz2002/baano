<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasColumn('listings', 'attributes')
            || Schema::hasColumn('listings', 'listing_attributes')
        ) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            $table->renameColumn(
                'attributes',
                'listing_attributes'
            );
        });
    }

    public function down(): void
    {
        if (
            ! Schema::hasColumn('listings', 'listing_attributes')
            || Schema::hasColumn('listings', 'attributes')
        ) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            $table->renameColumn(
                'listing_attributes',
                'attributes'
            );
        });
    }
};
