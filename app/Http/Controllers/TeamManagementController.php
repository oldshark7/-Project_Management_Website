<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use Illuminate\Http\Request;

class TeamManagementController extends Controller
{
    /**
     * Display a listing of the team members.
     */
    public function index(Request $request)
    {
        $query = TeamMember::query();

        $teamMembers = $query->orderBy('name')->get();
        $totalMembers = TeamMember::count();

        // Calculate average workload of all members
        $totalWorkload = 0;
        foreach ($teamMembers as $member) {
            $totalWorkload += $member->current_workload_percentage;
        }
        $avgWorkload = $totalMembers > 0 ? round($totalWorkload / $totalMembers) : 0;

        return view('project-executing.team-management.index', compact('teamMembers', 'totalMembers', 'avgWorkload'));
    }

    /**
     * Store a newly created team member in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_name' => 'required|string|max:255',
            'skills' => 'required|string',
            'default_capacity_percentage' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            // Create user login account with role IT
            $user = \App\Models\User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role' => 'IT',
            ]);

            // Prepare team member data
            $teamMemberData = [
                'name' => $request->name,
                'role_name' => $request->role_name,
                'skills' => $request->skills,
                'default_capacity_percentage' => $request->default_capacity_percentage,
                'notes' => $request->notes,
                'is_active' => true,
            ];

            // Link user if user_id column exists
            if (\Illuminate\Support\Facades\Schema::hasColumn('team_members', 'user_id')) {
                $teamMemberData['user_id'] = $user->id;
            }

            TeamMember::create($teamMemberData);
        });

        return redirect()->route('teamManagement')
            ->with('success', 'Anggota tim baru dan akun login berhasil ditambahkan.');
    }

    /**
     * Update the specified team member in storage.
     */
    public function update(Request $request, TeamMember $teamMember)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role_name' => 'required|string|max:255',
            'skills' => 'required|string',
            'default_capacity_percentage' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $teamMember->update([
            'name' => $request->name,
            'role_name' => $request->role_name,
            'skills' => $request->skills,
            'default_capacity_percentage' => $request->default_capacity_percentage,
            'notes' => $request->notes,
        ]);

        return redirect()->route('teamManagement')
            ->with('success', 'Data anggota tim berhasil diperbarui.');
    }

    /**
     * Toggle active/inactive status of the team member.
     */
    public function toggleStatus(TeamMember $teamMember)
    {
        $teamMember->is_active = !$teamMember->is_active;
        $teamMember->save();

        $statusStr = $teamMember->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('teamManagement')
            ->with('success', "Anggota tim {$teamMember->name} berhasil {$statusStr}.");
    }

    /**
     * Remove the specified team member from storage.
     */
    public function destroy(TeamMember $teamMember)
    {
        $teamMember->delete();

        return redirect()->route('teamManagement')
            ->with('success', "Anggota tim {$teamMember->name} berhasil dihapus.");
    }
}
