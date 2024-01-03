<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use Filament\Actions;
use App\Models\Customer;
use App\Enums\PaymentStatus;
use Filament\Actions\ImportAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Imports\CustomerImporter;
use App\Filament\Resources\CustomerResource;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(CustomerImporter::class),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $countPaidCustomers = Customer::wherehas('payments', function ($q) {
            return $q->where('status', PaymentStatus::PAID)
                ->whereBetween('payment_date', [
                    date('Y-m-01', strtotime(now())),
                    date('Y-m-t', strtotime(now()))
                ]);
        });

        $countNotPaidCustomers = Customer::whereDoesntHave('payments', function ($q) {
            return $q->where('status', PaymentStatus::PAID)
                ->whereBetween('payment_date', [
                    date('Y-m-01', strtotime(now())),
                    date('Y-m-t', strtotime(now()))
                ]);
        });


        return [
            null   => Tab::make('All'),
            'Paid' => Tab::make()
                ->badge($countPaidCustomers->count())
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->wherehas('payments', function ($q) {
                        return $q->where('status', PaymentStatus::PAID)
                            ->whereBetween('payment_date', [
                                date('Y-m-01', strtotime(now())),
                                date('Y-m-t', strtotime(now()))
                            ]);
                    });
                }),
            'Not Paid Yet' => Tab::make()
                ->badge($countNotPaidCustomers->count())
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereDoesntHave('payments', function ($q) {
                        return $q->where('status', PaymentStatus::PAID)
                            ->whereBetween('payment_date', [
                                date('Y-m-01', strtotime(now())),
                                date('Y-m-t', strtotime(now()))
                            ]);
                    });
                })
        ];
    }
}
