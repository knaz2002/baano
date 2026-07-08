<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingResource\Pages;
use App\Models\Listing;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-briefcase';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Контент';
    }

    public static function getNavigationLabel(): string
    {
        return 'Объявления';
    }

    public static function getModelLabel(): string
    {
        return 'Объявление';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Объявления';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('title')
                    ->label('Заголовок')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                    
                Forms\Components\Select::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->required(),
                    
                Forms\Components\TextInput::make('price')
                    ->label('Цена')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                    
                Forms\Components\Select::make('price_type')
                    ->label('Тип цены')
                    ->options([
                        'fixed' => 'Фиксированная',
                        'hourly' => 'В час',
                        'daily' => 'В день',
                        'monthly' => 'В месяц',
                        'negotiable' => 'Договорная',
                    ])
                    ->required(),
                    
                Forms\Components\TextInput::make('location')
                    ->label('Местоположение')
                    ->maxLength(255),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label('Активно')
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
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB'),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name'),
                    
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
            'index' => Pages\ListListings::route('/'),
            'create' => Pages\CreateListing::route('/create'),
            'edit' => Pages\EditListing::route('/{record}/edit'),
        ];
    }
}
