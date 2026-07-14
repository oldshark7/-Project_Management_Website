<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectProposal;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProjectProposalSeeder extends Seeder
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
                'pm@psm.com',
                'manager@psm.com',
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
            $createdBy = $userId('pm@psm.com') ?? $userId('manager@psm.com');
            $updatedBy = $createdBy;

            ProjectProposal::updateOrCreate(
                ['project_id' => $project->id],
                [
                    'background' => 'Background proposal untuk: ' . $project->title,
                    'objectives' => 'Tujuan utama proposal: perencanaan eksekusi proyek dan kesiapan WBS.',
                    'initial_needs' => 'Kebutuhan awal: referensi stakeholder, struktur WBS awal, dan definisi deliverables.',
                    'project_overview' => 'Ringkasan proyek mencakup ruang lingkup, prioritas, dan estimasi awal.',
                    'scope_overview' => 'Scope overview disesuaikan dengan project scope yang sudah dibuat.',
                    'estimated_budget' => 250000000 + ((int)$index * 50000000),
                    'status' => 'draft',
                    'feedback_notes' => 'Catatan feedback awal (seed).',
                    'ai_suggestions' => 'Saran AI (seed): validasi asumsi dan rincikan deliverables WBS.',
                    'created_by' => $createdBy,
                    'updated_by' => $updatedBy,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}

