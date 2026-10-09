<?php

use Illuminate\Support\Facades\Schema;

it('replaces the products foreign key with a morph index and can be rolled back', function () {
    $migration = require __DIR__.'/../../database/migrations/2026_10_09_000000_drop_questionable_product_foreign_key.php';
    $foreignColumns = fn () => collect(Schema::getForeignKeys('lunar_questionable'))->pluck('columns')->flatten()->all();
    $indexes = fn () => collect(Schema::getIndexes('lunar_questionable'))->pluck('columns')->all();

    expect($foreignColumns())->toBe(['question_id'])
        ->and($indexes())->toContain(['questionable_type', 'questionable_id']);

    $migration->down();

    expect($foreignColumns())->toEqualCanonicalizing(['question_id', 'questionable_id'])
        ->and($indexes())->not->toContain(['questionable_type', 'questionable_id']);

    $migration->up();

    expect($foreignColumns())->toBe(['question_id']);
});
