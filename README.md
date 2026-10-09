# Lunar Product FAQ

Allows to create translatable questions (with answers) and associate them to products.

TODO:
* Associate to collections, brands, etc.

# Requirements

- LunarPHP `~1.4.0`

# Installation

Install via Composer

```
composer require gongarce/lunar-product-faq
```

Then register the plugin in your service provider

```php
use Lunar\Admin\Support\Facades\LunarPanel;
use Gongarce\ProductFaq\ProductFaqPlugin;
// ...

public function register(): void
{
    LunarPanel::panel(function (Panel $panel) {
        return $panel->plugin(new ProductFaqPlugin());
    })->register();
    
    // ...
}
```

# Events

Whenever the FAQ rendered for one or more products may have changed, the plugin dispatches
`Gongarce\ProductFaq\Events\ProductFaqChanged`. It implements `ShouldDispatchAfterCommit`, so it is
never published if the surrounding transaction rolls back.

```php
use Gongarce\ProductFaq\Events\ProductFaqChanged;

Event::listen(function (ProductFaqChanged $event) {
    $event->productIds; // list<int>: unique ids of every affected product
    $event->reason;     // ProductFaqChangeReason
    $event->questionId; // ?int, may point to an already deleted question
});
```

| Reason            | When                                                                   |
|-------------------|------------------------------------------------------------------------|
| `QuestionUpdated` | `text` or `answer` of a question changes                               |
| `QuestionDeleted` | A question is deleted (ids are captured before the cascade)            |
| `ProductAttached` | A question is attached to a product (`attach`, `sync`...)              |
| `ProductDetached` | A question is detached from a product (`detach`, `sync`...)            |
| `PositionChanged` | The pivot `position` changes (e.g. reordering in the panel)            |

Questions apply to base products only. Both sides of the relationship (`Question::products()` and
`Product::questions()`) use the `Gongarce\ProductFaq\Models\Questionable` pivot, so these events are dispatched both from the admin panel and from your own Eloquent code.
Mass updates or deletes through the query builder (e.g. `Question::query()->delete()`) bypass Eloquent
events and therefore don't dispatch it.

# Testing

```
composer test
```
