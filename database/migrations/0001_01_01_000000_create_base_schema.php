<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Already-installed database (created before this baseline existed): nothing to do.
        if (Schema::hasTable('users')) {
            return;
        }

        DB::unprepared(file_get_contents(database_path('schema/base_schema.sql')));
    }

    public function down(): void
    {
        throw new RuntimeException('Base schema cannot be rolled back. Use migrate:fresh on a scratch database instead.');
    }
};
