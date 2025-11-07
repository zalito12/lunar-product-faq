<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'questions', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'questions', function (Blueprint $table) {
            $table->integer('position')->default(1)->index();
        });
    }
};
