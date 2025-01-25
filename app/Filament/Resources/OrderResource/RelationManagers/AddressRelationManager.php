<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddressRelationManager extends RelationManager
{
    protected static string $relationship = 'address';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name')
                    ->label(__('form.first_name'))
                    ->required()
                    ->maxLength(255),

                TextInput::make('last_name')
                    ->label(__('form.last_name'))
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label(__('form.phone'))
                    ->required()
                    ->tel()
                    ->maxLength(20),

                TextInput::make('city')
                    ->label(__('form.city'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('state')
                    ->label(__('form.state'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('zip_code')
                    ->label(__('form.zip_code'))
                    ->required()
                    ->numeric()
                    ->maxLength(255),
                Textarea::make('address')
                    ->label(__('form.street_address'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('street_address')
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('form.full_name')),
                TextColumn::make('phone')->label(__('form.phone')),
                TextColumn::make('city')->label(__('form.city')),
                TextColumn::make('state')->label(__('form.state')),
                TextColumn::make('zip_code')->label(__('form.zip_code')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
