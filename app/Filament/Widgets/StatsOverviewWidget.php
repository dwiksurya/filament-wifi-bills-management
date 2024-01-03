<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Payment;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $startDate = filled($this->filters['startDate'] ?? null) ?
            Carbon::parse($this->filters['startDate']) :
            null;

        $endDate = filled($this->filters['endDate'] ?? null) ?
            Carbon::parse($this->filters['endDate']) :
            now();

        $diffInDays = $startDate ? $startDate->diffInDays($endDate) : 0;

        $monthlyEarnings = Payment::sum('payment_ammount');
        $totalCustomers = Customer::whereStatus(true)->count();

        $formatNumber = function (int $number): string {
            if ($number < 1000) {
                return Number::format($number, 0);
            }

            if ($number < 1000000) {
                return Number::format($number / 1000, 2) . 'k';
            }

            return Number::format($number / 1000000, 2) . 'm';
        };

        return [
            Stat::make('Total Earnings', Number::currency($monthlyEarnings, 'IDR')),
            Stat::make('Monthly Earnings', Number::currency($monthlyEarnings, 'IDR')),
            Stat::make('Total Active Customers', $formatNumber($totalCustomers))
        ];
    }
}
