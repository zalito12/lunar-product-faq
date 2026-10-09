<?php

use Gongarce\ProductFaq\Events\ProductFaqChangeReason as Reason;
use Lunar\Models\Product;

it('works with a custom lunar table prefix', function () {
    $question = createQuestion();
    $product = Product::factory()->create();
    fakeFaqEvents();

    $product->questions()->attach($question, ['position' => 1]);
    $product->questions()->updateExistingPivot($question->id, ['position' => 2]);
    $question->update(['text' => ['en' => 'New text']]);
    $question->delete();

    $this->assertDatabaseCount('shop_questionable', 0);
    expect($question->getTable())->toBe('shop_questions')
        ->and(faqProductIds(Reason::ProductAttached))->toBe([$product->id])
        ->and(faqProductIds(Reason::PositionChanged))->toBe([$product->id])
        ->and(faqProductIds(Reason::QuestionUpdated))->toBe([$product->id])
        ->and(faqProductIds(Reason::QuestionDeleted))->toBe([$product->id]);
});
