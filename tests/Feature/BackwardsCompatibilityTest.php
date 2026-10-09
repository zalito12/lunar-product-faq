<?php

use Filament\Facades\Filament;
use Gongarce\ProductFaq\Filament\Resources\ProductResource\Pages\ManageProductQuestionsPage;
use Gongarce\ProductFaq\Filament\Resources\QuestionResource\Pages\EditQuestion;
use Gongarce\ProductFaq\Filament\Resources\QuestionResource\RelationManagers\ProductsRelationManager;
use Gongarce\ProductFaq\Models\Question;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Livewire\Livewire;
use Lunar\Models\Product;

/*
 * Behaviour that existed before ProductFaqChanged was introduced and must keep working.
 */

it('exposes the same public relationships', function () {
    $question = createQuestion();
    $product = Product::factory()->create();

    expect($question->products())->toBeInstanceOf(MorphToMany::class)
        ->and($product->questions())->toBeInstanceOf(MorphToMany::class)
        ->and($product->questions()->getTable())->toBe('lunar_questionable')
        ->and($product->questions()->getPivotColumns())->toContain('position');
});

it('stores translated text and answer', function () {
    $question = createQuestion();

    expect($question->refresh()->translate('text'))->toBe('How long does shipping take?')
        ->and($question->translate('answer'))->toBe('<p>Two days.</p>');
});

it('associates questions to products from both sides', function () {
    $question = createQuestion();
    $product = Product::factory()->create();

    $product->questions()->attach($question, ['position' => 2]);

    expect($question->products()->first()->is($product))->toBeTrue()
        ->and($product->questions()->first()->is($question))->toBeTrue()
        ->and((int) $product->questions()->first()->pivot->position)->toBe(2);

    $question->products()->detach($product);

    expect($product->questions()->count())->toBe(0);
});

it('removes associations when a question is deleted', function () {
    $question = createQuestion();
    Product::factory()->create()->questions()->attach($question);

    $question->delete();

    $this->assertDatabaseCount('lunar_questionable', 0);
});

it('orders product questions by pivot position', function () {
    $first = createQuestion();
    $second = createQuestion();
    $product = Product::factory()->create();
    $product->questions()->attach($first, ['position' => 2]);
    $product->questions()->attach($second, ['position' => 1]);

    expect($product->questions()->orderBy('position')->pluck('question_id')->all())->toBe([$second->id, $first->id]);
});

it('keeps the Filament pages working', function () {
    Filament::setCurrentPanel(Filament::getPanel('lunar'));
    $this->asStaff();
    $question = createQuestion();
    $second = createQuestion();
    $product = Product::factory()->create();

    Livewire::test(ManageProductQuestionsPage::class, ['record' => $product->getRouteKey()])
        ->callTableAction('attach', data: ['recordId' => $question->id])
        ->callTableAction('attach', data: ['recordId' => $second->id])
        ->assertCanSeeTableRecords([$question, $second])
        ->call('reorderTable', [(string) $second->id, (string) $question->id])
        ->callTableAction('detach', $question)
        ->assertHasNoTableActionErrors();

    expect($product->questions()->pluck('question_id')->all())->toBe([$second->id]);

    Livewire::test(ProductsRelationManager::class, ['ownerRecord' => $second, 'pageClass' => EditQuestion::class])
        ->assertCanSeeTableRecords([$product])
        ->callTableBulkAction('detach', [$product])
        ->assertHasNoTableActionErrors();

    expect(Question::query()->whereHas('products')->count())->toBe(0);
});
