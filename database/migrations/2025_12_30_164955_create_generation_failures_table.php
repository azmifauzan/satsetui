<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table untuk record semua kegagalan generation untuk analisa admin.
     * Setiap failure dicatat lengkap dengan context dan error details.
     */
    public function up(): void
    {
        Schema::create('generation_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generation_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('page_generation_id')->nullable()->constrained()->onDelete('cascade');
            
            // Failure details
            $table->string('failure_type'); // 'llm_error', 'timeout', 'invalid_response', 'system_error'
            $table->string('error_code')->nullable(); // HTTP code atau error code dari LLM
            $table->text('error_message');
            $table->longText('error_stack_trace')->nullable();
            
            // Context saat failure
            $table->string('model_used')->nullable();
            $table->string('page_name')->nullable();
            $table->integer('page_index')->nullable();
            $table->integer('attempt_number')->default(1); // Untuk retry tracking
            
            // Credits yang di-refund
            $table->integer('credits_refunded')->default(0);
            $table->boolean('credits_refunded_at')->nullable();
            
            // Request/Response data untuk debugging
            $table->longText('request_data')->nullable(); // JSON blueprint, params
            $table->longText('response_data')->nullable(); // Response dari LLM
            
            // Metadata
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('additional_context')->nullable(); // Extra data
            
            $table->timestamps();
            
            // Indexes untuk admin analytics
            $table->index('failure_type');
            $table->index('model_used');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generation_failures');
    }
};
