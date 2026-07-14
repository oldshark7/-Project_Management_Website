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
        Schema::create('meeting_schedules', function (Blueprint $table) {
            $table->id();

            // Relasi Project
            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            // Informasi Meeting
            $table->string('title');
            $table->enum('meeting_type', [
                'Project Kickoff',
                'Progress Meeting',
                'Coordination Meeting',
                'Client Meeting',
                'Issue Discussion',
                'Risk Review',
                'Change Request Review',
                'Project Closing',
                'Other',
            ])->default('Progress Meeting');

            $table->date('meeting_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();

            // Lokasi / Online
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();

            // Reminder
            $table->integer('reminder_before')
                ->default(30)
                ->comment('Reminder in minutes');

            // Catatan
            $table->text('description')->nullable();

            // Status
            $table->enum('status', [
                'Scheduled',
                'Completed',
                'Cancelled'
            ])->default('Scheduled');

            // Audit
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            // Index
            $table->index(['project_id', 'meeting_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_schedules');
    }
};