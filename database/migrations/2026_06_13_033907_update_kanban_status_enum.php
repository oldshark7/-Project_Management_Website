<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE wbs_items DROP CONSTRAINT IF EXISTS wbs_items_kanban_status_check");

            DB::statement("ALTER TABLE wbs_items 
            ADD CONSTRAINT wbs_items_kanban_status_check 
            CHECK (kanban_status IN ('todo','ongoing','review','approved','done'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE wbs_items DROP CONSTRAINT IF EXISTS wbs_items_kanban_status_check");
        }
    }
};
