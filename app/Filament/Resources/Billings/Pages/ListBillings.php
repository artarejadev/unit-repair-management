<?php

namespace App\Filament\Resources\Billings\Pages;

use App\Filament\Resources\Billings\BillingResource;
use App\Models\Trip;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBillings extends ListRecords
{
    protected static string $resource = BillingResource::class;

    protected function getTableQuery(): Builder
    {
        return Trip::query()
            ->with('customer')
            ->withCount('billingUnits');
    }
}