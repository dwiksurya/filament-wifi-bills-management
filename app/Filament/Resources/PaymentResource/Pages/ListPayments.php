<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use Filament\Actions;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\PaymentType;
use App\Enums\PaymentStatus;
use Filament\Resources\Components\Tab;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PaymentResource;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;
    protected static ?string $title = 'Pembayaran';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ExportAction::make()
            ->exports([
                ExcelExport::make()
                    ->fromTable()
                    ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                    ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                    ->withColumns([
                        Column::make('updated_at'),
                    ])
            ]),
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
