<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = [
            [
                'name' => 'Kresna',
                'role_name' => 'Fullstack Developer',
                'skills' => 'Frontend development, backend development, database, API integration, debugging, deployment dasar',
                'default_capacity_percentage' => 100,
                'notes' => '-',
                'is_active' => true,
            ],
            [
                'name' => 'Kayla',
                'role_name' => 'Fullstack Developer',
                'skills' => 'Frontend development, backend development, database, API integration, debugging, deployment dasar',
                'default_capacity_percentage' => 100,
                'notes' => '-',
                'is_active' => true,
            ],
            [
                'name' => 'Fahmi',
                'role_name' => 'Fullstack Developer',
                'skills' => 'Frontend development, backend development, database, API integration, debugging, deployment dasar',
                'default_capacity_percentage' => 100,
                'notes' => '-',
                'is_active' => true,
            ],
            [
                'name' => 'Yazeed',
                'role_name' => 'Fullstack Developer',
                'skills' => 'Frontend development, backend development, database, API integration, debugging, deployment dasar',
                'default_capacity_percentage' => 100,
                'notes' => '-',
                'is_active' => true,
            ],
            [
                'name' => 'Nizar',
                'role_name' => 'UI/UX Designer',
                'skills' => 'User research, wireframing, prototyping, UI design, usability testing, design system',
                'default_capacity_percentage' => 100,
                'notes' => '-',
                'is_active' => true,
            ],
            [
                'name' => 'Hatta',
                'role_name' => 'AI Engineer',
                'skills' => 'Machine learning, prompt engineering, AI integration, model evaluation, API integration',
                'default_capacity_percentage' => 100,
                'notes' => '-',
                'is_active' => true,
            ],
            [
                'name' => 'Daffa',
                'role_name' => 'AI Engineer',
                'skills' => 'Machine learning, prompt engineering, AI integration, model evaluation, API integration',
                'default_capacity_percentage' => 100,
                'notes' => '-',
                'is_active' => true,
            ],
            [
                'name' => 'Naufal',
                'role_name' => 'AI Engineer',
                'skills' => 'Machine learning, prompt engineering, AI integration, model evaluation, API integration',
                'default_capacity_percentage' => 100,
                'notes' => '-',
                'is_active' => true,
            ],
            [
                'name' => 'Rafif',
                'role_name' => 'DevOps Engineer',
                'skills' => 'CI/CD, server configuration, deployment, monitoring, cloud infrastructure, containerization',
                'default_capacity_percentage' => 100,
                'notes' => '-',
                'is_active' => true,
            ],
            [
                'name' => 'Angga',
                'role_name' => 'IT Governance',
                'skills' => 'IT governance, compliance, policy documentation, risk control, SOP, audit support',
                'default_capacity_percentage' => 100,
                'notes' => '-',
                'is_active' => true,
            ],
            [
                'name' => 'Ikhwan',
                'role_name' => 'Data Engineer',
                'skills' => 'Data pipeline, database management, ETL, data modeling, data integration, reporting',
                'default_capacity_percentage' => 100,
                'notes' => '-',
                'is_active' => true,
            ],
            [
                'name' => 'Luvena',
                'role_name' => 'QA Engineer',
                'skills' => 'Software testing, test case design, manual testing, regression testing, bug reporting, user acceptance testing',
                'default_capacity_percentage' => 100,
                'notes' => '-',
                'is_active' => true,
            ],
            [
                'name' => 'Neva',
                'role_name' => 'QA Engineer',
                'skills' => 'Software testing, test case design, manual testing, regression testing, bug reporting, user acceptance testing',
                'default_capacity_percentage' => 100,
                'notes' => '-',
                'is_active' => true,
            ],
        ];

        foreach ($members as $member) {
            TeamMember::updateOrCreate(
                ['name' => $member['name']],
                $member
            );
        }
    }
}
