<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'booting' to the preview_sessions status check constraint.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL stores Laravel enum() as a CHECK constraint.
            // Drop the old constraint and add a new one including 'booting'.
            DB::statement('ALTER TABLE preview_sessions DROP CONSTRAINT IF EXISTS preview_sessions_status_check');
            DB::statement("ALTER TABLE preview_sessions ADD CONSTRAINT preview_sessions_status_check CHECK (status::text = ANY (ARRAY['creating'::text, 'installing'::text, 'booting'::text, 'running'::text, 'stopped'::text, 'error'::text]))");
        }
        // SQLite has no CHECK constraints to update — the column is a plain string.
    }

    /**
     * Revert to the original constraint without 'booting'.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("UPDATE preview_sessions SET status = 'installing' WHERE status = 'booting'");
            DB::statement('ALTER TABLE preview_sessions DROP CONSTRAINT IF EXISTS preview_sessions_status_check');
            DB::statement("ALTER TABLE preview_sessions ADD CONSTRAINT preview_sessions_status_check CHECK (status::text = ANY (ARRAY['creating'::text, 'installing'::text, 'running'::text, 'stopped'::text, 'error'::text]))");
        }
    }
};
