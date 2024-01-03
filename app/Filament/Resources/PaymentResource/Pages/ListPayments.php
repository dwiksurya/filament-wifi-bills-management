<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use Filament\Actions;
use App\Models\Payment;
use App\Models\PaymentType;
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
        $tabs = [
            null => Tab::make('All')
        ];

        $paymentTypes = PaymentType::all();

        foreach ($paymentTypes as $paymentType) {
            $tab = Tab::make()
                ->badge(Payment::where('payment_type_id', $paymentType->id)->count())
                ->modifyQueryUsing(function (Builder $query) use ($paymentType) {
                    return $query->where('payment_type_id', $paymentType->id);
                });

            $tabs[$paymentType->name] = $tab;
        }

        return $tabs;
    }
}
