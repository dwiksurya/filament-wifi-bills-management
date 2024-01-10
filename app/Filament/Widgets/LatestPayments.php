<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PaymentHistoryResource;
use Filament\Tables;
use App\Models\Payment;
use App\Models\Shop\Order;
use Filament\Tables\Table;
use Squire\Models\Currency;
use Illuminate\Support\Number;
use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\Shop\OrderResource;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPayments extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(PaymentHistoryResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('payment_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->translateLabel()
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer Name')
                    ->translateLabel()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('paymentType.name')
                    ->label('Payment Type')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('customer.service.name')
                    ->label('Service')
                    ->translateLabel()
                    ->description(fn (Payment $record): string =>
                        Number::currency($record->customer?->service?->price, 'IDR'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->translateLabel()
            ]);
    }
}
