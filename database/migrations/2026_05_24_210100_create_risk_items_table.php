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
        Schema::create('risk_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_management_plan_id')->constrained('risk_management_plans')->onDelete('cascade');
            $table->string('risk_title');
            $table->text('risk_description');
            $table->text('risk_cause')->nullable();
            $table->text('impact');
            $table->string('probability'); // low, medium, high
            $table->string('severity'); // low, medium, high
            $table->text('mitigation_plan');
            $table->text('contingency_plan')->nullable();
            $table->string('risk_owner')->nullable();
            $table->foreignId('related_wbs_item_id')->nullable()->constrained('wbs_items')->onDelete('set null');
            $table->string('status')->default('open'); // open, mitigated, accepted, closed
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
        Schema::dropIfExists('risk_items');
    }
};
