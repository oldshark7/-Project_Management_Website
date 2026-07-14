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
        Schema::table('human_resource_items', function (Blueprint $table) {
            $table->foreignId('team_member_id')->nullable()->constrained('team_members')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('human_resource_items', function (Blueprint $table) {
            $table->dropForeign(['team_member_id']);
            $table->dropColumn('team_member_id');
        });
    }
};
