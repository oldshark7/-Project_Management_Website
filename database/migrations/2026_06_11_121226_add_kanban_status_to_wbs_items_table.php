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
        Schema::table('wbs_items', function (Blueprint $table) {
            $table->enum('kanban_status', ['todo', 'ongoing', 'review', 'approved'])
                ->default('todo')
                ->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wbs_items', function (Blueprint $table) {
            $table->dropColumn('kanban_status');
        });
    }
};
