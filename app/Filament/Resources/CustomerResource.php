<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Zone;
use Filament\Tables;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PaymentType;
use App\Enums\PaymentStatus;
use App\Enums\CustomerStatus;
use Illuminate\Support\Number;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\Layout\Split;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Imports\CustomerImporter;
use App\Filament\Resources\CustomerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CustomerResource\RelationManagers;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Manage';

    public static function getNavigationLabel(): string
    {
        return __('Customers');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->translateLabel(),
                Forms\Components\TextInput::make('phone_number')
                    ->label('Phone Number')
                    ->translateLabel(),
                Forms\Components\Select::make('zone_id')
                    ->label('Zone')
                    ->options(Zone::all()->pluck('name', 'id'))->searchable()
                    ->required()
                    ->translateLabel(),
                Forms\Components\Select::make('service_id')
                    ->label('Service')
                    ->options(Service::all()->pluck('name', 'id'))->searchable()
                    ->required()
                    ->translateLabel(),
                Forms\Components\TextInput::make('address')
                    ->label('Address')
                    ->translateLabel(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        true => 'Active',
                        false => 'Inactive'
                    ])
                    ->searchable()
                    ->required()
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
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Phone Number')
                    ->translateLabel()
                    ->searchable(),
                Tables\Columns\TextColumn::make('zone.name')
                    ->label('Zone')
                    ->translateLabel()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->translateLabel()
                    ->description(fn (Customer $record): string => Number::currency($record->service?->price, 'IDR'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('Active')
                    ->translateLabel()
                    ->icon(fn (string $state): string => match ($state) {
                        '1' => 'heroicon-o-check-circle',
                        '0' => 'heroicon-o-x-circle'
                    })
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'danger'
                    })
                    ->sortable(),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
            ])
            ->recordUrl(function () {
                return null;
            })
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
