<?php

namespace Gongarce\ProductFaq;

use Filament\Panel;
use Gongarce\ProductFaq\Filament\Resources\ProductQuestionsExtension;
use Gongarce\ProductFaq\Filament\Resources\ProductResource\MyProductResourceExtension;
use Gongarce\ProductFaq\Filament\Resources\ProductResource\Pages\ManageProductQuestionsPage;
use Gongarce\ProductFaq\Models\Question;
use Gongarce\ProductFaq\Models\Questionable;
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
            return $product->morphToMany(Question::modelClass(), 'questionable', Questionable::tableName())
                ->using(Questionable::class)
                ->withPivot('position');
        });

        ProductVariant::resolveRelationUsing('questions', function (ProductVariant $variant) {
            return $variant->morphToMany(Question::modelClass(), 'questionable', Questionable::tableName())
                ->using(Questionable::class)
                ->withPivot('position');
        });

        // The questionable_id column is polymorphic, so it can no longer cascade
        // through a foreign key. Keep the old cascade behaviour on hard deletes.
        Product::forceDeleted(function (Product $product) {
            Questionable::deleteFor($product);
        });

        ProductVariant::forceDeleted(function (ProductVariant $variant) {
            Questionable::deleteFor($variant);
        });

        ModelManifest::addDirectory(
            __DIR__.'/Models'
        );

        Relation::morphMap([
            'question' => Question::modelClass(),
        ]);
    }
}
