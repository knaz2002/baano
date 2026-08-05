<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use App\Enums\ModerationStatus;
use App\Models\User;
use App\Services\Moderation\ModerationDecisionService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use RuntimeException;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Одобренное имя')
                    ->searchable(),

                TextColumn::make('pending_name')
                    ->label('Новое имя')
                    ->searchable()
                    ->placeholder('—')
                    ->weight('bold'),

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
                                ModerationStatus::PendingModeration =>
                                    'warning',
                                ModerationStatus::Approved => 'success',
                                ModerationStatus::ManualReview => 'info',
                                ModerationStatus::Rejected => 'danger',
                                default => 'gray',
                            }
                    ),

                TextColumn::make('moderation_reason')
                    ->label('Причина')
                    ->limit(40)
                    ->placeholder('—')
                    ->tooltip(
                        fn (User $record): ?string =>
                            $record->moderation_reason
                    ),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->placeholder('—'),

                IconColumn::make('is_admin')
                    ->label('Администратор')
                    ->boolean(),

                TextColumn::make('role')
                    ->label('Роль')
                    ->badge()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Регистрация')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('moderated_at')
                    ->label('Проверено')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
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
            ])
            ->recordActions([
                Action::make('approve_profile')
                    ->label('Одобрить имя')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Одобрить новое имя')
                    ->modalDescription(
                        'Новое имя станет публичным вместо ранее одобренного.'
                    )
                    ->action(function (User $record): void {
                        $reviewer = auth()->user();

                        if (!$reviewer instanceof User) {
                            throw new RuntimeException(
                                'Администратор не авторизован.'
                            );
                        }

                        app(ModerationDecisionService::class)
                            ->approve($record, $reviewer);

                        Notification::make()
                            ->title('Новое имя одобрено')
                            ->success()
                            ->send();
                    })
                    ->visible(
                        fn (User $record): bool =>
                            filled($record->pending_name)
                            && in_array(
                                $record->moderation_status,
                                [
                                    ModerationStatus::PendingModeration,
                                    ModerationStatus::ManualReview,
                                    ModerationStatus::Rejected,
                                ],
                                true
                            )
                    ),

                Action::make('reject_profile')
                    ->label('Отклонить имя')
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
                    ->modalHeading('Отклонить новое имя')
                    ->modalDescription(
                        'Старое одобренное имя останется публичным.'
                    )
                    ->modalSubmitActionLabel('Отклонить')
                    ->action(
                        function (
                            array $data,
                            User $record,
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
                                ->title('Новое имя отклонено')
                                ->danger()
                                ->send();
                        }
                    )
                    ->visible(
                        fn (User $record): bool =>
                            filled($record->pending_name)
                            && in_array(
                                $record->moderation_status,
                                [
                                    ModerationStatus::PendingModeration,
                                    ModerationStatus::ManualReview,
                                ],
                                true
                            )
                    ),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
