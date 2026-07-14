<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class TeamMemberUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teamMembers = TeamMember::all();

        foreach ($teamMembers as $member) {
            // lowercase name without spaces + @psm.com
            $email = strtolower(str_replace(' ', '', $member->name)) . '@psm.com';

            // Check if user already exists
            $existingUser = User::where('email', $email)->first();
            if ($existingUser && strtolower($existingUser->role) !== 'it') {
                // Do not overwrite existing user accounts with the same email if their role is NOT IT,
                // unless that email was generated from a team member.
                continue;
            }

            // Create or update the user account
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $member->name,
                    'password' => Hash::make('teamit123'),
                    'role' => 'IT'
                ]
            );

            // If team_members table has user_id, link them
            if (Schema::hasColumn('team_members', 'user_id')) {
                $member->user_id = $user->id;
                $member->save();
            }
        }
    }
}
