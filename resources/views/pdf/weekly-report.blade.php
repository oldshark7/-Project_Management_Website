<h1>Weekly Report</h1>
<h2>{{ $project->title }}</h2>
<p>{{ $project->proposal->background }}</p>

<h4>Anggota Proyek:</h4>
<ul>
    @if($project->humanResourcePlan && $project->humanResourcePlan->humanResourceItems)
        @foreach($project->humanResourcePlan->humanResourceItems
                ->map(fn ($item) => $item->teamMember)
                ->filter()
                ->unique('id')
            as $user)
            <li>{{ $user->name }} - {{ $user->role_name }}</li>
        @endforeach
    @else
        <li>Tidak ada anggota</li>
    @endif
</ul>

<hr>

<p>Progress Minggu Ini: {{ round($thisWeek, 1) }}%</p>
<p>Progress Minggu Lalu: {{ round($lastWeek, 1) }}%</p>
<p>
    Perubahan:
    @if($diff >= 0)
        Naik {{ round($diff, 1) }}%
    @else
        Turun {{ abs(round($diff, 1)) }}%
    @endif
</p>

<hr>

<h3>AI Insight & Recommendation</h3>
<p>
    {{ is_array($aiInsight) ? $aiInsight['message'] : $aiInsight }}
</p>

<h3>Task Breakdown</h3>
<ul>
    <li>To Do: {{ $todo }}</li>
    <li>Ongoing: {{ $ongoing }}</li>
    <li>Done: {{ $done }}</li>
    <li>Approved: {{ $approved }}</li>
</ul>