<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates generation_costs table for tracking actual LLM API costs.
     */
    public function up(): void
    {
        // Create generation_costs table
        Schema::create('generation_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('page_generation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            $table->string('model_name');
            $table->string('provider')->index();
            
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->integer('total_tokens')->default(0);
            
            $table->decimal('input_price_per_million', 10, 7);
            $table->decimal('output_price_per_million', 10, 7);
            
            $table->decimal('input_cost_usd', 12, 6);
            $table->decimal('output_cost_usd', 12, 6);
            $table->decimal('total_cost_usd', 12, 6)->index();
            
            $table->integer('credits_charged')->default(0);
            $table->decimal('profit_margin_percent', 10, 2)->nullable();
            
            $table->integer('processing_time_ms')->nullable();
            
            $table->decimal('usd_to_local_rate', 10, 4)->nullable();
            $table->decimal('total_cost_local', 15, 2)->nullable();
            
            // Raw request/response tracking
            $table->text('raw_request')->nullable();
            $table->text('raw_response')->nullable();
            
            $table->timestamps();
            
            $table->index(['generation_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['model_name', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generation_costs');
    }
};
