<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Services\TelegramService;
use Awcodes\FilamentBadgeableColumn\Components\Badge;
use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use LaraZeus\Popover\Tables\PopoverColumn;
use Telegram\Bot\FileUpload\InputFile;


function handleMessage(Model $record)
{
    $message = "";
    $message .= "{$record->name}\n";
    $message .= " السعر {$record->price} $";
    return $message;
}

function sendToTelegram(array $data, Model $record)
{
    $message = $data['message'];

    $telegram = new TelegramService();

    if ($data['share_images'] && $record->images) {
        $media = [];
        foreach ($record->images as $index => $image) {
            $fileUrl = asset('storage/' . $image);

            $mediaItem = [
                'type' => 'photo',
//                'media' => InputFile::create($fileUrl),
                'media' =>  'https://letsenhance.io/static/8f5e523ee6b2479e26ecc91b9c25261e/1015f/MainAfter.jpg',
            ];

            if ($index === 0) {
                $mediaItem['caption'] = $message;
                $mediaItem['parse_mode'] = 'Markdown';
            }

            $media[] = $mediaItem;
        }

        $telegram->sendMediaGroup($media);
    } else {
        $telegram->sendMessage($message);
    }

    Notification::make()
        ->title('Success')
        ->body('Product details sent to Telegram!')
        ->success()
        ->send();
}

class ProductResource extends Resource
{
    use Translatable;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                PopoverColumn::make('name')
                    ->trigger('hover')
                    ->offset(0)
                    ->placement('left')
                    ->popOverMaxWidth('none')
                    ->icon('heroicon-o-qr-code')
                    ->content(fn(Model $record) => view('filament.qr_card', ['record' => $record])),

                BadgeableColumn::make('category.name')
                    ->suffixBadges([
                        Badge::make('category.name')->label(fn($record) => $record->category->name)->color('primary')->visible(true),
                    ])
                    ->searchable()
                    ->size(TextColumnSize::Medium)
                    ->sortable(),


                TextColumn::make('name')->sortable(),

                TextColumn::make('price')
                    ->label(__('form.product_price'))
                    ->money('USD')
                    ->formatStateUsing(function($state, $record) {

                        $activeDiscount = $record->discounts()
                            ->where('discount_products.is_active', true)
                            ->first();

                        if ($activeDiscount) {
                            $discountedPrice = $state - ($state * $activeDiscount->value / 100); // Assuming it's a percentage discount
                            return '<span style="text-decoration: line-through; font-size: 14px"">' . $state . '$</span><br>' . $discountedPrice . '$';
                        }

                        // If no discount, just show the regular price
                        return $state;
                    })
                    ->html()
                    ->size(TextColumnSize::Medium),


                ToggleColumn::make('in_stock')->label(__('form.in_stock')),
                ToggleColumn::make('is_active')->label(__('form.is_active')),
                ToggleColumn::make('is_featured')->label(__('form.is_featured')),
            ])
            ->filters([
                //
            ])
            ->actions([

                ActionGroup::make([

                    Action::make('Send to Telegram')
                        ->label(__('form.share'))
                        ->icon('heroicon-s-share')
                        ->color('black')
                        ->form([
                            Toggle::make('share_images')
                                ->label(__('form.send_product_image'))
                                ->default(true),
                            MarkdownEditor::make('message')
                                ->label(__('form.message'))
                                ->default(fn(Model $record) => handleMessage($record))
                        ])
                        ->action(fn(array $data, Model $record) => sendToTelegram($data, $record)),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

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

                        Toggle::make('is_featured')
                            ->required()
                            ->label(__('form.is_featured'))
                            ->default(false),
                    ])
                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DiscountsRelationManager::class
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
