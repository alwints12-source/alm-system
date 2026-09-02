<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE sla_policies DROP CONSTRAINT uq_sla_scope');
        DB::statement('ALTER TABLE sla_policies ADD CONSTRAINT uq_sla_scope UNIQUE NULLS NOT DISTINCT (priority, maintenance_type, category_id)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE sla_policies DROP CONSTRAINT uq_sla_scope');
        DB::statement('ALTER TABLE sla_policies ADD CONSTRAINT uq_sla_scope UNIQUE (priority, maintenance_type, category_id)');
    }
};
