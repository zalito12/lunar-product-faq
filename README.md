# Lunar Product FAQ

Allows to create translatable questions (with answers) and associate them to products.

TODO:
* Tests.
* Associate to variants, collections, brands, etc.

# Requirements

- LunarPHP Admin `>` `1.x`

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