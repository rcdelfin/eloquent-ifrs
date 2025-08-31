<?php

namespace IFRS\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use IFRS\Filament\Resources\AccountsResource\Pages\ListAccounts;
use IFRS\Filament\Resources\AccountsResource\Pages\CreateAccounts;
use IFRS\Filament\Resources\AccountsResource\Pages\EditAccounts;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use IFRS\Filament\Resources\AccountsResource\Pages;
use IFRS\Models\Account;

class AccountsResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('Name'),
                TextInput::make('code')
                    ->required()
                    ->label('Code'),
                Select::make('account_type')
                    ->options(fn() => collect(Account::TYPES)->mapWithKeys(fn($type) => [$type => ucfirst(strtolower(str_replace('_', ' ', $type)))]))
                    ->required()
                    ->label('Type'),
                Select::make('cost_center_id')
                    ->relationship('costCenter', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('Cost Center'),
                Textarea::make('description')
                    ->columnSpanFull()
                    ->label('Description'),
                TextInput::make('balance')
                    ->required()
                    ->label('Balance'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->label('ID'),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Name'),
                TextColumn::make('code')
                    ->sortable()
                    ->searchable()
                    ->label('Code'),
                TextColumn::make('category.name')
                    ->sortable()
                    ->searchable()
                    ->label('Category'),
                TextColumn::make('costCenter.name')
                    ->sortable()
                    ->searchable()
                    ->label('Cost Center'),
                TextColumn::make('balance')
                    ->sortable()
                    ->searchable()
                    ->label('Balance'),
            ])
            ->groups([
                Group::make('category.name')
                    ->label('Category')
                    ->collapsible(),
                Group::make('costCenter.name')
                    ->label('Cost Center')
                    ->collapsible(),
                Group::make('account_type')
                    ->label('Type')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn($record) => ucfirst(strtolower(str_replace('_', ' ', $record->account_type)))),
            ])
            ->defaultGroup('account_type')
            ->defaultSort('code', 'asc')
            ->filters([

            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccounts::route('/'),
            'create' => CreateAccounts::route('/create'),
            'edit' => EditAccounts::route('/{record}/edit'),
        ];
    }
}
