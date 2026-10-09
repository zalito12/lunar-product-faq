<?php

use Filament\Facades\Filament;
use Gongarce\ProductFaq\Events\ProductFaqChanged;
use Gongarce\ProductFaq\Events\ProductFaqChangeReason as Reason;
use Gongarce\ProductFaq\Filament\Resources\ProductResource\Pages\ManageProductQuestionsPage;
use Gongarce\ProductFaq\Filament\Resources\QuestionResource\Pages\EditQuestion;
use Gongarce\ProductFaq\Filament\Resources\QuestionResource;
use Gongarce\ProductFaq\Filament\Resources\QuestionResource\Pages\ListQuestion;
use Gongarce\ProductFaq\Filament\Resources\QuestionResource\RelationManagers\ProductsRelationManager;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Lunar\Models\Product;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('lunar'));
    $this->asStaff();

    $this->product = Product::factory()->create();
    $this->question = createQuestion();
});

describe('product questions page', function () {
    it('attaches a question', function () {
        fakeFaqEvents();

        Livewire::test(ManageProductQuestionsPage::class, ['record' => $this->product->getRouteKey()])
            ->callTableAction('attach', data: ['recordId' => $this->question->id])
            ->assertHasNoTableActionErrors();

        expect($this->product->questions()->pluck('question_id')->all())->toBe([$this->question->id]);
        $event = faqEvents()->sole();
        expect($event->reason)->toBe(Reason::ProductAttached)
            ->and($event->productIds)->toBe([$this->product->id])
            ->and($event->questionId)->toBe($this->question->id);
    });

    it('detaches a question', function () {
        $this->product->questions()->attach($this->question);
        fakeFaqEvents();

        Livewire::test(ManageProductQuestionsPage::class, ['record' => $this->product->getRouteKey()])
            ->callTableAction('detach', $this->question)
            ->assertHasNoTableActionErrors();

        expect($this->product->questions()->count())->toBe(0)
            ->and(faqEvents(Reason::ProductDetached)->sole()->productIds)->toBe([$this->product->id]);
    });

    it('edits a question', function () {
        $this->product->questions()->attach($this->question);
        fakeFaqEvents();

        Livewire::test(ManageProductQuestionsPage::class, ['record' => $this->product->getRouteKey()])
            ->callTableAction('edit', $this->question, data: [
                'text' => ['en' => 'Edited question'],
                'answer' => ['en' => '<p>Edited answer</p>'],
            ])
            ->assertHasNoTableActionErrors();

        expect($this->question->refresh()->translate('text'))->toBe('Edited question')
            ->and(faqEvents(Reason::QuestionUpdated)->sole()->productIds)->toBe([$this->product->id]);
    });

    it('deletes a question', function () {
        $other = Product::factory()->create();
        $this->product->questions()->attach($this->question);
        $other->questions()->attach($this->question);
        fakeFaqEvents();

        Livewire::test(ManageProductQuestionsPage::class, ['record' => $this->product->getRouteKey()])
            ->callTableAction('delete', $this->question)
            ->assertHasNoTableActionErrors();

        $this->assertModelMissing($this->question);
        expect(faqEvents(Reason::QuestionDeleted)->sole()->productIds)->toBe([$this->product->id, $other->id]);
    });

    it('bulk deletes questions', function () {
        $second = createQuestion();
        $this->product->questions()->attach([$this->question->id, $second->id]);
        fakeFaqEvents();

        Livewire::test(ManageProductQuestionsPage::class, ['record' => $this->product->getRouteKey()])
            ->callTableBulkAction('delete', [$this->question, $second])
            ->assertHasNoTableActionErrors();

        expect(faqEvents(Reason::QuestionDeleted))->toHaveCount(2)
            ->and(faqProductIds(Reason::QuestionDeleted))->toBe([$this->product->id]);
    });

    it('reorders questions', function () {
        $second = createQuestion();
        $this->product->questions()->attach($this->question, ['position' => 1]);
        $this->product->questions()->attach($second, ['position' => 2]);
        fakeFaqEvents();

        Livewire::test(ManageProductQuestionsPage::class, ['record' => $this->product->getRouteKey()])
            ->call('reorderTable', [(string) $second->id, (string) $this->question->id]);

        expect($this->product->questions()->orderBy('position')->pluck('question_id')->all())
            ->toBe([$second->id, $this->question->id])
            ->and(faqEvents(Reason::PositionChanged))->toHaveCount(2)
            ->and(faqProductIds(Reason::PositionChanged))->toBe([$this->product->id]);
    });
});

