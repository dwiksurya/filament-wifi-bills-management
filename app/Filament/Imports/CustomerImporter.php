<?php

namespace App\Filament\Imports;

use App\Models\Zone;
use App\Models\Service;
use App\Models\Customer;
use Filament\Forms\Components\Select;
use Filament\Actions\Imports\Importer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class CustomerImporter extends Importer
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->rules(['required', 'max:255']),
            ImportColumn::make('phone_number')
                ->rules(['max:255']),
            ImportColumn::make('address'),
            ImportColumn::make('status')
                ->boolean()
                ->rules(['required', 'boolean']),
        ];
    }

    public function resolveRecord(): ?Customer
    {
        return new Customer([
            'name' => $this->data['name'],
            'phone_number' => $this->data['phone_number'],
            'address' => $this->data['address'],
            'status' => $this->data['status'],
            'service_id' => $this->options['service_id'],
            'zone_id' => $this->options['zone_id']
        ]);
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('zone_id')
                    ->label('Zone')
                    ->options(Zone::all()->pluck('name', 'id'))->searchable()
                    ->required()
                    ->translateLabel(),
            Select::make('service_id')
                    ->label('Service')
                    ->options(Service::all()->pluck('name', 'id'))->searchable()
                    ->required()
                    ->translateLabel(),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your customer import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
