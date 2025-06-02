<?php

namespace IFRS\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use IFRS\Filament\Resources\ReportingPeriodResource\Pages;
use IFRS\Models\ReportingPeriod;

class ReportingPeriodResource extends Resource
{
    protected static ?string $model = ReportingPeriod::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('calendar_year')
                ->required()
                ->numeric()
                ->label('Year Period'),
            Forms\Components\TextInput::make('period_count')
                ->required()
                ->numeric()
                ->label('Period Count'),
            Forms\Components\Select::make('status')->options([
                'OPEN' => 'Open',
                'CLOSED' => 'Closed',
                'ADJUSTING' => 'Adjusting',
            ]),
            Forms\Components\Select::make('entity_id')
                ->relationship('entity', 'name')
                ->required()
                ->label('Entity'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('calendar_year')->label(
                    'Year Period'
                ),
                Tables\Columns\TextColumn::make('period_count')->label(
                    'Period Count'
                ),
                Tables\Columns\TextColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('entity.name')->label('Entity'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->filters([])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
