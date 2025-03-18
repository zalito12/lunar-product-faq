<?php

namespace Gongarce\ProductFaq;

use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\Support\Facades\FilamentIcon;
use Gongarce\ProductFaq\Filament\Resources\ProductQuestionsExtension;
use Gongarce\ProductFaq\Filament\Resources\ProductResource\MyProductResourceExtension;
use Gongarce\ProductFaq\Filament\Resources\ProductResource\Pages\MyPage;
use Gongarce\ProductFaq\Filament\Resources\ProductResource\Pages\MyRelationExtension;
use Gongarce\ProductFaq\Filament\Resources\ShippingExclusionListResource;
use Gongarce\ProductFaq\Filament\Resources\QuestionResource;
use Gongarce\ProductFaq\Filament\Resources\ShippingZoneResource;
use Lunar\Admin\Support\Facades\LunarPanel;

class ProductFaqPlugin implements Plugin
{
    public function getId(): string
    {
        return 'product-faq';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }

    public function register(Panel $panel): void
    {
        if (! config('lunar.product-faq.enabled')) {
            return;
        }

        $panel->resources([
            QuestionResource::class,
        ]);

        LunarPanel::extensions([
            \Lunar\Admin\Filament\Resources\ProductResource::class => ProductQuestionsExtension::class,
        ]);

        FilamentIcon::register([
            'lunar::product-faq' => 'lucide-circle-help',
        ]);
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel;
    }

    // ...
}
