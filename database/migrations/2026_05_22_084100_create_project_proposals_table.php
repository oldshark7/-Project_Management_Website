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
        Schema::create('project_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained('projects')->onDelete('cascade');
            $table->text('background')->nullable();
            $table->text('objectives')->nullable();
            $table->text('initial_needs')->nullable();
            $table->text('project_overview')->nullable();
            $table->text('scope_overview')->nullable();
            $table->decimal('estimated_budget', 15, 2)->nullable();
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
        Schema::dropIfExists('project_proposals');
    }
};
