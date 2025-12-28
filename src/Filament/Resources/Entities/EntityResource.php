<?php

namespace IFRS\Filament\Resources\Entities;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use IFRS\Filament\Resources\Entities\Pages\CreateEntity;
use IFRS\Filament\Resources\Entities\Pages\EditEntity;
use IFRS\Filament\Resources\Entities\Pages\ListEntities;
use IFRS\Filament\Resources\Entities\Pages\ViewEntity;
use IFRS\Filament\Resources\Entities\Schemas\EntityForm;
use IFRS\Filament\Resources\Entities\Schemas\EntityInfolist;
use IFRS\Filament\Resources\Entities\Tables\EntitiesTable;
use IFRS\Models\Entity;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class EntityResource extends Resource
{
    protected static null|string $model = Entity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static null|string $navigationLabel = 'Entities';

    protected static null|string $modelLabel = 'Entity';

    protected static string|UnitEnum|null $navigationGroup = 'IFRS';

    protected static null|int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return EntityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EntityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EntitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEntities::route('/'),
            'create' => CreateEntity::route('/create'),
            'view' => ViewEntity::route('/{record}'),
            'edit' => EditEntity::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view-any-entity') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create-entity') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update-entity') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete-entity') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete-entity') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view-entity') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['currency', 'parent'])
            ->withCount(['reportingPeriods', 'users'])
            ->addSelect(['daughters_count' => function ($query) {
                $query
                    ->from('ifrs_entities as children')
                    ->selectRaw('count(*)')
                    ->whereColumn('children.parent_id', 'ifrs_entities.id')
                    ->whereNull('children.deleted_at');
            }]);
    }
}
