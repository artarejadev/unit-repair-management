<?php

namespace App\Filament\Resources\TripFinancialReports;

use App\Enums\InvoiceStatus;
use App\Enums\UnitStatus;
use App\Models\Invoice;
use App\Models\Trip;
use App\Filament\Resources\TripFinancialReports\Pages\ListTripFinancialReports;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TripFinancialReportResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationLabel = 'Keuangan Trip';

    protected static string | \UnitEnum | null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 20;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('customer')

            ->withCount([
                'units as total_units',

                'units as pending_units' => fn (Builder $query) =>
                    $query->where(
                        'status',
                        UnitStatus::PENDING->value
                    ),

                'units as proses_units' => fn (Builder $query) =>
                    $query->where(
                        'status',
                        UnitStatus::PROSES->value
                    ),

                'units as selesai_units' => fn (Builder $query) =>
                    $query->where(
                        'status',
                        UnitStatus::SELESAI->value
                    ),

                'units as ditagihkan_units' => fn (Builder $query) =>
                    $query->where(
                        'status',
                        UnitStatus::DITAGIHKAN->value
                    ),

                'units as diambil_units' => fn (Builder $query) =>
                    $query->where(
                        'status',
                        UnitStatus::DIAMBIL->value
                    ),
            ])

            ->selectSub(
                Invoice::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('trip_id', 'trips.id')
                    ->where(
                        'status',
                        '!=',
                        InvoiceStatus::CANCELED->value
                    ),
                'active_invoice_count'
            )

            ->selectSub(
                Invoice::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('trip_id', 'trips.id')
                    ->where(
                        'status',
                        InvoiceStatus::CANCELED->value
                    ),
                'canceled_invoice_count'
            )

            ->selectSub(
                Invoice::query()
                    ->selectRaw('COALESCE(SUM(total), 0)')
                    ->whereColumn('trip_id', 'trips.id')
                    ->where(
                        'status',
                        InvoiceStatus::PAID->value
                    ),
                'paid_invoice_total'
            )

            ->selectSub(
                Invoice::query()
                    ->selectRaw('COALESCE(SUM(total), 0)')
                    ->whereColumn('trip_id', 'trips.id')
                    ->where(
                        'status',
                        InvoiceStatus::ISSUED->value
                    ),
                'outstanding_total'
            );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            ->columns([
                Tables\Columns\TextColumn::make('trip_number')
                    ->label('Trip')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_units')
                    ->label('Total Unit')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pending_units')
                    ->label('Pending')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('proses_units')
                    ->label('Proses')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('selesai_units')
                    ->label('Selesai')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ditagihkan_units')
                    ->label('Ditagihkan')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('diambil_units')
                    ->label('Diambil')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('active_invoice_count')
                    ->label('Invoice Aktif')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('canceled_invoice_count')
                    ->label('Invoice Batal')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_invoice_total')
                    ->label('Sudah Dibayar')
                    ->formatStateUsing(
                        fn ($state) =>
                            'Rp ' . number_format(
                                (float) $state,
                                0,
                                ',',
                                '.'
                            )
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('outstanding_total')
                    ->label('Outstanding')
                    ->formatStateUsing(
                        fn ($state) =>
                            'Rp ' . number_format(
                                (float) $state,
                                0,
                                ',',
                                '.'
                            )
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                //
            ])

            ->recordActions([])

            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTripFinancialReports::route('/'),
        ];
    }
}