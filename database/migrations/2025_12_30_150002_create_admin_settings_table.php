<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates the admin_settings table for storing admin-configurable settings.
     * Used for:
     * - Error margin percentage for credit calculation
     * - Profit margin percentage for credit calculation
     * - Other platform-wide settings
     */
    public function up(): void
    {
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Setting key (e.g., 'error_margin', 'profit_margin')
            $table->text('value'); // Setting value (JSON encoded for complex values)
            $table->string('type')->default('string'); // Value type: string, integer, float, boolean, json
            $table->text('description')->nullable(); // Admin-friendly description
            $table->string('group')->default('general'); // Setting group for UI organization
            $table->boolean('is_public')->default(false); // Whether clients can read this value
            $table->timestamps();

            $table->index('group');
            $table->index('is_public');
        });

        // Insert default margin settings
        DB::table('admin_settings')->insert([
            [
                'key' => 'error_margin',
                'value' => '0.10',
                'type' => 'float',
                'description' => 'Error margin percentage added to credit calculations to account for estimation inaccuracies',
                'group' => 'billing',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'profit_margin',
                'value' => '0.05',
                'type' => 'float',
                'description' => 'Profit margin percentage added to credit calculations',
                'group' => 'billing',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_base_pages',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Maximum number of pages included in base credit cost',
                'group' => 'billing',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_base_components',
                'value' => '6',
                'type' => 'integer',
                'description' => 'Maximum number of components included in base credit cost',
                'group' => 'billing',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'credits_per_extra_page',
                'value' => '1',
                'type' => 'float',
                'description' => 'Additional credits charged per extra page beyond max_base_pages',
                'group' => 'billing',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'credits_per_extra_component',
                'value' => '0.5',
                'type' => 'float',
                'description' => 'Additional credits charged per extra component beyond max_base_components',
                'group' => 'billing',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
