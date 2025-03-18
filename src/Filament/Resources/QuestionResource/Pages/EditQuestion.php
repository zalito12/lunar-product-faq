<?php

namespace Gongarce\ProductFaq\Filament\Resources\QuestionResource\Pages;

use Filament\Actions;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Gongarce\ProductFaq\Filament\Resources\QuestionResource;

class EditQuestion extends BaseEditRecord
{
    protected static string $resource = QuestionResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
