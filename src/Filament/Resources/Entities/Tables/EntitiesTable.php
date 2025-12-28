<?php

namespace IFRS\Filament\Resources\Entities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EntitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Entity Name')
                    ->searchable()
                    ->sortable()
                    ->grow(true),
                TextColumn::make('currency.name')
                    ->label('Reporting Currency')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),
                TextColumn::make('currency.currency_code')
                    ->label('Currency Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
                TextColumn::make('locale')
                    ->label('Locale')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                    ->color('info')
                    ->alignCenter(),
                IconColumn::make('multi_currency')
                    ->label('Multi-Currency')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),
                TextColumn::make('year_start')
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
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('parent.name')
                    ->label('Parent Entity')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Top-level')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reportingPeriods.count')
                    ->label('Reporting Periods')
                    ->counts('reportingPeriods')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('users.count')
                    ->label('Users')
                    ->counts('users')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('currency_id')
                    ->label('Currency')
                    ->relationship('currency', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('locale')
                    ->label('Locale')
                    ->options(function () {
                        $locales = config('ifrs.locales');
                        return array_combine($locales, array_map(fn($l) => strtoupper($l), $locales));
                    }),
                SelectFilter::make('multi_currency')
                    ->label('Multi-Currency')
                    ->options([
                        '1' => 'Yes',
                        '0' => 'No',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->defaultSort('name', 'asc');
    }
}
