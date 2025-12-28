<?php

namespace IFRS\Filament\Resources\Entities\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use IFRS\Filament\Resources\Entities\EntityResource;

class ViewEntity extends ViewRecord
{
    protected static string $resource = EntityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
