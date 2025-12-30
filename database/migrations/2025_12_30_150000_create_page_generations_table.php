<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates the page_generations table for storing per-page LLM generation history.
     * Each page generated gets its own record with prompt and response for:
     * - Better error recovery (can regenerate single page)
     * - Credit learning (track actual vs estimated costs per page type)
     * - Debugging and audit trail
     */
    public function up(): void
    {
        Schema::create('page_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generation_id')->constrained()->onDelete('cascade');
            $table->string('page_name'); // e.g., 'dashboard', 'login', 'custom:inventory'
            $table->string('page_type'); // 'predefined' or 'custom'
            $table->integer('page_index'); // Order in generation (0-based)
            $table->longText('mcp_prompt'); // Full MCP prompt sent to LLM for this page
            $table->longText('llm_response')->nullable(); // Raw response from LLM
            $table->longText('generated_content')->nullable(); // Processed/extracted content
            $table->string('status')->default('pending'); // pending, generating, completed, failed
            $table->text('error_message')->nullable();
            $table->integer('input_tokens')->nullable(); // Tokens in prompt
            $table->integer('output_tokens')->nullable(); // Tokens in response
            $table->integer('processing_time_ms')->nullable(); // Processing time in milliseconds
            $table->decimal('estimated_credits', 10, 4)->nullable(); // Pre-calculated credits
            $table->decimal('actual_credits', 10, 4)->nullable(); // Actual credits used (for learning)
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['generation_id', 'page_index']);
            $table->index('page_name');
            $table->index('page_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_generations');
    }
};
