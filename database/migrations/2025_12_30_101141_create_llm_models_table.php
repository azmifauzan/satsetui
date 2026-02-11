<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('llm_models', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->decimal('input_price_per_million', 10, 7); // USD per 1M tokens
            $table->decimal('output_price_per_million', 10, 7); // USD per 1M tokens
            $table->integer('estimated_credits_per_generation'); // Pre-calculated credits
            $table->integer('context_length')->default(8192)->comment('Maximum context window size in tokens');
            $table->boolean('is_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_models');
    }
};
