<?php

namespace App\Filament\Admin\Resources\ModerationChecks\Pages;

use App\Filament\Admin\Resources\ModerationChecks\ModerationCheckResource;
use Filament\Resources\Pages\ListRecords;

class ListModerationChecks extends ListRecords
{
    protected static string $resource = ModerationCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
