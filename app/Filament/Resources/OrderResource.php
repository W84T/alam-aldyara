<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([

                    Section::make(__('panel.order_info'))->schema([
                        Select::make('user_id')
                            ->label(__('form.customer'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('payment_method')
                            ->label(__('form.payment_method'))
                            ->required()
                            ->options([
                                'stripe' => __('form.credit'),
                                'cod' => __('form.cash'),
                            ]),

                        Select::make('payment_status')
                            ->label(__('form.payment_status'))
                            ->required()
                            ->options([
                                'pending' => __('status.pending'),
                                'paid' => __('status.paid'),
                                'failed' => __('status.failed'),
                            ])
                            ->default('pending'),

                        ToggleButtons::make('status')
                            ->label(__('form.status'))
                            ->inline()
                            ->required()
                            ->default('new')
                            ->options([
                                'new' => __('status.new'),
                                'processing' => __('status.processing'),
                                'shipping' => __('status.shipping'),
                                'delivered' => __('status.delivered'),
                                'cancelled' => __('status.cancelled'),
                            ])
                            ->colors([
                                'new' => 'info',
                                'processing' => 'warning',
                                'shipping' => 'success',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                            ])
                            ->icons([
                                'new' => 'heroicon-m-sparkles',
                                'processing' => 'heroicon-m-arrow-path',
                                'shipping' => 'heroicon-m-truck',
                                'delivered' => 'heroicon-m-check-badge',
                                'cancelled' => 'heroicon-m-x-circle',
                            ]),

                        Select::make('currency')
                            ->label(__('form.currency'))
                            ->required()
                            ->default('USD')
                            ->options([
                                'USD' => __('form.USD'),
                                'SYR' => __('form.SYR'),
                            ]),

                        Select::make('shipping_method')
                            ->label(__('form.shipping_method'))
                            ->required()
                            ->options([
                                'fedex' => __('form.fedex'),
                                'ups' => __('form.ups'),
                                'dhl' => __('form.dhl'),
                            ]),

                        Textarea::make('note')
                            ->label(__('form.note'))
                            ->columnSpanFull(),
                    ])->columns(2),
                    Section::make('order_details')
                        ->schema([
                            Repeater::make('items')
                                ->label(__("form.items"))
                                ->relationship()
                                ->schema([
                                    Select::make('product_id')
                                        ->relationship('product', 'name')
                                        ->label(__('form.product_name'))
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->distinct()
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->columnSpan(4)
                                        ->reactive()
                                        ->afterStateUpdated(fn($state, Set $set) => $set('unit_amount', Product::find($state)?->price ?? 0))
                                        ->afterStateUpdated(fn($state, Set $set) => $set('total_amount', Product::find($state)?->price ?? 0)),

                                    TextInput::make('quantity')
                                        ->label(__('form.quantity'))
                                        ->numeric()
                                        ->required()
                                        ->default(1)
                                        ->minValue(1)
                                        ->reactive()
                                        ->afterStateUpdated(fn($state, Set $set, Get $get) => $set('total_amount', $state * $get('unit_amount')))
                                        ->columnSpan(2),

                                    TextInput::make('unit_amount')
                                        ->label(__('form.unit_amount'))
                                        ->numeric()
                                        ->dehydrated()
                                        ->required()
                                        ->disabled()
                                        ->columnSpan(3),

                                    TextInput::make('total_amount')
                                        ->label(__('form.total_amount'))
                                        ->numeric()
                                        ->required()
                                        ->dehydrated()
                                        ->columnSpan(3),

                                ])->columns(12),

                            Placeholder::make('grand_total_placeholder')
                                ->label(__('form.grand_total'))
                                ->content(function (Get $get, Set $set) {
                                    $total = 0;
                                    if (!$repeaters = $get('items')) {
                                        return $total;
                                    }
                                    foreach ($repeaters as $key => $repeater) {
                                        $total += $get("items.{$key}.total_amount");
                                    }
                                    $set('grand_total', $total);
                                    return Number::currency($total, 'USD');
                                }),
                            Hidden::make('grand_total')->default(0)
                        ])
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make(__('user.name'))
                    ->label(__('form.customer'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('grand_total')
                    ->label(__('form.grand_total'))
                    ->money('USD')
                    ->formatStateUsing(fn ($state) => App::getLocale() === 'ar' ? $state . ' دولار' : '$' . $state)
                    ->size(TextColumn\TextColumnSize::Medium),

                TextColumn::make('payment_method')
                    ->label(__('form.payment_method'))
                    ->formatStateUsing(fn (string $state): string => __("form.{$state}"))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label(__('form.payment_status'))
                    ->formatStateUsing(fn (string $state): string => __("status.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'paid' => 'success',
                    })
                    ->searchable()
                    ->sortable(),

                SelectColumn::make('status')
                    ->label(__('form.status'))
                    ->options([
                        'new' => __('status.new'),
                        'processing' => __('status.processing'),
                        'shipping' => __('status.shipping'),
                        'delivered' => __('status.delivered'),
                        'cancelled' => __('status.cancelled'),
                    ])
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
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
              RelationManagers\AddressRelationManager::class
        ];
    }

    public static function getNavigationBadge(): ?string{
        return static::getModel()::where('status', 'new')->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
//            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('panel.order');
    }


    public static function getPluralModelLabel(): string
    {
        return __('panel.orders');
    }
}
