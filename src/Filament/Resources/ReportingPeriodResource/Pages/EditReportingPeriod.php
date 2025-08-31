<?php

namespace IFRS\Filament\Resources\ReportingPeriodResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use IFRS\Filament\Resources\ReportingPeriodResource;

class EditReportingPeriod extends EditRecord
{
    protected static string $resource = ReportingPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
