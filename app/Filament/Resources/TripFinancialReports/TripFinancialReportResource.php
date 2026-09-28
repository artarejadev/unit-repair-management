<?php

namespace App\Filament\Resources\TripFinancialReports;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Unit;
use App\Models\UnitRepair;
use App\Models\UnitSparepart;
use App\Models\Trip;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TripFinancialReportResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Keuangan Trip';

    protected static ?string $modelLabel = 'Keuangan Trip';

    protected static ?string $pluralModelLabel = 'Keuangan Trip';

    protected static string | \UnitEnum | null $navigationGroup = 'Laporan';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('trip_number')
                    ->label('Trip')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_units')
                    ->label('Jumlah Unit')
                    ->state(function (Trip $record): int {
                        return Unit::query()
                            ->where('trip_id', $record->id)
                            ->count();
                    })
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_repair')
                    ->label('Total Repair')
                    ->state(function (Trip $record): float {
                        return (float) UnitRepair::query()
                            ->whereHas('unit', function ($query) use ($record) {
                                $query->where('trip_id', $record->id);
                            })
                            ->selectRaw(
                                'COALESCE(SUM(COALESCE(override_price, price)), 0) as total'
                            )
                            ->value('total');
                    })
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('total_sparepart')
                    ->label('Total Sparepart')
                    ->state(function (Trip $record): float {
                        $unitIds = Unit::query()
                            ->where('trip_id', $record->id)
                            ->pluck('id');

                        if ($unitIds->isEmpty()) {
                            return 0;
                        }

                        $spareparts = UnitSparepart::query()
                            ->whereIn('unit_id', $unitIds)
                            ->get();

                        $total = 0.0;

                        foreach ($spareparts as $sparepart) {
                            $attributes = $sparepart->getAttributes();

                            /*
                             * Prioritaskan subtotal jika memang tersedia.
                             */
                            if (array_key_exists('subtotal', $attributes)) {
                                $total += (float) $sparepart->subtotal;
                                continue;
                            }

                            $quantity = array_key_exists('quantity', $attributes)
                                ? (float) $sparepart->quantity
                                : 1.0;

                            /*
                             * Coba beberapa kemungkinan nama field
                             * harga transaksi sparepart.
                             */
                            if (array_key_exists('unit_price', $attributes)) {
                                $total += $quantity * (float) $sparepart->unit_price;
                                continue;
                            }

                            if (array_key_exists('price', $attributes)) {
                                $total += $quantity * (float) $sparepart->price;
                                continue;
                            }

                            /*
                             * Fallback jika UnitSparepart tidak menyimpan
                             * harga transaksi: gunakan harga Master Sparepart.
                             */
                            if (
                                isset($sparepart->sparepart) &&
                                isset($sparepart->sparepart->price)
                            ) {
                                $total += $quantity * (float) $sparepart->sparepart->price;
                            }
                        }

                        return $total;
                    })
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('total_invoice')
                    ->label('Total Invoice')
                    ->state(function (Trip $record): float {
                        return (float) Invoice::query()
                            ->where('trip_id', $record->id)
                            ->where(
                                'status',
                                '!=',
                                InvoiceStatus::CANCELED->value
                            )
                            ->sum('total');
                    })
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('billing_status')
                    ->label('Status Billing')
                    ->state(function (Trip $record): string {
                        $units = Unit::query()
                            ->where('trip_id', $record->id)
                            ->get([
                                'id',
                                'status',
                            ]);

                        if ($units->isEmpty()) {
                            return 'BELUM ADA UNIT';
                        }

                        $billableUnits = Unit::query()
                            ->where('trip_id', $record->id)
                            ->where('status', 'SELESAI')
                            ->whereDoesntHave(
                                'invoiceUnits.invoice',
                                function ($query) {
                                    $query->where(
                                        'status',
                                        '!=',
                                        InvoiceStatus::CANCELED->value
                                    );
                                }
                            )
                            ->count();

                        $billedUnits = Unit::query()
                            ->where('trip_id', $record->id)
                            ->whereIn('status', [
                                'DITAGIHKAN',
                                'DIAMBIL',
                            ])
                            ->count();

                        $completedUnits = $units
                            ->where('status', 'SELESAI')
                            ->count();

                        if ($billableUnits > 0 && $billedUnits > 0) {
                            return 'SEBAGIAN DITAGIHKAN';
                        }

                        if ($billableUnits > 0) {
                            return 'BELUM DITAGIHKAN';
                        }

                        if ($billedUnits > 0) {
                            return 'SUDAH DITAGIHKAN';
                        }

                        if ($completedUnits === 0) {
                            return 'BELUM SIAP DITAGIHKAN';
                        }

                        return 'BELUM DITAGIHKAN';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'SUDAH DITAGIHKAN' => 'success',
                        'SEBAGIAN DITAGIHKAN' => 'warning',
                        'BELUM DITAGIHKAN' => 'danger',
                        'BELUM SIAP DITAGIHKAN' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTripFinancialReports::route('/'),
        ];
    }
}