describe('question resource', function () {
    it('updates a question from the edit page', function () {
        $this->product->questions()->attach($this->question);
        fakeFaqEvents();

        Livewire::test(EditQuestion::class, ['record' => $this->question->getRouteKey()])
            ->fillForm(['text' => ['en' => 'Edited question']])
            ->call('save')
            ->assertHasNoFormErrors();

        expect(faqEvents(Reason::QuestionUpdated)->sole()->productIds)->toBe([$this->product->id]);
    });

    it('deletes a question from the edit page', function () {
        $this->product->questions()->attach($this->question);
        fakeFaqEvents();

        Livewire::test(EditQuestion::class, ['record' => $this->question->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($this->question);
        expect(faqEvents(Reason::QuestionDeleted)->sole()->productIds)->toBe([$this->product->id]);
    });

    it('lists questions with their assignment tabs', function () {
        $unassigned = createQuestion();
        $this->product->questions()->attach($this->question);

        Livewire::test(ListQuestion::class)
            ->assertCanSeeTableRecords([$this->question, $unassigned])
            ->set('activeTab', 'unassigned')
            ->assertCanSeeTableRecords([$unassigned])
            ->assertCanNotSeeTableRecords([$this->question])
            ->set('activeTab', 'assigned')
            ->assertCanSeeTableRecords([$this->question])
            ->assertCanNotSeeTableRecords([$unassigned]);
    });

    it('bulk deletes questions from the list', function () {
        $this->product->questions()->attach($this->question);
        fakeFaqEvents();

        Livewire::test(ListQuestion::class)
            ->callTableBulkAction('delete', [$this->question]);

        expect(faqEvents(Reason::QuestionDeleted)->sole()->productIds)->toBe([$this->product->id]);
    });
});

describe('products relation manager', function () {
    it('is the only relation manager of the question resource', function () {
        expect(QuestionResource::getRelations())->toBe([ProductsRelationManager::class]);
    });

    it('attaches a product', function () {
        fakeFaqEvents();

        Livewire::test(ProductsRelationManager::class, ['ownerRecord' => $this->question, 'pageClass' => EditQuestion::class])
            ->callTableAction('attach', data: ['recordId' => $this->product->id])
            ->assertHasNoTableActionErrors();

        expect(faqEvents(Reason::ProductAttached)->sole()->productIds)->toBe([$this->product->id]);
    });

    it('detaches a product', function () {
        $this->question->products()->attach($this->product);
        fakeFaqEvents();

        Livewire::test(ProductsRelationManager::class, ['ownerRecord' => $this->question, 'pageClass' => EditQuestion::class])
            ->callTableAction('detach', $this->product)
            ->assertHasNoTableActionErrors();

        expect(faqEvents(Reason::ProductDetached)->sole()->productIds)->toBe([$this->product->id]);
    });

    it('bulk detaches products', function () {
        $other = Product::factory()->create();
        $this->question->products()->attach([$this->product->id, $other->id]);
        fakeFaqEvents();

        Livewire::test(ProductsRelationManager::class, ['ownerRecord' => $this->question, 'pageClass' => EditQuestion::class])
            ->callTableBulkAction('detach', [$this->product, $other])
            ->assertHasNoTableActionErrors();

        expect($this->question->products()->count())->toBe(0)
            ->and(faqProductIds(Reason::ProductDetached))->toBe([$this->product->id, $other->id]);
    });
});

