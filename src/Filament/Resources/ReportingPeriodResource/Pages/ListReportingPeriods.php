<?php

namespace IFRS\Filament\Resources\ReportingPeriodResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use IFRS\Filament\Resources\ReportingPeriodResource;

class ListReportingPeriods extends ListRecords
{
    protected static string $resource = ReportingPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
