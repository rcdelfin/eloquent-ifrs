<?php

namespace IFRS\Filament\Resources\Entities\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EntityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic Information')
                ->description('Entity identification and reporting configuration')
                ->schema([
                    TextInput::make('name')
                        ->label('Entity Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g., ABC School, Inc.')
                        ->helperText('Legal or operational name of the entity'),
                    Select::make('currency_id')
                        ->label('Reporting Currency')
                        ->relationship(
                            name: 'currency',
                            titleAttribute: 'name',
                        )
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->required()
                        ->helperText('Primary currency for financial reporting'),
                    TextInput::make('year_start')
                        ->label('Fiscal Year Start Month')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(12)
                        ->default(1)
                        ->helperText('Month number (1-12) when fiscal year begins'),
                ])
                ->columns(3),
            Section::make('Configuration')
                ->description('Multi-currency and entity relationships')
                ->schema([
                    Select::make('parent_id')
                        ->label('Parent Entity')
                        ->relationship(
                            name: 'parent',
                            titleAttribute: 'name',
                        )
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->placeholder('No parent (top-level entity)')
                        ->helperText('Optional parent entity for hierarchical structures'),
                    Select::make('locale')
                        ->label('Locale')
                        ->options(function () {
                            $locales = config('ifrs.locales');
                            return array_combine(
                                $locales,
                                array_map(fn($l) => strtoupper($l) . ' - ' . locale_get_display_name($l), $locales),
                            );
                        })
                        ->searchable()
                        ->native(false)
                        ->required()
                        ->default(config('ifrs.locales')[0] ?? 'en_PH')
                        ->helperText('Regional formatting for currency and numbers'),
                    TextInput::make('multi_currency')->label('Multi-Currency Mode')->hidden(),
                ])
                ->columns(3),
        ]);
    }
}
