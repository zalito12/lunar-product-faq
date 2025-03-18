<?php

namespace Gongarce\ProductFaq;

use Filament\Panel;
use Gongarce\ProductFaq\Filament\Resources\ProductQuestionsExtension;
use Gongarce\ProductFaq\Filament\Resources\ProductResource\MyProductResourceExtension;
use Gongarce\ProductFaq\Filament\Resources\ProductResource\Pages\ManageProductQuestionsPage;
use Gongarce\ProductFaq\Models\Question;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Facades\ModelManifest;
use Gongarce\ProductFaq\Models\ShippingExclusion;
use Gongarce\ProductFaq\Models\ShippingExclusionList;
use Gongarce\ProductFaq\Models\ShippingRate;
use Gongarce\ProductFaq\Models\ShippingZone;
use Gongarce\ProductFaq\Models\ShippingZonePostcode;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class ProductFaqServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/product-faq.php', 'lunar.product-faq');
    }

    public function boot()
    {
        if (! config('lunar.product-faq.enabled')) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lunarpanel.product-faq');

        if (! config('lunar.database.disable_migrations', false)) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'product-faq');

        Product::resolveRelationUsing('questions', function (Product $product) {
            $prefix = config('lunar.database.table_prefix');
            return $product->morphToMany(Question::class, 'questionable', "{$prefix}questionable")
                ->withPivot('position');
        });

        /*ProductVariant::resolveRelationUsing('questions', function (ProductVariant $product) {
            $prefix = config('lunar.database.table_prefix');
            return $product->morphToMany(Question::class, 'questionable', "{$prefix}questionable")
                ->withPivot('position');
        });*/

        ModelManifest::addDirectory(
            __DIR__.'/Models'
        );

        Relation::morphMap([
            'question' => Question::modelClass(),
        ]);
    }
}
