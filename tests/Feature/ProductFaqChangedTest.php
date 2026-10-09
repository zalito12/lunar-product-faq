<?php

use Gongarce\ProductFaq\Events\ProductFaqChanged;
use Gongarce\ProductFaq\Events\ProductFaqChangeReason as Reason;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Lunar\Models\Product;

beforeEach(function () {
    $this->question = createQuestion();
    $this->productA = Product::factory()->create();
    $this->productB = Product::factory()->create();
});

describe('question content', function () {
    it('dispatches QuestionUpdated with every directly associated product when the text changes', function () {
        $this->question->products()->attach([$this->productA->id, $this->productB->id]);
        fakeFaqEvents();

        $this->question->update(['text' => ['en' => 'New text']]);

        Event::assertDispatchedTimes(ProductFaqChanged::class, 1);
        $event = faqEvents()->first();
        expect($event->reason)->toBe(Reason::QuestionUpdated)
            ->and($event->questionId)->toBe($this->question->id)
            ->and($event->productIds)->toBe([$this->productA->id, $this->productB->id]);
    });

    it('dispatches QuestionUpdated when the answer changes', function () {
        $this->question->products()->attach($this->productA);
        fakeFaqEvents();

        $this->question->update(['answer' => ['en' => '<p>Three days.</p>']]);

        expect(faqProductIds(Reason::QuestionUpdated))->toBe([$this->productA->id]);
    });

    it('does not dispatch when nothing rendered has changed', function () {
        $this->question->products()->attach($this->productA);
        fakeFaqEvents();

        $this->question->save();
        $this->question->update(['text' => $this->question->text]);
        $this->question->touch();

        Event::assertNotDispatched(ProductFaqChanged::class);
    });

    it('dispatches an empty but valid event for a question without associations', function () {
        fakeFaqEvents();

        $this->question->update(['text' => ['en' => 'New text']]);

        expect(faqEvents())->toHaveCount(1)
            ->and(faqEvents()->first()->productIds)->toBe([]);
    });
});

describe('associations', function () {
    it('dispatches ProductAttached when attaching from the product side', function () {
        fakeFaqEvents();

        $this->productA->questions()->attach($this->question, ['position' => 1]);

        Event::assertDispatchedTimes(ProductFaqChanged::class, 1);
        $event = faqEvents()->first();
        expect($event->reason)->toBe(Reason::ProductAttached)
            ->and($event->questionId)->toBe($this->question->id)
            ->and($event->productIds)->toBe([$this->productA->id]);
    });

    it('dispatches the same contract when attaching from the question side', function () {
        fakeFaqEvents();

        $this->question->products()->attach($this->productA);

        $event = faqEvents()->sole();
        expect($event->reason)->toBe(Reason::ProductAttached)
            ->and($event->questionId)->toBe($this->question->id)
            ->and($event->productIds)->toBe([$this->productA->id]);
    });

    it('dispatches ProductDetached from both sides', function () {
        $this->question->products()->attach([$this->productA->id, $this->productB->id]);
        fakeFaqEvents();

        $this->productA->questions()->detach($this->question);
        $this->question->products()->detach($this->productB);

        expect(faqEvents(Reason::ProductDetached))->toHaveCount(2)
            ->and(faqProductIds(Reason::ProductDetached))->toBe([$this->productA->id, $this->productB->id]);
    });

    it('dispatches ProductDetached when detaching every association at once', function () {
        $this->question->products()->attach([$this->productA->id, $this->productB->id]);
        fakeFaqEvents();

        $this->question->products()->detach();

        expect(faqProductIds(Reason::ProductDetached))->toBe([$this->productA->id, $this->productB->id]);
        $this->assertDatabaseCount('lunar_questionable', 0);
    });

    it('covers added and removed associations on sync', function () {
        $this->question->products()->attach($this->productA);
        fakeFaqEvents();

        $this->question->products()->sync([$this->productB->id]);

        expect(faqProductIds(Reason::ProductDetached))->toBe([$this->productA->id])
            ->and(faqProductIds(Reason::ProductAttached))->toBe([$this->productB->id]);
    });

    it('covers sync from the product side', function () {
        $other = createQuestion();
        $this->productA->questions()->attach($this->question);
        fakeFaqEvents();

        $this->productA->questions()->sync([$other->id]);

        expect(faqEvents(Reason::ProductDetached)->sole()->questionId)->toBe($this->question->id)
            ->and(faqEvents(Reason::ProductAttached)->sole()->questionId)->toBe($other->id)
            ->and(faqProductIds())->toBe([$this->productA->id]);
    });
});

