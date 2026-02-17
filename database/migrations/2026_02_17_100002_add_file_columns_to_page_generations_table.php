<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_generations', function (Blueprint $table) {
            $table->string('file_path', 255)->nullable()->after('generated_content');
            $table->string('file_type', 50)->default('html')->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('page_generations', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'file_type']);
        });
    }
};
