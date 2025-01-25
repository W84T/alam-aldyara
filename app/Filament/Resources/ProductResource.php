<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Awcodes\FilamentBadgeableColumn\Components\Badge;
use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    use Translatable;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make(__('panel.product_info'))->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                            ->label(__('form.product_name')),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->unique(Product::class, 'slug', ignoreRecord: true),

                        MarkdownEditor::make('description')
                            ->columnSpanFull()
                            ->label(__('form.description'))
                            ->fileAttachmentsDirectory('products')
                    ])->columns(2),
                    Section::make(__('panel.images'))->schema([
                        FileUpload::make('images')
                            ->multiple()
                            ->label(__('form.product_images'))
                            ->directory('products')
                            ->maxFiles(5)
                            ->reorderable(true)
                            ->imageEditor()
                            ->optimize('webp'),
                    ])
                ])->columnSpan(2),

                Group::make()->schema([
                    Section::make(__('panel.price'))->schema([
                        TextInput::make('price')
                            ->numeric()
                            ->label(__('form.product_price'))
                            ->required()
                            ->prefix('USD'),
                    ]),

                    Section::make(__('panel.association'))->schema([
                        Select::make('category_id')
                            ->label(__('form.product_category'))
                            ->searchable()
                            ->preload()
                            ->relationship('category', 'name'),

                        Select::make('brand_id')
                            ->label(__('form.product_brand'))
                            ->searchable()
                            ->preload()
                            ->relationship('brand', 'name'),
                    ]),
                    Section::make(__('panel.statuses'))->schema([
                        Toggle::make('in_stock')
                            ->required()
                            ->label(__('form.in_stock'))
                            ->default(true),

                        Toggle::make('is_active')
                            ->required()
                            ->label(__('form.is_active'))
                            ->default(true),

                        Toggle::make('is_feature')
                            ->required()
                            ->label(__('form.is_featured'))
                            ->default(false),

                        Toggle::make('on_sale')
                            ->required()
                            ->label(__('form.on_sale'))
                            ->default(false),
                    ])
                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label(__('form.product_name'))
                    ->size(TextColumnSize::Medium),

                BadgeableColumn::make('name')
                    ->suffixBadges([
                        Badge::make('price')->label(fn($record) => $record->brand->name)->color('primary')->visible(true),
                        Badge::make('category.name')->label(fn($record) => $record->category->name)->color('primary')->visible(true),
                    ])
                    ->searchable()
                    ->size(TextColumnSize::Medium)
                    ->sortable(),


                TextColumn::make('price')
                    ->label(__('form.product_price'))
                    ->money('USD') // Keep the correct currency format
                    ->formatStateUsing(fn ($state) => App::getLocale() === 'ar' ? $state . ' دولار' : '$' . $state) // Append 'دولار' when Arabic
                    ->size(TextColumnSize::Medium),

                ToggleColumn::make('in_stock')->label(__('form.in_stock')),
                ToggleColumn::make('is_active')->label(__('form.is_active')),
                ToggleColumn::make('on_sale')->label(__('form.on_sale')),
                ToggleColumn::make('is_feature')->label(__('form.is_featured')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()->label(false),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('panel.product');
    }


    public static function getPluralModelLabel(): string
    {
        return __('panel.products');
    }
}
