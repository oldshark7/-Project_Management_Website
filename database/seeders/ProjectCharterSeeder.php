<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectCharter;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProjectCharterSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $usersByEmail = User::query()
            ->whereIn('email', [
                'manager@psm.com',
                'pm@psm.com',
                'pmo@psm.com',
                'teamit@psm.com',
            ])
            ->get()
            ->keyBy('email');

        $userId = function (string $email) use ($usersByEmail): ?int {
            $u = $usersByEmail->get($email);
            return $u?->id;
        };

        $projects = Project::query()->select(['id', 'title'])->orderBy('id')->get();

        foreach ($projects as $index => $project) {
            $createdBy = $userId('manager@psm.com') ?? $userId('pm@psm.com');
            $updatedBy = $createdBy;

            ProjectCharter::updateOrCreate(
                ['project_id' => $project->id],
                [
                    'project_purpose' => 'Purpose charter untuk: ' . $project->title,
                    'business_case' => 'Business case (seed): meningkatkan efisiensi melalui pengelolaan proyek berbasis WBS.',
                    'project_objectives' => 'Objektif proyek: menyelesaikan deliverables sesuai scope dan timeline yang disepakati.',
                    'scope_summary' => 'Scope summary mengacu pada project scope yang tersedia di database.',
                    'success_criteria' => 'Kriteria sukses: deliverables lengkap, jadwal terpenuhi, dan stakeholder menyetujui hasil.',
                    'assumptions' => 'Asumsi (seed): data WBS tersedia dan PIC/reviewer ditetapkan.',
                    'constraints' => 'Kendala (seed): dataset demo hanya mencakup tahap awal sampai WBS.',
                    'stakeholder_summary' => 'Stakeholder: PM, Manager, dan tim terkait (seed).',
                    'milestone_summary' => 'Milestone (seed): kickoff, implementasi, dan final review.',
                    'budget_summary' => 300000000 + ((int)$index * 65000000),
                    'status' => 'draft',
                    'feedback_notes' => 'Catatan feedback awal charter (seed).',
                    'ai_suggestions' => 'Saran AI (seed): buat rencana komunikasi stakeholder dan definisikan risiko utama.',
                    'created_by' => $createdBy,
                    'updated_by' => $updatedBy,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}

