<?php

namespace App\Filament\Admin\Resources\Reviews;

use App\Filament\Admin\Resources\Reviews\Pages;
use App\Models\Review;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

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
                ->label('Одобрен')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
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
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Одобрен')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Статус')
                    ->options([
                        true => 'Одобренные',
                        false => 'На модерации',
                    ]),
            ])
            ->actions([
                Actions\Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Review $record) {
                        $record->update(['is_active' => true]);
                        Notification::make()
                            ->title('Отзыв одобрен')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Review $record) => !$record->is_active),

                Actions\Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Review $record) {
                        $record->update(['is_active' => false]);
                        Notification::make()
                            ->title('Отзыв отклонён')
                            ->danger()
                            ->send();
                    })
                    ->visible(fn (Review $record) => $record->is_active),

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