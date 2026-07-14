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
        Schema::create('project_charters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained('projects')->onDelete('cascade');
            $table->text('project_purpose')->nullable();
            $table->text('business_case')->nullable();
            $table->text('project_objectives')->nullable();
            $table->text('scope_summary')->nullable();
            $table->text('success_criteria')->nullable();
            $table->text('assumptions')->nullable();
            $table->text('constraints')->nullable();
            $table->text('stakeholder_summary')->nullable();
            $table->text('milestone_summary')->nullable();
            $table->decimal('budget_summary', 15, 2)->nullable();
            $table->string('status')->default('draft');
            $table->text('feedback_notes')->nullable();
            $table->text('ai_suggestions')->nullable();
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
        Schema::dropIfExists('project_charters');
    }
};
