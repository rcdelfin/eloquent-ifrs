<?php

namespace IFRS\Filament\Resources\Entities\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EntityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Basic Information')
                ->description('Entity identification and reporting configuration')
                ->schema([
                    FileUpload::make('logo')
                        ->label('Logo')
                        ->image()
                        ->maxSize(2048)
                        ->disk('public')
                        ->visibility('public')
                        ->columnSpanFull()
                        ->directory('logos')
                        ->imagePreviewHeight('80')
                        ->loadingIndicatorPosition('left')
                        ->panelAspectRatio('1:1')
                        ->panelLayout('compact')
                        ->removeUploadedFileButtonPosition('right')
                        ->uploadButtonPosition('left')
                        ->uploadProgressIndicatorPosition('left')
                        ->helperText('School logo (max 2MB). Displayed on the login page.'),
                    TextInput::make('name')
                        ->label('Entity Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->placeholder('e.g., ABC School, Inc.')
                        ->helperText('Legal or operational name of the entity'),
                    TextInput::make('subtitle')
                        ->label('Subtitle')
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->placeholder('e.g., Catholic Educational Institution')
                        ->helperText('Optional secondary line shown below the entity name in report headers'),
                    TextInput::make('tin')
                        ->label('TIN')
                        ->maxLength(50)
                        ->placeholder('e.g., 123-456-789-000')
                        ->helperText('Tax identification number'),
                    Textarea::make('address')
                        ->label('Address')
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull()
                        ->placeholder('Registered or business address')
                        ->helperText('Registered or business address of the entity'),
                    Toggle::make('non_vat_registered')
                        ->label('Non-VAT Registration')
                        ->helperText('Show “NON VAT Reg.” on standard receipt headers.')
                        ->default(false),
                    Select::make('currency_id')
                        ->label('Reporting Currency')
                        ->relationship(name: 'currency', titleAttribute: 'name')
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
                ->columns(2)
                ->columnSpan(['lg' => 2]),
            Section::make('Configuration')
                ->description('Multi-currency and entity relationships')
                ->schema([
                    Select::make('parent_id')
                        ->label('Parent Entity')
                        ->relationship(name: 'parent', titleAttribute: 'name')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->placeholder('No parent (top-level entity)')
                        ->helperText('Optional parent entity for hierarchical structures'),
                    Select::make('locale')
                        ->label('Locale')
                        ->options(function () {
                            $locales = config('ifrs.locales');
                            return array_combine($locales, array_map(
                                fn($l) => strtoupper($l) . ' - ' . locale_get_display_name($l),
                                $locales,
                            ));
                        })
                        ->searchable()
                        ->native(false)
                        ->required()
                        ->default(config('ifrs.locales')[0] ?? 'en_PH')
                        ->helperText('Regional formatting for currency and numbers'),
                    TextInput::make('multi_currency')->label('Multi-Currency Mode')->hidden(),
                    Toggle::make('is_default')
                        ->label('Default Entity')
                        ->helperText('Used when no user or domain Entity is assigned.')
                        ->default(false),
                ])
                ->columnSpan(['lg' => 1]),
        ])->columns(3);
    }
}
