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
        Schema::create('topup_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('credit_package_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('amount'); // IDR amount paid
            $table->integer('credits_added');
            $table->string('mayar_transaction_id')->nullable()->index();
            $table->string('mayar_payment_link')->nullable();
            $table->enum('status', ['pending', 'success', 'failed', 'expired'])->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->json('mayar_payload')->nullable(); // raw webhook payload for audit
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topup_transactions');
    }
};
