<?php

namespace IFRS\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use IFRS\Filament\Resources\ReportingPeriodResource\Pages;
use IFRS\Filament\Resources\ReportingPeriodResource\Pages\ListReportingPeriods;
use IFRS\Models\ReportingPeriod;

class ReportingPeriodResource extends Resource
{
    protected static ?string $model = ReportingPeriod::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('calendar_year')
                ->required()
                ->numeric()
                ->label('Year Period'),
            TextInput::make('period_count')
                ->required()
                ->numeric()
                ->label('Period Count'),
            Select::make('status')->options([
                'OPEN' => 'Open',
                'CLOSED' => 'Closed',
                'ADJUSTING' => 'Adjusting',
            ]),
            Select::make('entity_id')
                ->relationship('entity', 'name')
                ->required()
                ->label('Entity'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('calendar_year')->label(
                    'Year Period',
                ),
                TextColumn::make('period_count')->label(
                    'Period Count',
                ),
                TextColumn::make('status')->label('Status'),
                TextColumn::make('entity.name')->label('Entity'),
            ])
            ->filters([

            ])
            ->headerActions([])
            ->filters([])
            ->actions([EditAction::make()])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReportingPeriods::route('/'),
            // 'create' => Pages\CreateReportingPeriod::route('/create'),
            // 'edit' => Pages\EditReportingPeriod::route('/{record}/edit'),
        ];
    }
}
