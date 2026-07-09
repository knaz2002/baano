<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-chat-bubble-left-ellipsis';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Контент';
    }

    public static function getNavigationLabel(): string
    {
        return 'Отзывы';
    }

    public static function getModelLabel(): string
    {
        return 'Отзыв';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Отзывы';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('listing_id')
                    ->label('Объявление')
                    ->relationship('listing', 'title')
                    ->required(),
                    
                Forms\Components\Select::make('user_id')
                    ->label('Пользователь')
                    ->relationship('user', 'name')
                    ->required(),
                    
                Forms\Components\TextInput::make('rating')
                    ->label('Оценка')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5),
                    
                Forms\Components\Textarea::make('comment')
                    ->label('Комментарий')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('listing.title')
                    ->label('Объявление')
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Автор'),
                    
                Tables\Columns\TextColumn::make('rating')
                    ->label('Оценка')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    }),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('listing_id')
                    ->label('Объявление')
                    ->relationship('listing', 'title'),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()->label('Редактировать'),
                \Filament\Actions\DeleteAction::make()->label('Удалить'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()->label('Удалить выбранные'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
