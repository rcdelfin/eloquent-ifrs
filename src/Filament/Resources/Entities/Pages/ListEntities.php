<?php

namespace IFRS\Filament\Resources\Entities\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use IFRS\Filament\Resources\Entities\EntityResource;

class ListEntities extends ListRecords
{
    protected static string $resource = EntityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
