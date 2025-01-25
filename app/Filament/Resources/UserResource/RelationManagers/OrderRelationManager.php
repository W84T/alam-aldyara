<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\OrderResource;
use App\Models\Order;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\App;

class OrderRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')->label(__("form.order_id"))->searchable(),
                TextColumn::make('grand_total')
                    ->label(__('form.grand_total'))
                    ->money('USD')
                    ->formatStateUsing(fn($state) => App::getLocale() === 'ar' ? $state . ' دولار' : '$' . $state)
                    ->size(TextColumn\TextColumnSize::Medium),

                TextColumn::make('status')
                    ->label(__("form.status"))
                    ->searchable()
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => __("status.{$state}"))
                    ->color(fn(string $state): string => match ($state) {
                        'new' => 'info',
                        'processing' => 'warning',
                        'shipped' => 'success',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'new' => 'heroicon-m-sparkles',
                        'processing' => 'heroicon-m-arrow-path',
                        'shipping' => 'heroicon-m-truck',
                        'delivered' => 'heroicon-m-check-badge',
                        'cancelled' => 'heroicon-m-x-circle',
                    }),

                TextColumn::make('payment_method')
                    ->label(__('form.payment_method'))
                    ->formatStateUsing(fn(string $state): string => __("form.{$state}"))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label(__('form.payment_status'))
                    ->formatStateUsing(fn(string $state): string => __("status.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'paid' => 'success',
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Action::make('View Order')
                    ->url(fn(Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->color('info')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
