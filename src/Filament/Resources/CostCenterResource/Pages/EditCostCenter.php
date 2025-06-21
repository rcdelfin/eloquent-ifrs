<?php

namespace IFRS\Filament\Resources\CostCenterResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use IFRS\Filament\Resources\CostCenterResource;

class EditCostCenter extends EditRecord
{
    protected static string $resource = CostCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
