<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Filament\Resources\DiscountResource\RelationManagers;
use App\Models\Discount;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->label(__('form.name'))
                    ->maxLength(255),

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

                Select::make('applies_to')
                    ->label(__('form.applied_to'))
                    ->options([
                        'product' => __('form.products'),
                        'category' => __('form.categories'),
                    ])
                    ->required()
                    ->reactive(),

                Select::make('products')
                    ->label(__('form.products'))
                    ->multiple()
                    ->relationship('products', 'name')  // Correct relationship reference
                    ->preload()
                    ->visible(fn($get) => $get('applies_to') === 'product')
                    ->options(fn () => \App\Models\Product::whereDoesntHave('discounts')->pluck('name', 'id')),

                Select::make('categories')
                    ->label(__('form.categories'))
                    ->multiple()
                    ->preload()
                    ->relationship('categories', 'name') // Correct relationship reference
                    ->visible(fn($get) => $get('applies_to') === 'category'),

                Toggle::make('is_active')
                    ->label(__('form.is_active'))
                    ->default(false),

                Forms\Components\DatePicker::make('start_date')
                    ->label(__('form.start_date'))
                    ->nullable(),

                Forms\Components\DatePicker::make('end_date')
                    ->label(__('form.end_date'))
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('form.name'))
                    ->sortable(),
                TextColumn::make('discount_type')
                    ->label(__('form.discount_type'))
                    ->formatStateUsing(fn($state) => __("form.{$state}"))
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('form.value'))
                    ->formatStateUsing(fn($state, $record) => $record->discount_type === 'fixed'
                        ? $state . '$'
                        : $state . '%'),
                TextColumn::make('applies_to')->sortable()
                    ->label(__('form.is_active')),
                TextColumn::make('start_date')->sortable(),
                TextColumn::make('end_date')->sortable(),
            ])
            ->filters([
                SelectFilter::make('discount_type')
                    ->options([
                        'fixed' => 'Fixed Amount',
                        'percentage' => 'Percentage',
                    ]),
                SelectFilter::make('applies_to')
                    ->options([
                        'product' => 'Product',
                        'category' => 'Category',
                        'bulk' => 'Bulk Purchase',
                    ]),
            ])
            ->actions([
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
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('panel.discount');
    }


    public static function getPluralModelLabel(): string
    {
        return __('panel.discounts');
    }
}
