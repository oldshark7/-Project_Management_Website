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
        Schema::create('cost_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->contrained()->cascaeOnDelete();
            $table->decimal('total_budget',15,2)->defult(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_plans');
    }
};
