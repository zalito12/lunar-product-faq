<?php

use Gongarce\ProductFaq\Events\ProductFaqChangeReason as Reason;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

it('works with a custom lunar table prefix', function () {
    $question = createQuestion();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create();
    fakeFaqEvents();

    $product->questions()->attach($question, ['position' => 1]);
    $question->variants()->attach($variant);
    $product->questions()->updateExistingPivot($question->id, ['position' => 2]);
    $question->update(['text' => ['en' => 'New text']]);
    $question->delete();

    $this->assertDatabaseCount('shop_questionable', 0);
    expect($question->getTable())->toBe('shop_questions')
        ->and(faqProductIds(Reason::ProductAttached))->toBe(collect([$product->id, $variant->product_id])->sort()->values()->all())
        ->and(faqProductIds(Reason::PositionChanged))->toBe([$product->id])
        ->and(faqProductIds(Reason::QuestionUpdated))->toBe(faqProductIds(Reason::ProductAttached))
        ->and(faqProductIds(Reason::QuestionDeleted))->toBe(faqProductIds(Reason::ProductAttached));
});
