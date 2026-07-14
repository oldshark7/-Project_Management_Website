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
        Schema::create('timeline_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('wbs_item_id')->unique()->constrained('wbs_items')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_days');
            $table->foreignId('dependency_wbs_item_id')->nullable()->constrained('wbs_items')->onDelete('cascade');
            $table->boolean('is_milestone')->default(false);
            $table->string('milestone_name')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('draft'); // draft, finalized
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
        Schema::dropIfExists('timeline_items');
    }
};
