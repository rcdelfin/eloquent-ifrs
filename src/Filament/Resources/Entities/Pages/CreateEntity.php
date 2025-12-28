<?php

namespace IFRS\Filament\Resources\Entities\Pages;

use Filament\Resources\Pages\CreateRecord;
use IFRS\Filament\Resources\Entities\EntityResource;

class CreateEntity extends CreateRecord
{
    protected static string $resource = EntityResource::class;
}
