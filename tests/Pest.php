<?php

use Gongarce\ProductFaq\Events\ProductFaqChanged;
use Gongarce\ProductFaq\Events\ProductFaqChangeReason;
use Gongarce\ProductFaq\Models\Question;
use Gongarce\ProductFaq\Tests\PrefixedTestCase;
use Gongarce\ProductFaq\Tests\TestCase;
use Illuminate\Support\Facades\Event;

uses(TestCase::class)->in('Feature');
uses(PrefixedTestCase::class)->in('Prefixed');

function createQuestion(array $attributes = []): Question
{
    return Question::create([
        'text' => ['en' => 'How long does shipping take?'],
        'answer' => ['en' => '<p>Two days.</p>'],
        ...$attributes,
    ]);
}

/**
 * Only fake the plugin event: Eloquent model events must keep running.
 */
function fakeFaqEvents(): void
{
    Event::fake([ProductFaqChanged::class]);
}

/**
 * @return \Illuminate\Support\Collection<int, ProductFaqChanged>
 */
function faqEvents(?ProductFaqChangeReason $reason = null)
{
    return Event::dispatched(ProductFaqChanged::class)
        ->map(fn (array $args) => $args[0])
        ->filter(fn (ProductFaqChanged $event) => $reason === null || $event->reason === $reason)
        ->values();
}

/**
 * Union of the product ids of every dispatched event, optionally filtered by reason.
 *
 * @return list<int>
 */
function faqProductIds(?ProductFaqChangeReason $reason = null): array
{
    return ProductFaqChanged::normalizeIds(
        faqEvents($reason)->flatMap(fn (ProductFaqChanged $event) => $event->productIds)
    );
}
