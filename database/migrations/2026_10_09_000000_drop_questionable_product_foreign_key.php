<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

/**
 * questionable_id is polymorphic (products and variants), so it can't reference
 * the products table. Replace the foreign key with a regular morph index.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'questionable', function (Blueprint $table) {
            if ($this->canDropForeignKeys()) {
                $table->dropForeign(['questionable_id']);
            }
            $table->index(['questionable_type', 'questionable_id']);
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'questionable', function (Blueprint $table) {
            $table->dropIndex(['questionable_type', 'questionable_id']);
            if ($this->canDropForeignKeys()) {
                $table->foreign('questionable_id')
                    ->references('id')
                    ->on($this->prefix.'products')
                    ->onDelete('cascade');
            }
        });
    }
};
