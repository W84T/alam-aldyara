<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\App;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(OrderResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')->label(__("form.customer"))->searchable(),
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
            ->actions([
                Action::make('View Order')
                    ->url(fn(Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->color('info')
                    ->icon('heroicon-o-eye'),
            ]);
    }
}
