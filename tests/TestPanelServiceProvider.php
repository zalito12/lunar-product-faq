<?php

namespace Gongarce\ProductFaq\Tests;

use Gongarce\ProductFaq\ProductFaqPlugin;
use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Support\Facades\LunarPanel;

class TestPanelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        LunarPanel::panel(fn ($panel) => $panel->plugin(new ProductFaqPlugin))->register();
    }
}
