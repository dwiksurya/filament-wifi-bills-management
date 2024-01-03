<?php

namespace App\Filament\Resources\Shop\ProductResource\Widgets;

use App\Filament\Resources\PaymentResource\Pages\ListPayments;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListPayments::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Product Inventory', $this->getPageTableQuery()->sum('qty')),
            Stat::make('Average price', number_format($this->getPageTableQuery()->avg('price'), 2)),
        ];
    }
}
