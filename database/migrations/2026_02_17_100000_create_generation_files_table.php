<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generation_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generation_id')->constrained('generations')->cascadeOnDelete();
            $table->foreignId('page_generation_id')->nullable()->constrained('page_generations')->nullOnDelete();
            $table->string('file_path', 500);
            $table->longText('file_content');
            $table->string('file_type', 50); // tsx, vue, svelte, ts, css, json, html, config
            $table->boolean('is_scaffold')->default(false);
            $table->timestamps();

            $table->index(['generation_id', 'is_scaffold']);
            $table->index('file_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generation_files');
    }
};
