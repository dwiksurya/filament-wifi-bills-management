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
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PaymentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Filament\Resources\Shop\ProductResource\Widgets\PaymentStats;

class PaymentResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $title = "Rekening";

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Transactions';

    public static function getNavigationLabel(): string
    {
        return __('Payments');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->translateLabel()
                    ->options(Customer::whereStatus(true)->pluck('name', 'id'))->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('payment_ammount', Customer::find($state)->service->price ?? null);
                    }),
                Forms\Components\Select::make('payment_type_id')
                    ->label('Payment Type')
                    ->translateLabel()
                    ->options(PaymentType::all()->pluck('name', 'id'))->searchable()
                    ->required(),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->translateLabel()
                    ->description(fn (Customer $record): string => $record->zone?->name)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Phone Number')
                    ->translateLabel()
                    ->searchable()
                    ->visibleFrom('md'),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->translateLabel()
                    ->description(fn (Customer $record): string => Number::currency($record->service?->price, 'IDR'))
                    ->sortable()
                    ->searchable()
                    ->visibleFrom('md'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->alignment('center')
                    ->label('Status Pembayaran')
                    ->translateLabel()
                    ->getStateUsing(
                        fn (Customer $record): string =>
                        $record->isPaid() ? __('Paid') : __('Not Paid Yet')
                    )
                    ->icon(fn (Customer $record): string => match ($record->isPaid()) {
                        true => 'heroicon-o-check-circle',
                        false => 'heroicon-o-x-circle'
                    })
                    ->color(fn (Customer $record): string => match ($record->isPaid()) {
                        true => 'success',
                        false => 'danger'
                    })->visibleFrom('md'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('make_it_paid')
                    ->disabled(fn (Customer $record): string => $record->isPaid())
                    ->button()
                    ->form([
                        Select::make('payment_type_id')
                            ->label('Payment Type')
                            ->translateLabel()
                            ->options(PaymentType::query()->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->color(
                        fn (Customer $record): string =>
                        $record->isPaid() ? 'success' : 'primary'
                    )
                    ->outlined(
                        fn (Customer $record): string => $record->isPaid()
                    )
                    ->label(
                        fn (Customer $record): string =>
                        $record->isPaid() ? __('Paid') : __('Make it paid')
                    )
                    ->action(function (array $data, Customer $record): void {

                        if (!$record->isPaid()) {
                            Payment::create([
                                'payment_type_id' => $data['payment_type_id'],
                                'customer_id' => $record->id,
                                'payment_ammount' => $record->service->price,
                                'payment_date' => now(),
                                'status' => PaymentStatus::PAID
                            ]);
                        }

                    })
            ])
            ->recordUrl(function () {
                return null;
            });
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
