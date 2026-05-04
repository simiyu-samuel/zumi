<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255),
                Forms\Components\TextInput::make('google_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('apple_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('drops_balance')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('flow_score')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('onboarding_completed')
                    ->required(),
                Forms\Components\DateTimePicker::make('verified_at'),
                Forms\Components\Textarea::make('bio')
                    ->columnSpanFull(),
                Forms\Components\Select::make('role')
                    ->options(\App\Enums\UserRole::class)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(\App\Enums\UserStatus::class)
                    ->required(),
                Forms\Components\TextInput::make('stripe_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('pm_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('pm_last_four')
                    ->maxLength(4),
                Forms\Components\DateTimePicker::make('trial_ends_at'),
                Forms\Components\TextInput::make('stripe_connect_id')
                    ->maxLength(255),
                Forms\Components\Toggle::make('stripe_onboarding_completed')
                    ->required(),
                Forms\Components\TextInput::make('notification_settings'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('google_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('apple_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('drops_balance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('flow_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('onboarding_completed')
                    ->boolean(),
                Tables\Columns\TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('stripe_id')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pm_type')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pm_last_four')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('stripe_connect_id')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('stripe_onboarding_completed')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
