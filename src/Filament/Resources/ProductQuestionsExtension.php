<?php

namespace Gongarce\ProductFaq\Filament\Resources;

use Gongarce\ProductFaq\Filament\Resources\ProductResource\Pages\ManageProductQuestionsPage;

class ProductQuestionsExtension extends \Lunar\Admin\Support\Extending\ResourceExtension
{

    public function extendPages(array $pages) : array
    {
        return [
            ...$pages,
            'questions' => ManageProductQuestionsPage::route('/{record}/questions'),
        ];
    }

    public function extendSubNavigation(array $nav) : array
    {
        return [
            ...$nav,
            ManageProductQuestionsPage::class,
        ];
    }
}