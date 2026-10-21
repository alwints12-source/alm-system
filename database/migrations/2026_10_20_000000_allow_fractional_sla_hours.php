<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Allow SLA targets shorter than one hour (1 minute = 1/60 hour).
        DB::statement('ALTER TABLE sla_policies
            ALTER COLUMN response_time_hours TYPE numeric(10,6),
            ALTER COLUMN resolution_time_hours TYPE numeric(10,6)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE sla_policies
            ALTER COLUMN response_time_hours TYPE integer USING CEIL(response_time_hours)::integer,
            ALTER COLUMN resolution_time_hours TYPE integer USING CEIL(resolution_time_hours)::integer');
    }
};
