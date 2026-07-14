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
        Schema::create('human_resource_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('human_resource_plan_id')->constrained('human_resource_plans')->onDelete('cascade');
            $table->foreignId('wbs_item_id')->nullable()->constrained('wbs_items')->onDelete('set null');
            $table->string('role_name');
            $table->text('required_skill');
            $table->text('job_description');
            $table->string('person_in_charge')->nullable();
            $table->integer('workload_percentage')->nullable();
            $table->integer('estimated_work_days')->nullable();
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('human_resource_items');
    }
};
