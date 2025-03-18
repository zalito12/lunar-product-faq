<?php

namespace Gongarce\ProductFaq\Filament\Resources\QuestionResource\Pages;

use Filament\Actions;
use Gongarce\ProductFaq\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Admin\Support\Pages\BaseListRecords;
use Gongarce\ProductFaq\Filament\Resources\QuestionResource;
use Filament\Resources\Components\Tab;

class ListQuestion extends BaseListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getDefaultTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'unassigned' => Tab::make('Unassigned')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDoesntHave('products'))
                ->badge(Question::query()->whereDoesntHave('products')->count()),
            'assigned' => Tab::make('Assigned')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('products')),
        ];
    }
}
