<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates the custom_page_statistics table for tracking custom page usage.
     * Used for:
     * - Admin visibility into what custom pages users are creating
     * - Identifying popular custom pages to potentially add as predefined options
     * - Understanding user needs and feature requests
     */
    public function up(): void
    {
        Schema::create('custom_page_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('normalized_name'); // Lowercase, trimmed, standardized version
            $table->string('original_name'); // First seen original name
            $table->text('sample_description')->nullable(); // Representative description
            $table->unsignedInteger('usage_count')->default(1); // How many times this page was requested
            $table->unsignedInteger('success_count')->default(0); // Successful generations
            $table->unsignedInteger('failure_count')->default(0); // Failed generations
            $table->decimal('avg_generation_time_ms', 10, 2)->nullable(); // Average time to generate
            $table->decimal('avg_tokens_used', 10, 2)->nullable(); // Average tokens consumed
            $table->boolean('promoted_to_predefined')->default(false); // If added to wizard options
            $table->timestamp('promoted_at')->nullable();
            $table->timestamp('first_used_at');
            $table->timestamp('last_used_at');
            $table->timestamps();

            $table->unique('normalized_name');
            $table->index('usage_count');
            $table->index('promoted_to_predefined');
            $table->index('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_page_statistics');
    }
};
