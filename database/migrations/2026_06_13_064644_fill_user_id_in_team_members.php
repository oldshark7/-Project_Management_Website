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
        Schema::table('team_members', function (Blueprint $table) {
            DB::statement("
                UPDATE team_members tm
                SET user_id = u.id
                FROM users u
                WHERE LOWER(tm.name) = LOWER(u.name)
            ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            DB::statement("
                UPDATE team_members
                SET user_id = NULL
            ");
        });
    }
};
