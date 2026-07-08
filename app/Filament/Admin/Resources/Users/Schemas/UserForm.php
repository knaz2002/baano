<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                DateTimePicker::make('email_verified_at'),
                DateTimePicker::make('phone_verified_at'),
                TextInput::make('phone_verification_code')
                    ->tel(),
                DateTimePicker::make('phone_verification_expires_at'),
                Toggle::make('is_admin')
                    ->required(),
                Textarea::make('push_subscription')
                    ->columnSpanFull(),
                TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_subscribed')
                    ->required(),
                DateTimePicker::make('subscription_expires_at'),
                TextInput::make('subscription_listings_limit')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('subscription_listings_used')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('role')
                    ->required()
                    ->default('user'),
            ]);
    }
}
