<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaveResource\Pages;
use App\Filament\Resources\WaveResource\RelationManagers;
use App\Models\Wave;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WaveResource extends Resource
{
    protected static ?string $model = Wave::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('stream_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('thumbnail_url')
                    ->maxLength(255),
                Forms\Components\Select::make('visibility')
                    ->options(\App\Enums\WaveVisibility::class)
                    ->required(),
                Forms\Components\TextInput::make('gated_drops')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('likes_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('comments_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('shares_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('views_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('cloudflare_id')
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options(\App\Enums\WaveStatus::class)
                    ->required(),
                Forms\Components\TextInput::make('video_metadata'),
                Forms\Components\TextInput::make('circle_id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID'),
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stream_id')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('thumbnail_url')
                    ->circular(),
                Tables\Columns\TextColumn::make('visibility')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gated_drops')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('likes_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('comments_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shares_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('views_count')
                    ->numeric()
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
                Tables\Columns\TextColumn::make('cloudflare_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('visibility')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('circle_id'),
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
            'index' => Pages\ListWaves::route('/'),
            'create' => Pages\CreateWave::route('/create'),
            'view' => Pages\ViewWave::route('/{record}'),
            'edit' => Pages\EditWave::route('/{record}/edit'),
        ];
    }
}
