<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Переименовываем колонку attributes в listing_attributes
        DB::statement("ALTER TABLE listings CHANGE COLUMN attributes listing_attributes JSON NULL");
    }

    public function down(): void
    {
        // Возвращаем обратно
        DB::statement("ALTER TABLE listings CHANGE COLUMN listing_attributes attributes JSON NULL");
    }
};