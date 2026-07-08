<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Справочники';
    }

    public static function getNavigationLabel(): string
    {
        return 'Пользователи';
    }

    public static function getModelLabel(): string
    {
        return 'Пользователь';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Пользователи';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label('Имя')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                    
                Forms\Components\TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                    
                Forms\Components\TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => ! empty($state) ? Hash::make($state) : '')
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->minLength(8),
                    
                Forms\Components\Select::make('role')
                    ->label('Роль')
                    ->options([
                        'user' => 'Пользователь',
                        'admin' => 'Администратор',
                    ])
                    ->default('user'),
                    
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('Email подтвержден'),
                
                Forms\Components\DateTimePicker::make('phone_verified_at')
                    ->label('Телефон подтвержден'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон'),
                
                Tables\Columns\TextColumn::make('role')
                    ->label('Роль')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Администратор',
                        default => 'Пользователь',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'success',
                        default => 'secondary',
                    }),
                    
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email подтвержден')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => ! is_null($record->email_verified_at)),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Роль')
                    ->options([
                        'user' => 'Пользователь',
                        'admin' => 'Администратор',
                    ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
