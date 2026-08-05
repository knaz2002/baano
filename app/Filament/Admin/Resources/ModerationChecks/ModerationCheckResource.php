<?php

namespace App\Filament\Admin\Resources\ModerationChecks;

use App\Filament\Admin\Resources\ModerationChecks\Pages\ListModerationChecks;
use App\Filament\Admin\Resources\ModerationChecks\Tables\ModerationChecksTable;
use App\Models\ModerationCheck;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ModerationCheckResource extends Resource
{
    protected static ?string $model = ModerationCheck::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-shield-check';
    }

    public static function getNavigationLabel(): string
    {
        return 'История модерации';
    }

    public static function getNavigationGroup(): string
    {
        return 'Безопасность';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getModelLabel(): string
    {
        return 'проверка';
    }

    public static function getPluralModelLabel(): string
    {
        return 'история модерации';
    }

    public static function table(Table $table): Table
    {
        return ModerationChecksTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModerationChecks::route('/'),
        ];
    }
}
