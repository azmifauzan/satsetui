<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preview_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generation_id')->constrained('generations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('workspace_path', 500)->nullable();
            $table->unsignedInteger('preview_port')->nullable();
            $table->enum('preview_type', ['server', 'static'])->default('server');
            $table->enum('status', ['creating', 'installing', 'running', 'stopped', 'error'])->default('creating');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['generation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preview_sessions');
    }
};
