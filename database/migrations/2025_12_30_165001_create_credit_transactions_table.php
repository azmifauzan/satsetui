<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table untuk record semua transaksi kredit (charge, refund, topup, bonus).
     * Setiap perubahan balance user harus tercatat di sini untuk audit trail.
     */
    public function up(): void
    {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('generation_id')->nullable()->constrained()->onDelete('set null');
            
            // Transaction details
            $table->string('type'); // 'charge', 'refund', 'topup', 'bonus', 'adjustment'
            $table->integer('amount'); // Positive untuk topup/refund, negative untuk charge
            $table->integer('balance_before');
            $table->integer('balance_after');
            
            // Reference
            $table->string('reference_type')->nullable(); // 'Generation', 'PageGeneration', 'GenerationFailure'
            $table->unsignedBigInteger('reference_id')->nullable();
            
            // Description & metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Model used, pages count, etc
            
            // Admin tracking
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->onDelete('set null'); // Jika manual adjustment
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
