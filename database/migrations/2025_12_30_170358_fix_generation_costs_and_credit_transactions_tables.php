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
        // Check if generation_costs table exists
        if (!Schema::hasTable('generation_costs')) {
            // Create new table if not exists
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
        } else {
            // Add missing columns if table exists but is empty
            Schema::table('generation_costs', function (Blueprint $table) {
                if (!Schema::hasColumn('generation_costs', 'generation_id')) {
                    $table->foreignId('generation_id')->after('id')->constrained()->cascadeOnDelete();
                }
                if (!Schema::hasColumn('generation_costs', 'page_generation_id')) {
                    $table->foreignId('page_generation_id')->after('generation_id')->nullable()->constrained()->nullOnDelete();
                }
                if (!Schema::hasColumn('generation_costs', 'user_id')) {
                    $table->foreignId('user_id')->after('page_generation_id')->constrained()->cascadeOnDelete();
                }
                if (!Schema::hasColumn('generation_costs', 'model_name')) {
                    $table->string('model_name')->after('user_id');
                }
                if (!Schema::hasColumn('generation_costs', 'provider')) {
                    $table->string('provider')->after('model_name')->index();
                }
                if (!Schema::hasColumn('generation_costs', 'input_tokens')) {
                    $table->integer('input_tokens')->after('provider')->default(0);
                }
                if (!Schema::hasColumn('generation_costs', 'output_tokens')) {
                    $table->integer('output_tokens')->after('input_tokens')->default(0);
                }
                if (!Schema::hasColumn('generation_costs', 'total_tokens')) {
                    $table->integer('total_tokens')->after('output_tokens')->default(0);
                }
                if (!Schema::hasColumn('generation_costs', 'input_price_per_million')) {
                    $table->decimal('input_price_per_million', 10, 7)->after('total_tokens');
                }
                if (!Schema::hasColumn('generation_costs', 'output_price_per_million')) {
                    $table->decimal('output_price_per_million', 10, 7)->after('input_price_per_million');
                }
                if (!Schema::hasColumn('generation_costs', 'input_cost_usd')) {
                    $table->decimal('input_cost_usd', 12, 6)->after('output_price_per_million');
                }
                if (!Schema::hasColumn('generation_costs', 'output_cost_usd')) {
                    $table->decimal('output_cost_usd', 12, 6)->after('input_cost_usd');
                }
                if (!Schema::hasColumn('generation_costs', 'total_cost_usd')) {
                    $table->decimal('total_cost_usd', 12, 6)->after('output_cost_usd')->index();
                }
                if (!Schema::hasColumn('generation_costs', 'credits_charged')) {
                    $table->integer('credits_charged')->after('total_cost_usd')->default(0);
                }
                if (!Schema::hasColumn('generation_costs', 'profit_margin_percent')) {
                    $table->decimal('profit_margin_percent', 10, 2)->after('credits_charged')->nullable();
                }
                if (!Schema::hasColumn('generation_costs', 'processing_time_ms')) {
                    $table->integer('processing_time_ms')->after('profit_margin_percent')->nullable();
                }
                if (!Schema::hasColumn('generation_costs', 'usd_to_local_rate')) {
                    $table->decimal('usd_to_local_rate', 10, 4)->after('processing_time_ms')->nullable();
                }
                if (!Schema::hasColumn('generation_costs', 'total_cost_local')) {
                    $table->decimal('total_cost_local', 15, 2)->after('usd_to_local_rate')->nullable();
                }
                if (!Schema::hasColumn('generation_costs', 'raw_request')) {
                    $table->text('raw_request')->after('total_cost_local')->nullable();
                }
                if (!Schema::hasColumn('generation_costs', 'raw_response')) {
                    $table->text('raw_response')->after('raw_request')->nullable();
                }
            });
            
            // Add indexes if not exist
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('generation_costs');
            
            Schema::table('generation_costs', function (Blueprint $table) use ($indexes) {
                if (!isset($indexes['generation_costs_generation_id_created_at_index'])) {
                    $table->index(['generation_id', 'created_at']);
                }
                if (!isset($indexes['generation_costs_user_id_created_at_index'])) {
                    $table->index(['user_id', 'created_at']);
                }
                if (!isset($indexes['generation_costs_model_name_provider_index'])) {
                    $table->index(['model_name', 'provider']);
                }
            });
        }
        
        // Fix credit_transactions table - change description to text
        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generation_costs');
        
        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->string('description', 255)->nullable()->change();
        });
    }
};
