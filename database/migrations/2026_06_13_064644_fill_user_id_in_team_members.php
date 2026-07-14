<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $users = DB::table('users')->select('id', 'name')->get();

        foreach ($users as $user) {
            DB::table('team_members')
                ->whereRaw('LOWER(name) = ?', [strtolower($user->name)])
                ->update(['user_id' => $user->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('team_members')->update(['user_id' => null]);
    }
};
