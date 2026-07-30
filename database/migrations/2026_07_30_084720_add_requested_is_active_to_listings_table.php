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
            $table->boolean('requested_is_active')
                ->nullable()
                ->after('is_active');
        });

        DB::table('listings')
            ->where('is_active', true)
            ->update([
                'status' => 'active',
                'requested_is_active' => null,
            ]);

        DB::table('listings')
            ->where('is_active', false)
            ->update([
                'status' => 'inactive',
                'requested_is_active' => null,
            ]);
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('requested_is_active');
        });
    }
};
