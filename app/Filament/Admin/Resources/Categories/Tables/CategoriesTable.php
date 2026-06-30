<?php

namespace App\Filament\Admin\Resources\Categories\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('parent.name')->label('Родитель')->sortable(),
                TextColumn::make('type')->label('Тип')->badge(),
                IconColumn::make('is_active')->boolean()->label('Активна'),
                TextColumn::make('sort_order')->sortable(),
            ]);
    }
}