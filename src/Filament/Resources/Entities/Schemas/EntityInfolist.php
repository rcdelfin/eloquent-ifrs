<?php

namespace IFRS\Filament\Resources\Entities\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EntityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic Information')
                ->description('Entity identification and reporting configuration')
                ->schema([
                    TextEntry::make('name')->label('Entity Name'),
                    TextEntry::make('currency.name')
                        ->label('Reporting Currency')
                        ->badge()
                        ->color('primary'),
                    TextEntry::make('currency.currency_code')
                        ->label('Currency Code')
                        ->badge()
                        ->color('success'),
                    TextEntry::make('locale')
                        ->label('Locale')
                        ->badge()
                        ->formatStateUsing(fn(string $state): string => strtoupper($state))
                        ->color('info'),
                    IconEntry::make('multi_currency')
                        ->label('Multi-Currency')
                        ->boolean()
                        ->alignCenter(),
                ])
                ->columns(3),
            Section::make('Configuration')
                ->description('Multi-currency and entity relationships')
                ->schema([
                    TextEntry::make('parent.name')
                        ->label('Parent Entity')
                        ->placeholder('Top-level entity')
                        ->badge()
                        ->color('gray'),
                    TextEntry::make('year_start')
                        ->label('Fiscal Year Start')
                        ->formatStateUsing(fn(int $state): string => match ($state) {
                            1 => 'January',
                            2 => 'February',
                            3 => 'March',
                            4 => 'April',
                            5 => 'May',
                            6 => 'June',
                            7 => 'July',
                            8 => 'August',
                            9 => 'September',
                            10 => 'October',
                            11 => 'November',
                            12 => 'December',
                        }),
                ])
                ->columns(2),
            Section::make('Statistics')
                ->description('Entity usage and relationships')
                ->schema([
                    TextEntry::make('reportingPeriods.count')
                        ->label('Reporting Periods')
                        ->counts('reportingPeriods')
                        ->badge()
                        ->color('gray'),
                    TextEntry::make('users.count')
                        ->label('Associated Users')
                        ->counts('users')
                        ->badge()
                        ->color('info'),
                    TextEntry::make('daughters_count')
                        ->label('Sub-Entities')
                        ->default(0)
                        ->badge()
                        ->color('warning'),
                    TextEntry::make('created_at')
                        ->label('Created')
                        ->dateTime('M j, Y')
                        ->badge()
                        ->color('gray'),
                ])
                ->columns(4),
        ]);
    }
}
