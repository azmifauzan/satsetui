<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration updates the generations table with:
     * - Credit breakdown fields (margins, totals)
     * - Blueprint JSON storage for complete wizard state
     * - Output format tracking
     */
    public function up(): void
    {
        Schema::table('generations', function (Blueprint $table) {
            // Blueprint storage (complete wizard state for regeneration/audit)
            $table->json('blueprint_json')->nullable()->after('mcp_prompt');

            // Credit breakdown fields
            $table->decimal('base_credits', 10, 4)->default(0)->after('credits_used');
            $table->decimal('extra_page_credits', 10, 4)->default(0)->after('base_credits');
            $table->decimal('extra_component_credits', 10, 4)->default(0)->after('extra_page_credits');
            $table->decimal('subtotal_credits', 10, 4)->default(0)->after('extra_component_credits');
            $table->decimal('error_margin', 8, 4)->default(0.10)->after('subtotal_credits');
            $table->decimal('profit_margin', 8, 4)->default(0.05)->after('error_margin');
            $table->decimal('error_margin_amount', 10, 4)->default(0)->after('profit_margin');
            $table->decimal('profit_margin_amount', 10, 4)->default(0)->after('error_margin_amount');

            // Generation metadata
            $table->string('output_format')->nullable()->after('model_used');
            $table->string('framework')->nullable()->after('output_format');
            $table->string('category')->nullable()->after('framework');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generations', function (Blueprint $table) {
            $table->dropColumn([
                'blueprint_json',
                'base_credits',
                'extra_page_credits',
                'extra_component_credits',
                'subtotal_credits',
                'error_margin',
                'profit_margin',
                'error_margin_amount',
                'profit_margin_amount',
                'output_format',
                'framework',
                'category',
            ]);
        });
    }
};
