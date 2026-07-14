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
            $table->unsignedBigInteger('status_updated_by')->nullable()->after('kanban_status');
            $table->timestamp('status_updated_at')->nullable()->after('status_updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wbs_items', function (Blueprint $table) {
            $table->dropColumn(['status_updated_by', 'status_updated_at']);
        });
    }
};
