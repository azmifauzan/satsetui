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
        Schema::create('refinement_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generation_id')->constrained('generations')->onDelete('cascade');
            $table->string('role'); // 'system', 'user', 'assistant'
            $table->text('content');
            $table->string('type')->nullable(); // 'status', 'page_complete', 'error', 'refine'
            $table->string('page_name')->nullable(); // which page this message relates to
            $table->timestamps();

            $table->index(['generation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refinement_messages');
    }
};
