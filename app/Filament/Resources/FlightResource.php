<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlightResource\Pages;
use App\Models\Flight;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FlightResource extends Resource
{
    protected static ?string $model = Flight::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Jadwal Penerbangan';
    protected static ?string $modelLabel = 'Jadwal Penerbangan';
    protected static ?string $pluralModelLabel = 'Jadwal Penerbangan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('flight_number')
                    ->label('Nomor Penerbangan')
                    ->required(),
                Forms\Components\Select::make('route_id')
                    ->label('Rute')
                    ->relationship('route', 'id')
                    ->required(),
                Forms\Components\Select::make('aircraft_id')
                    ->label('Pesawat')
                    ->relationship('aircraft', 'registration_number')
                    ->required(),
                Forms\Components\DatePicker::make('departure_date')
                    ->label('Tanggal Keberangkatan')
                    ->required(),
                Forms\Components\TimePicker::make('departure_time')
                    ->label('Jam Keberangkatan')
                    ->required(),
                Forms\Components\TimePicker::make('arrival_time')
                    ->label('Jam Tiba')
                    ->required(),
                Forms\Components\TextInput::make('base_price')
                    ->label('Harga Dasar')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('tax_surcharge')
                    ->label('Surcharge Pajak')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('fuel_surcharge')
                    ->label('Surcharge Bahan Bakar')
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Terjadwal',
                        'boarding' => 'Boarding',
                        'in_flight' => 'Terbang',
                        'landed' => 'Mendarat',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('scheduled'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('flight_number')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('route.flight_number_prefix')
                    ->label('Maskapai'),
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('departure_time')
                    ->label('Berangkat'),
                Tables\Columns\TextColumn::make('arrival_time')
                    ->label('Tiba'),
                Tables\Columns\TextColumn::make('base_price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'scheduled',
                        'info' => 'boarding',
                        'warning' => 'in_flight',
                        'success' => 'landed',
                        'danger' => 'cancelled',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
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
            'index' => Pages\ListFlights::route('/'),
            'create' => Pages\CreateFlight::route('/create'),
            'edit' => Pages\EditFlight::route('/{record}/edit'),
        ];
    }
}
