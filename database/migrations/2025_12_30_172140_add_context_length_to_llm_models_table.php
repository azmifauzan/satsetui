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
        Schema::table('llm_models', function (Blueprint $table) {
            $table->integer('context_length')->after('estimated_credits_per_generation')->default(8192)->comment('Maximum context window size in tokens');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('llm_models', function (Blueprint $table) {
            $table->dropColumn('context_length');
        });
    }
};
