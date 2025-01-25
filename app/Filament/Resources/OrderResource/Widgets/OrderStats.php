<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('panel.new_orders'), Order::query()->where('status', 'new')->count()),
            Stat::make(__('panel.orders_in_process'), Order::query()->where('status', 'processing')->count()),
            Stat::make(__('panel.orders_shipped'), Order::query()->where('status', 'shipped')->count()),
            Stat::make(__('panel.income'), Number::currency(Order::query()->sum('grand_total'), 'USD')),
        ];
    }
}
