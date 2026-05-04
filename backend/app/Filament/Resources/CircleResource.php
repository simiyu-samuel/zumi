<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CircleResource\Pages;
use App\Filament\Resources\CircleResource\RelationManagers;
use App\Models\Circle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CircleResource extends Resource
{
    protected static ?string $model = Circle::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('owner_id')
                    ->relationship('owner', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->options(\App\Enums\CircleType::class)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(\App\Enums\CircleStatus::class)
                    ->required(),
                Forms\Components\TextInput::make('avatar')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('cover_image')
                    ->image(),
                Forms\Components\TextInput::make('members_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('monthly_drops_price')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID'),
                Tables\Columns\TextColumn::make('owner.name'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular(),
                Tables\Columns\ImageColumn::make('cover_image'),
                Tables\Columns\TextColumn::make('members_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('monthly_drops_price')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListCircles::route('/'),
            'create' => Pages\CreateCircle::route('/create'),
            'view' => Pages\ViewCircle::route('/{record}'),
            'edit' => Pages\EditCircle::route('/{record}/edit'),
        ];
    }
}
