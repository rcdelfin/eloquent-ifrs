<?php

namespace IFRS\Filament\Resources\AccountsResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use IFRS\Filament\Resources\AccountsResource;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
