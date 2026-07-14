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
            $table->string('role_name')->nullable()->change();
            $table->text('required_skill')->nullable()->change();
            $table->text('job_description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('human_resource_items', function (Blueprint $table) {
            $table->string('role_name')->nullable(false)->change();
            $table->text('required_skill')->nullable(false)->change();
            $table->text('job_description')->nullable(false)->change();
        });
    }
};
