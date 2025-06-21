<?php

namespace IFRS\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Name'),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->label('Code'),
                Forms\Components\Select::make('account_type')
                    ->options(fn() => collect(Account::TYPES)->mapWithKeys(fn($type) => [$type => ucfirst(strtolower(str_replace('_', ' ', $type)))]))
                    ->required()
                    ->label('Type'),
                Forms\Components\Select::make('cost_center_id')
                    ->relationship('costCenter', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('Cost Center'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->label('Description'),
                Forms\Components\TextInput::make('balance')
                    ->required()
                    ->label('Balance'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->label('ID'),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Name'),
                Tables\Columns\TextColumn::make('code')
                    ->sortable()
                    ->searchable()
                    ->label('Code'),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->searchable()
                    ->label('Category'),
                Tables\Columns\TextColumn::make('costCenter.name')
                    ->sortable()
                    ->searchable()
                    ->label('Cost Center'),
                Tables\Columns\TextColumn::make('balance')
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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccounts::route('/create'),
            'edit' => Pages\EditAccounts::route('/{record}/edit'),
        ];
    }
}
