<?php

namespace App\Http\Controllers;

use App\Models\MeetingSchedule;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $meetings = MeetingSchedule::with(['project', 'creator'])
            ->orderBy('meeting_date')
            ->orderBy('start_time')
            ->get();

        $projects = Project::orderBy('title')->get();

        return view('project-monitoring.meeting.index', compact(
            'meetings',
            'projects'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'       => ['required', 'exists:projects,id'],
            'title'            => ['required', 'string', 'max:255'],
            'meeting_type'     => ['required', 'string', 'max:100'],
            'meeting_date'     => ['required', 'date', 'after_or_equal:today'],
            'start_time'       => ['required'],
            'end_time'         => ['nullable','after:start_time',],
            'location'         => ['nullable', 'string', 'max:255'],
            'meeting_link'     => ['nullable', 'url'],
            'reminder_before'  => ['required', 'integer'],
            'description'      => ['nullable', 'string'],
            'status'           => ['nullable'],
        ]);

        if ($request->meeting_date === now()->toDateString()) {
            $minimumTime = now()->addMinutes(30)->format('H:i');

            if ($request->start_time < $minimumTime) {
                return back()
                    ->withErrors([
                        'start_time' => 'Start time must be at least 30 minutes from now.'
                    ])
                    ->withInput();
            }
        }

        $validated['created_by'] = Auth::id();

        MeetingSchedule::create($validated);

        return back()->with('success', 'Meeting berhasil dijadwalkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MeetingSchedule $meetingSchedule)
    {
        $meetingSchedule->load([
            'project',
            'creator',
        ]);

        return response()->json($meetingSchedule);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MeetingSchedule $meetingSchedule)
    {
        $validated = $request->validate([
            'project_id'       => ['required', 'exists:projects,id'],
            'title'            => ['required', 'string', 'max:255'],
            'meeting_type'     => ['required', 'string', 'max:100'],
            'meeting_date'     => ['required', 'date'],
            'start_time'       => ['required'],
            'end_time'         => ['nullable'],
            'location'         => ['nullable', 'string', 'max:255'],
            'meeting_link'     => ['nullable', 'url'],
            'reminder_before'  => ['required', 'integer'],
            'description'      => ['nullable', 'string'],
            'status'           => ['required'],
        ]);

        $meetingSchedule->update($validated);

        return back()->with('success', 'Meeting berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MeetingSchedule $meetingSchedule)
    {
        $meetingSchedule->delete();

        return back()->with('success', 'Meeting berhasil dihapus.');
    }
}