<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Filament\Resources\DiscountResource\RelationManagers;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')
                    ->label(__('form.discount_code'))
                    ->unique(ignoreRecord: true)
                    ->nullable(),

                Select::make('type')
                    ->label(__('form.discount_type'))
                    ->options([
                        'percentage' => __('form.percentage'),
                        'fixed' => __('form.fixed'),
                    ])
                    ->required(),

                TextInput::make('value')
                    ->label(__('form.discount_value'))
                    ->numeric()
                    ->required(),

                DatePicker::make('start_date')
                    ->label(__('form.discount_start_date'))
                    ->required(),

                DatePicker::make('end_date')
                    ->label(__('form.discount_end_date'))
                    ->required(),

                Select::make('category_id')
                    ->label(__('form.discount_category'))
                    ->options(Category::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn ($set) => $set('product_id', null))
                    ->nullable(),

                Select::make('product_id')
                    ->label(__('form.discount_products'))
                    ->options(fn (callable $get) =>
                    Product::whereDoesntHave('discounts', function ($query) {
                        $query->where('is_active', true);
                    })
                        ->when($get('category_id'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
                        ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
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
}
