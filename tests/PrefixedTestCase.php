<?php

namespace Gongarce\ProductFaq\Tests;

abstract class PrefixedTestCase extends TestCase
{
    protected function tablePrefix(): string
    {
        return 'shop_';
    }
}
