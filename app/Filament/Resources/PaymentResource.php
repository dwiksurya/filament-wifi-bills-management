<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Payment;
use Filament\Forms\Set;
use App\Models\Customer;
use Filament\Forms\Form;
use App\Models\Collector;
use Filament\Tables\Table;
use App\Models\PaymentType;
use App\Enums\PaymentStatus;
use Illuminate\Support\Number;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PaymentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Filament\Resources\Shop\ProductResource\Widgets\PaymentStats;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Transactions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->options(Customer::whereStatus(true)->pluck('name', 'id'))->searchable()
                    ->required()
                    ->translateLabel()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('payment_ammount', Customer::find($state)->service->price ?? null);
                    }),
                Forms\Components\Select::make('payment_type_id')
                    ->label('Payment Type')
                    ->options(PaymentType::all()->pluck('name', 'id'))->searchable()
                    ->required()
                    ->translateLabel(),
                Forms\Components\TextInput::make('payment_ammount')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->label('Payment Ammount')
                    ->translateLabel()
                    ->readOnly(),
                Forms\Components\DateTimePicker::make('payment_date')
                    ->required()
                    ->label('Payment Date')
                    ->translateLabel()
                    ->default(now()),
                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->translateLabel(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->description(fn (Payment $record): string =>
                    Number::currency($record->customer?->service?->price, 'IDR'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_ammount')
                    ->label('Payment Ammount')
                    ->translateLabel()
                    ->money('IDR')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->translateLabel()
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->translateLabel()
                    ->multiple()
                    ->options(Customer::all()->pluck('name', 'id'))->searchable(),
                Filter::make('payment_date')
                    ->form([
                        Forms\Components\DatePicker::make('startDate')
                            ->label('Start Date')
                            ->translateLabel(),
                        Forms\Components\DatePicker::make('endDate')
                            ->label('End Date')
                            ->translateLabel()
                            ->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['startDate'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '>=', $date),
                            )
                            ->when(
                                $data['endDate'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '<=', $date),
                            );
                    })
            ])->actions([
                Action::make('paid')
                    ->label('Cancel')
                    ->translateLabel()
                    ->requiresConfirmation()
                    ->button()
                    ->action(
                        fn (Payment $record) =>
                        $record->update([
                            'status' => PaymentStatus::CANCELED
                        ])
                    )
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
        ];
    }
}