describe('position', function () {
    it('dispatches PositionChanged when the pivot position changes', function () {
        $this->productA->questions()->attach($this->question, ['position' => 1]);
        fakeFaqEvents();

        $this->productA->questions()->updateExistingPivot($this->question->id, ['position' => 2]);

        $event = faqEvents()->sole();
        expect($event->reason)->toBe(Reason::PositionChanged)
            ->and($event->questionId)->toBe($this->question->id)
            ->and($event->productIds)->toBe([$this->productA->id]);
    });

    it('dispatches PositionChanged when updating a loaded pivot', function () {
        $this->productA->questions()->attach($this->question, ['position' => 1]);
        fakeFaqEvents();

        $this->productA->questions()->first()->pivot->update(['position' => 5]);

        expect(faqEvents(Reason::PositionChanged))->toHaveCount(1)
            ->and(faqProductIds())->toBe([$this->productA->id]);
        $this->assertDatabaseHas('lunar_questionable', ['question_id' => $this->question->id, 'position' => 5]);
    });

    it('does not dispatch when the position is unchanged', function () {
        $this->productA->questions()->attach($this->question, ['position' => 1]);
        fakeFaqEvents();

        $this->productA->questions()->updateExistingPivot($this->question->id, ['position' => 1]);

        Event::assertNotDispatched(ProductFaqChanged::class);
    });
});

describe('deletion', function () {
    it('keeps every affected product id although the pivots are removed by cascade', function () {
        $this->question->products()->attach([$this->productA->id, $this->productB->id]);
        $questionId = $this->question->id;
        fakeFaqEvents();

        $this->question->delete();

        $this->assertDatabaseCount('lunar_questionable', 0);
        $event = faqEvents()->sole();
        expect($event->reason)->toBe(Reason::QuestionDeleted)
            ->and($event->questionId)->toBe($questionId)
            ->and($event->productIds)->toBe(
                [$this->productA->id, $this->productB->id]
            );
    });

    it('dispatches an empty event when deleting a question without associations', function () {
        fakeFaqEvents();

        $this->question->delete();

        expect(faqEvents(Reason::QuestionDeleted)->sole()->productIds)->toBe([]);
    });
});

describe('payload', function () {
    it('normalizes ids to unique integers without nulls', function () {
        $event = new ProductFaqChanged(['3', 1, null, 3, '', 2], Reason::ProductAttached);

        expect($event->productIds)->toBe([1, 2, 3])
            ->and($event->questionId)->toBeNull();
    });

    it('is dispatched after commit', function () {
        expect(new ProductFaqChanged([], Reason::ProductAttached))
            ->toBeInstanceOf(\Illuminate\Contracts\Events\ShouldDispatchAfterCommit::class);
    });
});

describe('transactions', function () {
    it('does not publish anything when the transaction rolls back', function () {
        $received = [];
        Event::listen(ProductFaqChanged::class, function (ProductFaqChanged $event) use (&$received) {
            $received[] = $event;
        });

        try {
            DB::transaction(function () {
                $this->question->products()->attach($this->productA);
                $this->question->update(['text' => ['en' => 'New text']]);
                $this->question->delete();

                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException) {
        }

        expect($received)->toBe([]);
        $this->assertDatabaseHas('lunar_questions', ['id' => $this->question->id]);
    });

    it('publishes only once the transaction commits', function () {
        $received = [];
        Event::listen(ProductFaqChanged::class, function (ProductFaqChanged $event) use (&$received) {
            $received[] = $event;
        });

        DB::transaction(function () use (&$received) {
            $this->question->products()->attach($this->productA);

            expect($received)->toBe([]);
        });

        expect($received)->toHaveCount(1)
            ->and($received[0]->productIds)->toBe([$this->productA->id]);
    });
});
