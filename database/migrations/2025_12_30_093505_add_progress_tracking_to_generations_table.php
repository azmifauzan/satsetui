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
        Schema::table('generations', function (Blueprint $table) {
            $table->json('progress_data')->nullable()->after('generated_content');
            $table->integer('current_page_index')->default(0)->after('progress_data');
            $table->integer('total_pages')->default(1)->after('current_page_index');
            $table->string('current_status')->default('pending')->after('total_pages'); // pending, generating, completed, failed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generations', function (Blueprint $table) {
            $table->dropColumn(['progress_data', 'current_page_index', 'total_pages', 'current_status']);
        });
    }
};
