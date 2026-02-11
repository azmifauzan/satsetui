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
        Schema::create('generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->string('model_used'); // gemini-flash, claude-sonnet, etc
            $table->string('output_format')->nullable();
            $table->string('framework')->nullable();
            $table->string('category')->nullable();
            $table->integer('credits_used');
            
            // Credit breakdown fields
            $table->decimal('base_credits', 10, 4)->default(0);
            $table->decimal('extra_page_credits', 10, 4)->default(0);
            $table->decimal('extra_component_credits', 10, 4)->default(0);
            $table->decimal('subtotal_credits', 10, 4)->default(0);
            $table->decimal('error_margin', 8, 4)->default(0.10);
            $table->decimal('profit_margin', 8, 4)->default(0.05);
            $table->decimal('error_margin_amount', 10, 4)->default(0);
            $table->decimal('profit_margin_amount', 10, 4)->default(0);
            
            $table->string('status'); // pending, processing, completed, failed
            $table->longText('mcp_prompt')->nullable(); // MCP prompt used
            $table->json('blueprint_json')->nullable(); // Complete wizard state for regeneration/audit
            $table->text('generated_content')->nullable();
            
            // Progress tracking fields
            $table->json('progress_data')->nullable();
            $table->integer('current_page_index')->default(0);
            $table->integer('total_pages')->default(1);
            $table->string('current_status')->default('pending'); // pending, generating, completed, failed
            
            $table->text('error_message')->nullable();
            $table->integer('processing_time')->nullable(); // in seconds
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generations');
    }
};
