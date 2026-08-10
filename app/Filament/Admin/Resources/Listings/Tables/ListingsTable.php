<?php

namespace App\Filament\Admin\Resources\Listings\Tables;

use App\Enums\ModerationStatus;
use App\Models\Listing;
use App\Models\User;
use App\Services\Moderation\ModerationDecisionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use RuntimeException;

class ListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('moderation_status')
                    ->label('Модерация')
                    ->badge()
                    ->formatStateUsing(
                        fn (?ModerationStatus $state): string =>
                            $state?->label() ?? 'Не задан'
                    )
                    ->color(
                        fn (?ModerationStatus $state): string =>
                            match ($state) {
                                ModerationStatus::Draft => 'gray',
                                ModerationStatus::PendingModeration => 'warning',
                                ModerationStatus::Approved => 'success',
                                ModerationStatus::ManualReview => 'info',
                                ModerationStatus::Rejected => 'danger',
                                default => 'gray',
                            }
                    ),

                TextColumn::make('status')
                    ->label('Публикация')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'draft' => 'Черновик',
                            'pending' => 'Ожидает решения',
                            'active' => 'Активно',
                            'inactive' => 'Неактивно',
                            'sold' => 'Завершено',
                            default => $state,
                        }
                    )
                    ->color(
                        fn (string $state): string => match ($state) {
                            'draft' => 'gray',
                            'pending' => 'warning',
                            'active' => 'success',
                            'inactive' => 'gray',
                            'sold' => 'info',
                            default => 'gray',
                        }
                    ),

                IconColumn::make('is_active')
                    ->label('Опубликовано')
                    ->boolean(),

                TextColumn::make('moderation_reason')
                    ->label('Причина')
                    ->limit(40)
                    ->placeholder('—')
                    ->tooltip(
                        fn (Listing $record): ?string =>
                            $record->moderation_reason
                    ),

                TextColumn::make('title')
                    ->label('Наименование')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('user.name')
                    ->label('Автор')
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Категория'),

                TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('moderation_status')
                    ->label('Статус модерации')
                    ->options([
                        ModerationStatus::PendingModeration->value =>
                            'На модерации',
                        ModerationStatus::ManualReview->value =>
                            'Ручная проверка',
                        ModerationStatus::Approved->value =>
                            'Одобрено',
                        ModerationStatus::Rejected->value =>
                            'Отклонено',
                    ]),

                SelectFilter::make('status')
                    ->label('Статус публикации')
                    ->options([
                        'draft' => 'Черновик',
                        'pending' => 'Ожидает решения',
                        'active' => 'Активно',
                        'inactive' => 'Неактивно',
                        'sold' => 'Завершено',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Одобрить объявление')
                    ->modalDescription(
                        'Объявление будет опубликовано согласно запросу пользователя.'
                    )
                    ->action(function (Listing $record): void {
                        $reviewer = auth()->user();

                        if (!$reviewer instanceof User) {
                            throw new RuntimeException(
                                'Администратор не авторизован.'
                            );
                        }

                        app(ModerationDecisionService::class)
                            ->approve($record, $reviewer);

                        Notification::make()
                            ->title('Объявление одобрено')
                            ->success()
                            ->send();
                    })
                    ->visible(
                        fn (Listing $record): bool => in_array(
                            $record->moderation_status,
                            [
                                ModerationStatus::PendingModeration,
                                ModerationStatus::ManualReview,
                                ModerationStatus::Rejected,
                            ],
                            true
                        )
                    ),

                Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Причина отклонения')
                            ->required()
                            ->minLength(5)
                            ->maxLength(2000)
                            ->rows(4),
                    ])
                    ->modalHeading('Отклонить объявление')
                    ->modalSubmitActionLabel('Отклонить')
                    ->action(
                        function (
                            array $data,
                            Listing $record,
                        ): void {
                            $reviewer = auth()->user();

                            if (!$reviewer instanceof User) {
                                throw new RuntimeException(
                                    'Администратор не авторизован.'
                                );
                            }

                            app(ModerationDecisionService::class)
                                ->reject(
                                    $record,
                                    $reviewer,
                                    $data['reason']
                                );

                            Notification::make()
                                ->title('Объявление отклонено')
                                ->danger()
                                ->send();
                        }
                    )
                    ->visible(
                        fn (Listing $record): bool => in_array(
                            $record->moderation_status,
                            [
                                ModerationStatus::PendingModeration,
                                ModerationStatus::ManualReview,
                            ],
                            true
                        )
                    ),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
