<?php

namespace App\Filament\Admin\Resources\Listings\Tables;

use App\Models\Listing;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->limit(30),
                TextColumn::make('user.name')->label('Автор')->sortable(),
                TextColumn::make('category.name')->label('Категория'),
                TextColumn::make('price')->money('RUB')->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending',
                        'success' => 'active',
                        'danger' => 'rejected',
                    ]),
                IconColumn::make('is_premium')->boolean()->label('VIP'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Черновик',
                        'pending' => 'На модерации',
                        'active' => 'Активна',
                    ]),
            ])
            ->actions([
  Action::make('approve')
    ->label('Одобрить')
    ->icon('heroicon-o-check-circle')
    ->color('success')
    ->requiresConfirmation()
    ->action(function (Listing $record) {
        $record->update([
            'status' => 'active',
            'is_active' => true,
        ]);

        Notification::make()
            ->title('Объявление одобрено')
            ->success()
            ->send();
    })
    ->visible(fn (Listing $record) => $record->status === 'pending'),
                
                Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Listing $record) {
                        $record->update([
                            'status' => 'rejected',
                            'is_active' => false, // ← ДОБАВЛЕНО
                        ]);

                        Notification::make()
                            ->title('Объявление отклонено')
                            ->danger()
                            ->send();
                    })
                    ->visible(fn (Listing $record) => $record->status === 'pending'),
            ]);
    }
}