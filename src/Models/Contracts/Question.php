<?php

namespace Gongarce\ProductFaq\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface Question
{

    /**
     * Return the question's products relationship.
     */
    public function products(): MorphToMany;

    /**
     * Return the question's products relationship.
     */
    public function variants(): MorphToMany;
}
