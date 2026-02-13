<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Change LLM model types from 3 (fast/professional/expert) to 2 (satset/expert)
     */
    public function up(): void
    {
        // Remove the enum constraint first, then re-add
        // SQLite doesn't support ALTER COLUMN, so we recreate
        Schema::dropIfExists('llm_models');

        Schema::create('llm_models', function (Blueprint $table) {
            $table->id();
            $table->enum('model_type', ['satset', 'expert'])
                ->unique()
                ->comment('Fixed model type: satset (fast) or expert (high quality)');
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

            $table->index(['model_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_models');

        Schema::create('llm_models', function (Blueprint $table) {
            $table->id();
            $table->enum('model_type', ['fast', 'professional', 'expert'])
                ->unique();
            $table->enum('provider', ['gemini', 'openai'])->default('gemini');
            $table->string('model_name');
            $table->text('api_key');
            $table->text('base_url')->nullable();
            $table->integer('base_credits')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['model_type', 'is_active']);
        });
    }
};
