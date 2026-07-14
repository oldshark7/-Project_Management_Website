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
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('issues', function (Blueprint $table) {
            $table->string('status')->default('open');
        });

        // optional: enforce constraint di PostgreSQL
        DB::statement("
        ALTER TABLE issues
        ADD CONSTRAINT status_check
        CHECK (status IN ('open','in_progress','done','closed'))
    ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE issues DROP CONSTRAINT IF EXISTS status_check");

        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('issues', function (Blueprint $table) {
            $table->string('status')->default('open');
        });
    }
};
