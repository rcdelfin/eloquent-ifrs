<?php

namespace IFRS\Filament\Resources\CostCenterResource\Pages;

use Filament\Resources\Pages\ListRecords;
use IFRS\Filament\Resources\CostCenterResource;

class ListCostCenters extends ListRecords
{
    protected static string $resource = CostCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
