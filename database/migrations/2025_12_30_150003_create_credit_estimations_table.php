<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates the credit_estimations table for learning and improving credit predictions.
     * Stores historical data about:
     * - Predicted vs actual credits used
     * - Per-model accuracy tracking
     * - Per-page-type accuracy tracking
     *
     * Used by the credit learning algorithm to improve future estimates.
     */
    public function up(): void
    {
        Schema::create('credit_estimations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generation_id')->constrained()->onDelete('cascade');
            $table->string('model_id'); // LLM model used
            $table->string('page_type'); // 'predefined' or 'custom'
            $table->string('page_name')->nullable(); // For predefined pages
            $table->string('category'); // Template category (admin-dashboard, etc.)
            $table->string('framework'); // CSS framework used
            $table->string('output_format'); // Output format (vue, react, etc.)
            $table->integer('page_count'); // Total pages in generation
            $table->integer('component_count'); // Total components selected
            $table->decimal('estimated_credits', 10, 4); // Credits estimated before generation
            $table->decimal('actual_credits', 10, 4)->nullable(); // Actual credits used (calculated after)
            $table->decimal('error_percentage', 8, 4)->nullable(); // (actual - estimated) / estimated * 100
            $table->integer('estimated_tokens')->nullable(); // Estimated token count
            $table->integer('actual_input_tokens')->nullable(); // Actual input tokens
            $table->integer('actual_output_tokens')->nullable(); // Actual output tokens
            $table->boolean('was_successful')->default(false); // Whether generation succeeded
            $table->timestamps();

            $table->index('model_id');
            $table->index('page_type');
            $table->index('page_name');
            $table->index('category');
            $table->index('framework');
            $table->index('output_format');
            $table->index('was_successful');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_estimations');
    }
};
