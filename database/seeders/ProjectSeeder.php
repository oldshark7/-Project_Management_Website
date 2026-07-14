<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectScope;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed ini membuat contoh data: Project -> ProjectScope -> WbsItem (tree sampai 2 level) + task_user (PIC/Reviewer).
     */
    public function run(): void
    {
        $usersByEmail = User::query()
            ->whereIn('email', [
                'teamit@psm.com',
                'ariel@psm.com',
                'ulhaq@psm.com',
                'abid@psm.com',
                'pmo@psm.com',
                'pm@psm.com',
                'manager@psm.com',
            ])
            ->get()
            ->keyBy('email');

        $userId = function (string $email) use ($usersByEmail) {
            $user = $usersByEmail->get($email);
            return $user?->id;
        };

        $now = Carbon::now();

        // 3 project untuk demo:
        // - Project A: owner=PM (pm@psm.com) agar role PM tampil
        // - Project B: status=planning agar role PMO tampil
        // - Project C: owner=PM (pm@psm.com) agar PM punya banyak data
        $projects = [
            [
                'title' => 'TA Project Management Sample - A',
                'description' => 'Contoh proyek untuk kebutuhan demo: Initiation -> Planning -> Executing (WBS & task PIC).',
                'owner' => 'pm@psm.com',
                'manager' => 'manager@psm.com',
                'status' => 'draft',
                'scope' => [
                    'objective' => 'Menyusun dan mengelola proyek berbasis WBS untuk memastikan deliverable dan timeline jelas.',
                    'scope_description' => 'Cakupan proyek meliputi pembentukan WBS, penjadwalan awal, pembagian PIC, dan penilaian risiko tingkat awal.',
                    'in_scope' => 'Penyusunan WBS, assignment PIC, penjadwalan milestone dasar.',
                    'out_of_scope' => 'Pengembangan fitur yang belum termasuk dalam modul demo.',
                    'main_requirements' => 'Struktur WBS bertingkat, data task PIC/reviewer, dan metadata status.',
                    'deliverables' => 'Tree WBS + assignment PIC untuk setiap paket kerja.',
                    'acceptance_criteria' => 'Data seed tampil di halaman project planning (WBS) dan task management.',
                    'assumptions' => 'Akses database dan migrasi sudah tersedia.',
                    'constraints' => 'Dataset demo tidak mencakup semua modul lanjutan.',
                    'notes' => 'Seeder ini hanya untuk pengujian tampilan dan relasi data.',
                ],
            ],
            [
                'title' => 'TA Project Management Sample - B',
                'description' => 'Proyek kedua untuk menguji pengalaman user ketika terdapat banyak proyek.',
                'owner' => 'manager@psm.com',
                'manager' => 'pm@psm.com',
                'status' => 'planning', // agar PMO bisa melihat
                'scope' => [
                    'objective' => 'Menyediakan contoh manajemen proyek dengan variasi tree WBS dan assignment PIC.',
                    'scope_description' => 'Membuat WBS dengan prioritas dan urutan berbeda, serta beberapa PIC berlainan antar project.',
                    'in_scope' => 'WBS tree (2 level), mapping PIC pada item WBS tertentu.',
                    'out_of_scope' => 'Integrasi eksternal di luar sistem demo.',
                    'main_requirements' => 'WBS items lengkap untuk kebutuhan tampilan Kanban/Task management.',
                    'deliverables' => 'WBS tree + relasi task_user.',
                    'acceptance_criteria' => 'Setiap project memiliki WBS dan assignment role yang bisa dilihat.',
                    'assumptions' => 'Seeder user tersedia.',
                    'constraints' => 'Jumlah data dibatasi untuk performance.',
                    'notes' => 'Gunakan untuk melihat perbedaan data antar project.',
                ],
            ],
            [
                'title' => 'TA Project Management Sample - C',
                'description' => 'Proyek ketiga untuk menambah variasi data demo.',
                'owner' => 'pm@psm.com',
                'manager' => 'pmo@psm.com',
                'status' => 'draft',
                'scope' => [
                    'objective' => 'Memperagakan WBS dengan kombinasi prioritas dan status yang beragam.',
                    'scope_description' => 'Paket kerja mencakup penjadwalan milestone, pembagian PIC, dan status draft.',
                    'in_scope' => 'Pembuatan tree WBS dan assignment PIC untuk level tertentu.',
                    'out_of_scope' => 'Detail timeline/budget/risk pada tahap ini.',
                    'main_requirements' => 'WBS items konsisten secara relasi project_id dan project_scope_id.',
                    'deliverables' => 'Tree WBS + task_user mapping.',
                    'acceptance_criteria' => 'Tidak ada pelanggaran foreign key saat seeding.',
                    'assumptions' => 'Tidak ada record dummy lain yang mengganggu unique constraints.',
                    'constraints' => 'Hanya seed sampai WBS.',
                    'notes' => 'Fokus pada relasi inti WBS.',
                ],
            ],
        ];

        foreach ($projects as $index => $p) {
            $ownerId = $userId($p['owner']);
            $managerId = $userId($p['manager']);

            // project
            $project = Project::updateOrCreate(
                ['title' => $p['title']],
                [
                    'description' => $p['description'],
                    'owner_id' => $ownerId,
                    'manager_id' => $managerId,
                    'status' => $p['status'] ?? 'draft',
                    'start_date' => $now->copy()->subDays(14 + ($index * 3))->toDateString(),
                    'end_date' => $now->copy()->addDays(30 + ($index * 5))->toDateString(),
                ]
            );

            // scope (1:1)
            $scope = ProjectScope::updateOrCreate(
                ['project_id' => $project->id],
                [
                    'objective' => $p['scope']['objective'],
                    'scope_description' => $p['scope']['scope_description'],
                    'in_scope' => $p['scope']['in_scope'],
                    'out_of_scope' => $p['scope']['out_of_scope'],
                    'main_requirements' => $p['scope']['main_requirements'],
                    'deliverables' => $p['scope']['deliverables'],
                    'acceptance_criteria' => $p['scope']['acceptance_criteria'],
                    'assumptions' => $p['scope']['assumptions'],
                    'constraints' => $p['scope']['constraints'],
                    'notes' => $p['scope']['notes'],
                    'status' => 'draft',
                    'created_by' => $managerId,
                    'updated_by' => $managerId,
                ]
            );

            $createdBy = $managerId;
            $updatedBy = $managerId;

            // WBS tree (2 level): parent + children
            $wbsParents = [
                [
                    'title' => '1. Perencanaan & Persiapan',
                    'priority' => 'high',
                    'description' => 'Aktivitas awal untuk memastikan kebutuhan dan struktur WBS siap dieksekusi.',
                    'deliverable' => 'Dokumen kebutuhan & breakdown WBS',
                    'estimated' => 5 + $index,
                    'order' => 1,
                    'status' => 'draft',
                ],
                [
                    'title' => '2. Eksekusi & Implementasi',
                    'priority' => 'medium',
                    'description' => 'Pelaksanaan paket kerja sesuai WBS dan pembagian PIC.',
                    'deliverable' => 'Komponen/Modul yang selesai',
                    'estimated' => 10 + $index,
                    'order' => 2,
                    'status' => 'draft',
                ],
                [
                    'title' => '3. Penutupan',
                    'priority' => 'low',
                    'description' => 'Validasi akhir, dokumentasi, dan serah terima.',
                    'deliverable' => 'Handover dan final review',
                    'estimated' => 4 + $index,
                    'order' => 3,
                    'status' => 'draft',
                ],
            ];

            $wbsChildren = [
                [
                    'parent_key' => '1. Perencanaan & Persiapan',
                    'items' => [
                        [
                            'title' => '1.1 Kickoff & Requirement Gathering',
                            'priority' => 'high',
                            'description' => 'Workshop kickoff dan pengumpulan requirement.',
                            'deliverable' => 'Minutes & daftar requirement',
                            'estimated' => 2 + $index,
                            'order' => 1,
                            'status' => 'draft',
                            'pic_email' => 'pmo@psm.com',
                            'reviewer_email' => 'pm@psm.com',
                        ],
                        [
                            'title' => '1.2 Penyusunan WBS & Deliverables',
                            'priority' => 'medium',
                            'description' => 'Menyusun struktur WBS dan deliverables per paket kerja.',
                            'deliverable' => 'WBS tree + deliverables',
                            'estimated' => 3 + $index,
                            'order' => 2,
                            'status' => 'draft',
                            'pic_email' => 'pm@psm.com',
                            'reviewer_email' => 'manager@psm.com',
                        ],
                    ],
                ],
                [
                    'parent_key' => '2. Eksekusi & Implementasi',
                    'items' => [
                        [
                            'title' => '2.1 Implementasi Modul',
                            'priority' => 'high',
                            'description' => 'Implementasi modul sesuai rencana.',
                            'deliverable' => 'Modul ter-implementasi',
                            'estimated' => 5 + $index,
                            'order' => 1,
                            'status' => 'draft',
                            'pic_email' => 'ariel@psm.com',
                            'reviewer_email' => 'pmo@psm.com',
                        ],
                        [
                            'title' => '2.2 Testing & QA',
                            'priority' => 'medium',
                            'description' => 'Pengujian fungsional dan QA.',
                            'deliverable' => 'Laporan testing & checklist QA',
                            'estimated' => 5 + $index,
                            'order' => 2,
                            'status' => 'draft',
                            'pic_email' => 'ulhaq@psm.com',
                            'reviewer_email' => 'pm@psm.com',
                        ],
                    ],
                ],
                [
                    'parent_key' => '3. Penutupan',
                    'items' => [
                        [
                            'title' => '3.1 Dokumentasi & Handover',
                            'priority' => 'medium',
                            'description' => 'Dokumentasi hasil dan serah terima.',
                            'deliverable' => 'Dokumen final',
                            'estimated' => 2 + $index,
                            'order' => 1,
                            'status' => 'draft',
                            'pic_email' => 'manager@psm.com',
                            'reviewer_email' => 'pmo@psm.com',
                        ],
                        [
                            'title' => '3.2 Final Review',
                            'priority' => 'low',
                            'description' => 'Review final bersama stakeholder.',
                            'deliverable' => 'Minutes final review',
                            'estimated' => 2 + $index,
                            'order' => 2,
                            'status' => 'draft',
                            'pic_email' => 'pm@psm.com',
                            'reviewer_email' => 'teamit@psm.com',
                        ],
                    ],
                ],
            ];

            // parent create/update
            $parentIdByTitle = [];
            foreach ($wbsParents as $parent) {
                $wbs = WbsItem::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'project_scope_id' => $scope->id,
                        'parent_id' => null,
                        'title' => $parent['title'],
                    ],
                    [
                        'description' => $parent['description'],
                        'deliverable' => $parent['deliverable'],
                        'priority' => $parent['priority'],
                        'estimated_duration_days' => $parent['estimated'],
                        'status' => $parent['status'],
                        'order_number' => $parent['order'],
                        'created_by' => $createdBy,
                        'updated_by' => $updatedBy,
                    ]
                );

                $parentIdByTitle[$parent['title']] = $wbs->id;
            }

            // reset pivot for project
            DB::table('task_user')
                ->whereIn('wbs_item_id', WbsItem::query()->where('project_id', $project->id)->pluck('id'))
                ->delete();

            // children create/update + pivot
            foreach ($wbsChildren as $group) {
                $parentTitle = $group['parent_key'];
                $parentId = $parentIdByTitle[$parentTitle] ?? null;
                if (!$parentId) {
                    continue;
                }

                foreach ($group['items'] as $child) {
                    $wbsChild = WbsItem::updateOrCreate(
                        [
                            'project_id' => $project->id,
                            'project_scope_id' => $scope->id,
                            'parent_id' => $parentId,
                            'title' => $child['title'],
                        ],
                        [
                            'description' => $child['description'],
                            'deliverable' => $child['deliverable'],
                            'priority' => $child['priority'],
                            'estimated_duration_days' => $child['estimated'],
                            'status' => $child['status'],
                            'order_number' => $child['order'],
                            'created_by' => $createdBy,
                            'updated_by' => $updatedBy,
                        ]
                    );

                    $picId = $userId($child['pic_email']);
                    $reviewerId = $userId($child['reviewer_email']);

                    if ($picId) {
                        DB::table('task_user')->updateOrInsert(
                            [
                                'wbs_item_id' => $wbsChild->id,
                                'user_id' => $picId,
                                'role' => 'PIC',
                            ],
                            [
                                'project_id' => $project->id,
                                'role' => 'PIC',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]
                        );
                    }

                    if ($reviewerId) {
                        DB::table('task_user')->updateOrInsert(
                            [
                                'wbs_item_id' => $wbsChild->id,
                                'user_id' => $reviewerId,
                                'role' => 'Reviewer',
                            ],
                            [
                                'project_id' => $project->id,
                                'role' => 'Reviewer',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]
                        );
                    }
                }
            }
        }
    }
}

