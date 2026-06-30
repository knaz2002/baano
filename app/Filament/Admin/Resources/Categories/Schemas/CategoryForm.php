<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Основное')->schema([
                Grid::make(2)->schema([
                    Select::make('parent_id')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload()
                        ->label('Родительская категория')
                        ->nullable(),

                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('Название'),

                    Select::make('type')
                        ->options([
                            'realty' => 'Недвижимость',
                            'transport' => 'Транспорт',
                            'equipment' => 'Техника',
                            'service' => 'Услуги',
                        ])
                        ->required()
                        ->label('Тип'),

                    TextInput::make('icon')
                        ->label('Иконка (emoji или класс)'),

                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->label('Сортировка'),

                    Toggle::make('is_active')
                        ->default(true)
                        ->label('Активна'),
                ]),
            ]),
        ]);
    }
}