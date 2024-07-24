<?php

namespace App\Filament\Resources\PlacementTestResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\PlacementTestResource;
use App\Models\PlacementTest;
use Illuminate\Database\Eloquent\Builder;

use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab; 
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Contracts\HasLabel;

class ListPlacementTests extends ListRecords
{
    // use ExposesTableToWidgets;

    protected static string $resource = PlacementTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
{
    return [
        null => Tab::make('All'),
        'Done' => Tab::make()
            ->query(function ($query) {
                $query->where('status', 'Done');
            })
            ->icon('heroicon-m-check-badge')
            ->badgeColor('success')
            ->badge(PlacementTest::query()->where('status', 'Done')->count()),
        'Not yet' => Tab::make()
            ->query(function ($query) {
                $query->where('status', 'Not yet');
            })
            ->icon('heroicon-m-exclamation-triangle')
            ->badgeColor('warning')
            ->badge(PlacementTest::query()->where('status', 'Not yet')->count()),
        'Canceled' => Tab::make()
            ->query(function ($query) {
                $query->where('status', 'Canceled');
            })
            ->icon('heroicon-m-x-circle')
            ->badgeColor('danger')
            ->badge(PlacementTest::query()->where('status', 'Canceled')->count()),
    ];
}
}


