<?php

namespace Gongarce\ProductFaq\Models;

use Gongarce\ProductFaq\Events\ProductFaqChanged;
use Gongarce\ProductFaq\Events\ProductFaqChangeReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

/**
 * Pivot between questions and their questionable models (products, variants).
 *
 * Every relationship able to write the questionable table uses this pivot, so
 * attach, detach, sync and pivot updates all end up dispatching ProductFaqChanged.
 *
 * @property int $question_id
 * @property int $questionable_id
 * @property string $questionable_type
 * @property int $position
 */
class Questionable extends MorphPivot
{
    protected static function booted(): void
    {
        static::created(function (self $pivot) {
            $pivot->dispatchProductFaqChanged(ProductFaqChangeReason::ProductAttached);
        });

        static::deleted(function (self $pivot) {
            $pivot->dispatchProductFaqChanged(ProductFaqChangeReason::ProductDetached);
        });

        static::updated(function (self $pivot) {
            if ($pivot->wasChanged('position')) {
                $pivot->dispatchProductFaqChanged(ProductFaqChangeReason::PositionChanged);
            }
        });
    }

    public function getTable()
    {
        return $this->table ?? static::tableName();
    }

    public static function tableName(): string
    {
        return config('lunar.database.table_prefix').'questionable';
    }

    /**
     * Resolve the products whose FAQ depends on the given questionable records.
     *
     * @param  iterable<array{questionable_type: string, questionable_id: int|string}|object>  $records
     * @return list<int>
     */
    public static function resolveProductIds(iterable $records): array
    {
        $productIds = [];
        $variantIds = [];

        foreach ($records as $record) {
            $record = (array) $record;
            $class = Relation::getMorphedModel($record['questionable_type']) ?? $record['questionable_type'];

            if (is_a($class, Product::class, true)) {
                $productIds[] = $record['questionable_id'];
            } elseif (is_a($class, ProductVariant::class, true)) {
                $variantIds[] = $record['questionable_id'];
            }
        }

        if (! empty($variantIds)) {
            $variantClass = ProductVariant::modelClass();

            $productIds = [
                ...$productIds,
                ...$variantClass::withTrashed()
                    ->whereIn((new $variantClass)->getKeyName(), array_unique($variantIds))
                    ->pluck('product_id')
                    ->all(),
            ];
        }

        return ProductFaqChanged::normalizeIds($productIds);
    }

    /**
     * Resolve the products affected by this pivot record.
     *
     * @return list<int>
     */
    public function affectedProductIds(): array
    {
        return static::resolveProductIds([[
            'questionable_type' => $this->getAttribute($this->morphType ?? 'questionable_type') ?? $this->morphClass,
            'questionable_id' => $this->getAttribute('questionable_id'),
        ]]);
    }

    protected function dispatchProductFaqChanged(ProductFaqChangeReason $reason): void
    {
        $questionId = $this->getAttribute('question_id');

        ProductFaqChanged::dispatch(
            $this->affectedProductIds(),
            $reason,
            $questionId === null ? null : (int) $questionId,
        );
    }

    /**
     * Remove every pivot row of a questionable model without dispatching events.
     */
    public static function deleteFor(Model $questionable): void
    {
        (new static)
            ->setConnection($questionable->getConnectionName())
            ->newQuery()
            ->toBase()
            ->where('questionable_type', $questionable->getMorphClass())
            ->where('questionable_id', $questionable->getKey())
            ->delete();
    }

    /**
     * @return Collection<int, object{questionable_type: string, questionable_id: int}>
     */
    public static function recordsForQuestion(int $questionId, ?string $connection = null): Collection
    {
        return (new static)
            ->setConnection($connection)
            ->newQuery()
            ->toBase()
            ->where('question_id', $questionId)
            ->get(['questionable_type', 'questionable_id']);
    }
}
