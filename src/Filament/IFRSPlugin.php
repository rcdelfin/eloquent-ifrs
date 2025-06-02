<?php

namespace IFRS\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use IFRS\Filament\Resources\AccountsResource;
use IFRS\Filament\Resources\ReportingPeriodResource;

class IFRSPlugin implements Plugin
{
    public function getId(): string
    {
        return 'ifrs';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                AccountsResource::class,
                ReportingPeriodResource::class,
            ])
            ->pages([
                // Settings::class,
            ]);
    }

    public function boot(Panel $panel): void {}
}
