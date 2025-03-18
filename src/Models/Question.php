<?php

namespace Gongarce\ProductFaq\Models;

use factories\QuestionFactory;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Base\Traits\Searchable;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

/**
 * @property int $id
 * @property string $text translatable text
 * @property string $answer translatable rich text
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class Question extends BaseModel implements Contracts\Question
{
    use HasFactory;
    use HasTranslations;
    use Searchable;

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        //'attribute_data' => AsAttributeData::class,
        'text' => AsCollection::class,
        'answer' => AsCollection::class,
    ];

    protected static function booted()
    {
        /*static::deleting(function (self $shippingMethod) {
            DB::beginTransaction();
            $shippingMethod->customerGroups()->detach();
            $shippingMethod->shippingRates()->delete();
            DB::commit();
        });*/
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return QuestionFactory::new();
    }

    /**
     * Return the purchasable relationship.
     */
    public function products(): MorphToMany
    {
        $prefix = config('lunar.database.table_prefix');
        return $this->morphedByMany(Product::class, 'questionable', "{$prefix}questionable");
    }

    /**
     * Return the purchasable relationship.
     */
    public function variants(): MorphToMany
    {
        $prefix = config('lunar.database.table_prefix');
        return $this->morphedByMany(ProductVariant::class, 'questionable', "{$prefix}questionable");
    }
}
