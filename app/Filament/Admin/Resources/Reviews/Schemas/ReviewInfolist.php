<?php

namespace App\Filament\Admin\Resources\Reviews\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ReviewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('listing.title')
                    ->label('Объявление'),
                TextEntry::make('user.name')
                    ->label('Пользователь'),
                TextEntry::make('booking.id')
                    ->label('Бронирование')
                    ->placeholder('-'),
                TextEntry::make('rating')
                    ->numeric(),
                TextEntry::make('comment')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
            ]);
    }
}
