<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Restructure llm_models table to support exactly 3 fixed model types
     * with configurable providers (Gemini or OpenAI)
     */
    public function up(): void
    {
        Schema::dropIfExists('llm_models');
        
        Schema::create('llm_models', function (Blueprint $table) {
            $table->id();
            $table->enum('model_type', ['fast', 'professional', 'expert'])
                ->unique()
                ->comment('Fixed model type: fast, professional, or expert');
            $table->enum('provider', ['gemini', 'openai'])
                ->default('gemini')
                ->comment('LLM provider: gemini or openai');
            $table->string('model_name')
                ->comment('Actual model identifier (e.g., gemini-2.5-flash, gpt-4)');
            $table->text('api_key')
                ->comment('Encrypted API key for the provider');
            $table->text('base_url')
                ->nullable()
                ->comment('Encrypted custom base URL (optional)');
            $table->integer('base_credits')
                ->default(1)
                ->comment('Base credit cost per generation');
            $table->boolean('is_active')
                ->default(true)
                ->comment('Whether this model type is available for users');
            $table->timestamps();
            
            // Index for quick lookups
            $table->index(['model_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_models');
        
        // Recreate old structure
        Schema::create('llm_models', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->decimal('input_price_per_million', 10, 7);
            $table->decimal('output_price_per_million', 10, 7);
            $table->integer('estimated_credits_per_generation');
            $table->integer('context_length')->default(8192);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
};
