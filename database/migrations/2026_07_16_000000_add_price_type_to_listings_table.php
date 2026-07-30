<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('listings', 'price_type')) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            $table
                ->string('price_type')
                ->default('fixed')
                ->after('price');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('listings', 'price_type')) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('price_type');
        });
    }
};
