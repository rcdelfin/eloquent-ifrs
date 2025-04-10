<?php

namespace IFRS\Filament\Resources\AccountsResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use IFRS\Filament\Resources\AccountsResource;

class EditAccounts extends EditRecord
{
    protected static string $resource = AccountsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
