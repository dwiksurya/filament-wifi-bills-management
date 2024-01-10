<?php

namespace App\Filament\Resources\PaymentHistoryResource\Pages;

use Filament\Actions;
use App\Models\Payment;
use App\Models\PaymentType;
use Filament\Tables\Columns\Column;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use App\Filament\Resources\PaymentHistoryResource;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;

class ListPaymentHistories extends ListRecords
{
    protected static string $resource = PaymentHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
            ->exports([
                ExcelExport::make()
                    ->fromTable()
                    ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                    ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
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
