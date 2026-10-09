<?php

namespace Gongarce\ProductFaq\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched (after commit) whenever the FAQ rendered for one or more products may have changed.
 */
final readonly class ProductFaqChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    /**
     * @var list<int>
     */
    public array $productIds;

    /**
     * @param  iterable<int|string|null>  $productIds
     */
    public function __construct(
        iterable $productIds,
        public ProductFaqChangeReason $reason,
        public ?int $questionId = null,
    ) {
        $this->productIds = static::normalizeIds($productIds);
    }

    /**
     * @param  iterable<int|string|null>  $ids
     * @return list<int>
     */
    public static function normalizeIds(iterable $ids): array
    {
        $normalized = [];

        foreach ($ids as $id) {
            if ($id === null || $id === '') {
                continue;
            }

            $normalized[(int) $id] = (int) $id;
        }

        sort($normalized);

        return array_values($normalized);
    }
}
