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
use IFRS\Models\ReportingPeriod;
use UnitEnum;

class ReportingPeriodResource extends Resource
{
    protected static ?string $model = ReportingPeriod::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'IFRS';

    protected static ?int $navigationSort = 4;

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
                TextColumn::make('calendar_year')->label('Year Period')->sortable(),
                TextColumn::make('period_count')->label('Period Count'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'OPEN' => 'success',
                        'CLOSED' => 'danger',
                        'ADJUSTING' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('academic_terms_count')
                    ->label('Terms')
                    ->state(fn(ReportingPeriod $record): int => $record->academicTerms()->count())
                    ->badge(),
                TextColumn::make('date_range')
                    ->label('Date Range')
                    ->state(function (ReportingPeriod $record): string {
                        $terms = $record->academicTerms;
                        if ($terms->isEmpty()) {
                            return '—';
                        }

                        return (
                            $terms->min('start_date')->format('M j, Y')
                            . ' – '
                            . $terms->max('end_date')->format('M j, Y')
                        );
                    }),
                TextColumn::make('entity.name')->label('Entity'),
            ])
            ->defaultSort('calendar_year', 'desc')
            ->filters([])
            ->headerActions([])
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
            'index' => Pages\ListReportingPeriods::route('/'),
            // 'create' => Pages\CreateReportingPeriod::route('/create'),
            // 'edit' => Pages\EditReportingPeriod::route('/{record}/edit'),
        ];
    }
}
