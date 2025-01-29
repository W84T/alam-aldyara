<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DiscountsRelationManager extends RelationManager
{
    protected static string $relationship = 'discounts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('name')
                    ->required()
                    ->label(__('form.name'))
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label(__('form.is_active'))
                    ->default(false)
                    ->inline(false),

                Select::make('discount_type')
                    ->label(__('form.discount_type'))
                    ->options([
                        'fixed' => __('form.fixed'),
                        'percentage' => __('form.percentage'),
                    ])
                    ->required(),

                TextInput::make('value')
                    ->label(__('form.value'))
                    ->numeric()
                    ->required(),

                Forms\Components\DatePicker::make('start_date')
                    ->label(__('form.start_date'))
                    ->nullable(),

                Forms\Components\DatePicker::make('end_date')
                    ->label(__('form.end_date'))
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->label(__('form.name')),
                TextColumn::make('discount_type')
                    ->label(__('form.discount_type'))
                    ->formatStateUsing(fn($state) => __("form.{$state}"))
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('form.value'))
                    ->formatStateUsing(fn($state, $record) => $record->discount_type === 'fixed'
                        ? $state . '$'
                        : $state . '%'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label(__('form.is_active'))
                    ->getStateUsing(fn($record) => $record->pivot->is_active) // Fetch pivot value
                    ->afterStateUpdated(function ($record, $state) {
                        $record->pivot->update(['is_active' => $state]); // Update pivot field
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
            ]);
    }
}
