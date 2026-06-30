<?php

namespace App\Filament\Admin\Resources\Listings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Основное')->schema([
                Grid::make(2)->schema([
                    Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->label('Пользователь'),

                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->label('Категория'),

                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->label('Заголовок'),

                    Select::make('price_type')
                        ->options([
                            'fixed' => 'Фиксированная',
                            'hourly' => 'За час',
                            'daily' => 'За сутки',
                            'monthly' => 'За месяц',
                            'negotiable' => 'Договорная',
                        ])
                        ->required()
                        ->label('Тип цены'),

                    TextInput::make('price')
                        ->numeric()
                        ->required()
                        ->label('Цена'),

                    Select::make('status')
                        ->options([
                            'draft' => 'Черновик',
                            'pending' => 'На модерации',
                            'active' => 'Активна',
                            'rejected' => 'Отклонена',
                            'archived' => 'В архиве',
                        ])
                        ->required()
                        ->label('Статус'),

                    Toggle::make('is_premium')
                        ->label('Премиум размещение'),
                ]),

                Textarea::make('description')
                    ->required()
                    ->columnSpanFull()
                    ->label('Описание'),
            ])->columns(2),

            Section::make('Фотографии')->schema([
                FileUpload::make('images')
                    ->multiple()
                    ->maxFiles(10)
                    ->image()
                    ->directory('listings')
                    ->label('Изображения'),
            ])->columnSpanFull(),
        ]);
    }
}