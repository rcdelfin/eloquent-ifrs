<?php

namespace IFRS\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use IFRS\Filament\Resources\CostCenterResource\Pages\ListCostCenters;
use IFRS\Filament\Resources\CostCenterResource\Pages\CreateCostCenter;
use IFRS\Filament\Resources\CostCenterResource\Pages\EditCostCenter;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use IFRS\Filament\Resources\CostCenterResource\Pages;
use IFRS\Models\CostCenter;
use IFRS\Models\Entity;

class CostCenterResource extends Resource
{
    protected static ?string $model = CostCenter::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $label = 'Cost Center';

    protected static ?string $pluralLabel = 'Cost Centers';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->label('Code'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Name'),
                Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(3)
                    ->label('Description'),

                Select::make('entity_id')
                    ->options(fn() => Entity::query()->pluck('name', 'id'))
                    ->default(fn($record) => $record?->entity_id ?? auth()->user()?->entity_id)
                    ->required()
                    ->label('Entity'),

                Toggle::make('active')
                    ->default(true)
                    ->label('Active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->sortable()
                    ->searchable()
                    ->label('Code'),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Name'),
                TextColumn::make('description')
                    ->limit(50)
                    ->label('Description'),
                TextColumn::make('entity.name')
                    ->sortable()
                    ->label('Entity'),
                IconColumn::make('active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->label('Active'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Created'),
            ])
            ->filters([
                TernaryFilter::make('active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCostCenters::route('/'),
            'create' => CreateCostCenter::route('/create'),
            'edit' => EditCostCenter::route('/{record}/edit'),
        ];
    }
}
