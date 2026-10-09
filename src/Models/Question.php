<?php

namespace Gongarce\ProductFaq\Models;

use factories\QuestionFactory;
use Gongarce\ProductFaq\Events\ProductFaqChanged;
use Gongarce\ProductFaq\Events\ProductFaqChangeReason;
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

    /**
     * Products affected by this question, captured before deletion because the
     * pivot rows are removed by the database cascade. Not persisted.
     *
     * @var list<int>|null
     */
    protected ?array $productFaqDeletedProductIds = null;

    protected static function booted()
    {
        static::updated(function (self $question) {
            if (! $question->wasChanged(['text', 'answer'])) {
                return;
            }

            ProductFaqChanged::dispatch(
                $question->affectedProductIds(),
                ProductFaqChangeReason::QuestionUpdated,
                $question->getKey(),
            );
        });

        static::deleting(function (self $question) {
            $question->productFaqDeletedProductIds = $question->affectedProductIds();
        });

        static::deleted(function (self $question) {
            $productIds = $question->productFaqDeletedProductIds ?? [];
            $question->productFaqDeletedProductIds = null;

            ProductFaqChanged::dispatch(
                $productIds,
                ProductFaqChangeReason::QuestionDeleted,
                $question->getKey(),
            );
        });
    }

    /**
     * Return the ids of every product whose FAQ includes this question,
     * either directly or through one of its variants.
     *
     * @return list<int>
     */
    public function affectedProductIds(): array
    {
        if (! $this->getKey()) {
            return [];
        }

        return Questionable::resolveProductIds(
            Questionable::recordsForQuestion($this->getKey(), $this->getConnectionName())
        );
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
        return $this->morphedByMany(Product::modelClass(), 'questionable', Questionable::tableName())
            ->using(Questionable::class);
    }

    /**
     * Return the purchasable relationship.
     */
    public function variants(): MorphToMany
    {
        return $this->morphedByMany(ProductVariant::modelClass(), 'questionable', Questionable::tableName())
            ->using(Questionable::class);
    }
}
