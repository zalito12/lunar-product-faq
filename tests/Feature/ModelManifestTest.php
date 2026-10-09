<?php

use Gongarce\ProductFaq\Events\ProductFaqChanged;
use Gongarce\ProductFaq\Events\ProductFaqChangeReason as Reason;
use Gongarce\ProductFaq\Filament\Resources\QuestionResource;
use Gongarce\ProductFaq\Models\Contracts\Question as QuestionContract;
use Gongarce\ProductFaq\Models\Question;
use Gongarce\ProductFaq\Tests\Fixtures\CustomQuestion;
use Illuminate\Support\Facades\Event;
use Lunar\Facades\ModelManifest;
use Lunar\Models\Product;

beforeEach(function () {
    ModelManifest::replace(QuestionContract::class, CustomQuestion::class);
});

it('resolves the replaced question model everywhere', function () {
    $question = createQuestion();
    $product = Product::factory()->create();
    $product->questions()->attach($question);

    expect(Question::modelClass())->toBe(CustomQuestion::class)
        ->and(QuestionResource::getModel())->toBe(CustomQuestion::class)
        ->and(Question::query()->first())->toBeInstanceOf(CustomQuestion::class)
        ->and($product->questions()->first())->toBeInstanceOf(CustomQuestion::class);
});

it('dispatches each event once for the replaced model', function () {
    $product = Product::factory()->create();
    $question = CustomQuestion::create([
        'text' => ['en' => 'Question'],
        'answer' => ['en' => 'Answer'],
    ]);
    // Booting the base class too must not register duplicated listeners.
    new Question;
    $product->questions()->attach($question);
    fakeFaqEvents();

    $question->update(['text' => ['en' => 'New text']]);
    $question->delete();

    Event::assertDispatchedTimes(ProductFaqChanged::class, 2);
    expect(faqProductIds(Reason::QuestionUpdated))->toBe([$product->id])
        ->and(faqProductIds(Reason::QuestionDeleted))->toBe([$product->id]);
});
