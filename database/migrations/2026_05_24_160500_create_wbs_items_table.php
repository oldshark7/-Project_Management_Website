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
        Schema::create('wbs_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('project_scope_id')->constrained('project_scopes')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('wbs_items')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->text('deliverable')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high
            $table->integer('estimated_duration_days')->nullable();
            $table->string('status')->default('draft'); // draft, finalized
            $table->integer('order_number')->default(0);
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
        Schema::dropIfExists('wbs_items');
    }
};
