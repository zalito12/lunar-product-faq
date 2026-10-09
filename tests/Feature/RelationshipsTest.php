<?php

use Gongarce\ProductFaq\Models\Question;
use Gongarce\ProductFaq\Models\Questionable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

it('uses the plugin pivot from every side of the relationship', function () {
    $question = createQuestion();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create();

    expect($question->products())->toBeInstanceOf(MorphToMany::class)
        ->and($question->products()->getPivotClass())->toBe(Questionable::class)
        ->and($question->variants())->toBeInstanceOf(MorphToMany::class)
        ->and($question->variants()->getPivotClass())->toBe(Questionable::class)
        ->and($product->questions())->toBeInstanceOf(MorphToMany::class)
        ->and($product->questions()->getPivotClass())->toBe(Questionable::class)
        ->and($variant->questions())->toBeInstanceOf(MorphToMany::class)
        ->and($variant->questions()->getPivotClass())->toBe(Questionable::class);
});

it('keeps storing associations in the questionable table', function () {
    $question = createQuestion();
    $product = Product::factory()->create();

    $product->questions()->attach($question, ['position' => 3]);

    $this->assertDatabaseHas('lunar_questionable', [
        'question_id' => $question->id,
        'questionable_id' => $product->id,
        'questionable_type' => $product->getMorphClass(),
        'position' => 3,
    ]);

    expect($question->products()->pluck($product->getQualifiedKeyName())->all())->toBe([$product->id])
        ->and($product->questions()->first())->toBeInstanceOf(Question::class)
        ->and($product->questions()->first()->pivot)->toBeInstanceOf(Questionable::class)
        ->and((int) $product->questions()->first()->pivot->position)->toBe(3);
});

it('can attach questions to variants', function () {
    $question = createQuestion();
    $variant = ProductVariant::factory()->create();

    $question->variants()->attach($variant);

    expect($variant->questions()->pluck($question->getQualifiedKeyName())->all())->toBe([$question->id])
        ->and($question->variants()->first()->is($variant))->toBeTrue();
});

it('removes associations when a product or variant is force deleted', function () {
    $question = createQuestion();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create();
    $question->products()->attach($product);
    $question->variants()->attach($variant);

    $variant->forceDelete();
    $product->forceDelete();

    $this->assertDatabaseCount('lunar_questionable', 0);
});

it('keeps associations when a product is soft deleted', function () {
    $question = createQuestion();
    $product = Product::factory()->create();
    $question->products()->attach($product);

    $product->delete();

    $this->assertDatabaseCount('lunar_questionable', 1);
});
