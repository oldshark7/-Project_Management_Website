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
        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();

            // relasi utama
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wbs_item_id')->constrained()->cascadeOnDelete();

            // info perubahan
            $table->string('field_changed'); // contoh: title, description, estimated_duration_days
            $table->text('old_value')->nullable();
            $table->text('new_value');

            // alasan perubahan
            $table->text('reason')->nullable();

            // status approval
            $table->string('status')->default('pending'); // pending, approved, rejected

            // user
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_request');
    }
};
