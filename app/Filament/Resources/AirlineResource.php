<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AirlineResource\Pages;
use App\Models\Airline;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AirlineResource extends Resource
{
    protected static ?string $model = Airline::class;
    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $navigationLabel = 'Maskapai';
    protected static ?string $modelLabel = 'Maskapai';
    protected static ?string $pluralModelLabel = 'Maskapai';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Kode Maskapai')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(3),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Maskapai')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('logo_url')
                    ->label('URL Logo')
                    ->url(),
                Forms\Components\TextInput::make('headquarters_city')
                    ->label('Kota Kantor Pusat')
                    ->maxLength(100),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('headquarters_city')
                    ->label('Kota')
                    ->searchable(),
                Tables\Columns\BooleanColumn::make('is_active')
                    ->label('Aktif'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAirlines::route('/'),
            'create' => Pages\CreateAirline::route('/create'),
            'edit' => Pages\EditAirline::route('/{record}/edit'),
        ];
    }
}
