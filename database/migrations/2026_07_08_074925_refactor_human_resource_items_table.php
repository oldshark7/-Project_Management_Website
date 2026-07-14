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
            $table->dropForeign(['wbs_item_id']);
            $table->dropColumn([
                'wbs_item_id',
                'workload_percentage',
                'job_description',
                'estimated_work_days',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('human_resource_items', function (Blueprint $table) {
            $table->foreignId('wbs_item_id')
                ->nullable()
                ->constrained('wbs_items')
                ->nullOnDelete();
            $table->integer('workload_percentage')->default(0);
            $table->text('job_description')->nullable();
            $table->integer('estimated_work_days')->default(1);
        });
    }
};
