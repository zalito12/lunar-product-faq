<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'questionable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained($this->prefix.'questions')->onDelete('cascade');
            $table->foreignId('questionable_id')->constrained($this->prefix.'products')->onDelete('cascade');
            $table->string('questionable_type');
            $table->integer('position')->default(1)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'questionable');
    }
};
