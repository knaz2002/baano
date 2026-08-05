<?php

namespace App\Filament\Admin\Resources\Reviews;

use App\Enums\ModerationStatus;
use App\Filament\Admin\Resources\Reviews\Pages;
use App\Models\Review;
use App\Models\User;
use App\Services\Moderation\ModerationDecisionService;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use RuntimeException;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-chat-bubble-left-right';
    }

    public static function getNavigationLabel(): string
    {
        return 'Отзывы';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function getNavigationGroup(): string
    {
        return 'Контент';
    }

    public static function schema(Schema $schema): Schema
    {
        return $schema->components([
            Components\Select::make('listing_id')
                ->relationship('listing', 'title')
                ->required()
                ->searchable(),

            Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->required()
                ->searchable(),

            Components\TextInput::make('rating')
                ->required()
                ->integer()
                ->minValue(1)
                ->maxValue(5),

            Components\Textarea::make('comment')
                ->maxLength(1000),

            Components\Toggle::make('is_active')
                ->label('Опубликован'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('listing.title')
                    ->label('Объявление')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Автор')
                    ->searchable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Оценка')
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(50)
                    ->tooltip(
                        fn (Review $record): ?string =>
                            $record->comment
                    ),

                Tables\Columns\TextColumn::make('moderation_status')
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

                Tables\Columns\TextColumn::make('moderation_reason')
                    ->label('Причина')
                    ->limit(40)
                    ->placeholder('—')
                    ->tooltip(
                        fn (Review $record): ?string =>
                            $record->moderation_reason
                    ),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Опубликован')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make(
                    'moderation_status'
                )
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
            ->actions([
                Actions\Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Review $record): void {
                        $reviewer = auth()->user();

                        if (!$reviewer instanceof User) {
                            throw new RuntimeException(
                                'Администратор не авторизован.'
                            );
                        }

                        app(ModerationDecisionService::class)
                            ->approve($record, $reviewer);

                        Notification::make()
                            ->title('Отзыв одобрен')
                            ->success()
                            ->send();
                    })
                    ->visible(
                        fn (Review $record): bool => in_array(
                            $record->moderation_status,
                            [
                                ModerationStatus::PendingModeration,
                                ModerationStatus::ManualReview,
                                ModerationStatus::Rejected,
                            ],
                            true
                        )
                    ),

                Actions\Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->schema([
                        Components\Textarea::make('reason')
                            ->label('Причина отклонения')
                            ->required()
                            ->minLength(5)
                            ->maxLength(2000)
                            ->rows(4),
                    ])
                    ->modalHeading('Отклонить отзыв')
                    ->modalSubmitActionLabel('Отклонить')
                    ->action(
                        function (
                            array $data,
                            Review $record,
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
                                ->title('Отзыв отклонён')
                                ->danger()
                                ->send();
                        }
                    )
                    ->visible(
                        fn (Review $record): bool => in_array(
                            $record->moderation_status,
                            [
                                ModerationStatus::PendingModeration,
                                ModerationStatus::ManualReview,
                            ],
                            true
                        )
                    ),

                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
