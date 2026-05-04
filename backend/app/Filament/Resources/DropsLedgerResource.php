<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DropsLedgerResource\Pages;
use App\Filament\Resources\DropsLedgerResource\RelationManagers;
use App\Models\DropsLedger;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DropsLedgerResource extends Resource
{
    protected static ?string $model = DropsLedger::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name'),
                Forms\Components\Select::make('type')
                    ->options(\App\Enums\DropsTransactionType::class)
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('direction')
                    ->options([
                        'credit' => 'Credit',
                        'debit' => 'Debit',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('reference_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('reference_id'),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('completed'),
                Forms\Components\TextInput::make('metadata'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID'),
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record) => $record->direction === 'credit' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('direction')
                    ->badge()
                    ->color(fn ($state) => $state === 'credit' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('reference_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference_id'),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
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
            'index' => Pages\ListDropsLedgers::route('/'),
            'create' => Pages\CreateDropsLedger::route('/create'),
            'view' => Pages\ViewDropsLedger::route('/{record}'),
            'edit' => Pages\EditDropsLedger::route('/{record}/edit'),
        ];
    }
}
