<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Filament\Resources\BannerResource\RelationManagers;
use App\Models\Banner;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    use Translatable;

    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make(__('form.banner_details'))->schema([
                        TextInput::make('title')
                            ->required()
                            ->label(__('form.title'))
                            ->maxLength(255),
                        Textarea::make('description')
                            ->required()
                            ->label(__('form.sub_title'))
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->required()
                            ->label(__('form.is_active'))
                            ->default(true)
                    ]),
                ]),
                Group::make()->schema([
                    Section::make(__('form.banner_images'))->schema([
                        FileUpload::make('image')->label(__('form.main_image'))
                            ->required()
                            ->directory('banners ')
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->imageEditor(),
                    ]),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('form.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('form.description'))
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('form.is_active'))
                    ->searchable()
                    ->sortable(),
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
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('panel.banner');
    }


    public static function getPluralModelLabel(): string
    {
        return __('panel.banners');
    }
}
