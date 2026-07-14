<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Project Proposal - {{ $project->title }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #0B1329;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .header-title {
            font-size: 24px;
            font-weight: bold;
            color: #0B1329;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header-subtitle {
            font-size: 14px;
            color: #555555;
            margin: 0;
        }
        .metadata-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
        }
        .metadata-table td {
            padding: 10px 15px;
            font-size: 11px;
            vertical-align: top;
        }
        .metadata-label {
            font-weight: bold;
            color: #64748B;
            text-transform: uppercase;
            font-size: 9px;
            margin-bottom: 3px;
        }
        .metadata-value {
            font-weight: bold;
            color: #1E293B;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #0B1329;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 5px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .section-content {
            text-align: justify;
            white-space: pre-wrap;
        }
        .budget-box {
            background-color: #0B1329;
            color: #FFFFFF;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .budget-title {
            font-size: 10px;
            font-weight: bold;
            color: #94A3B8;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .budget-amount {
            font-size: 20px;
            font-weight: bold;
        }
        .tag-list {
            margin-top: 5px;
        }
        .tag {
            display: inline-block;
            background-color: #F1F5F9;
            border: 1px solid #E2E8F0;
            color: #334155;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            margin-right: 5px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 9px;
            color: #94A3B8;
            text-align: center;
            border-top: 1px solid #E2E8F0;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="header-title">Project Proposal</h1>
        <p class="header-subtitle">Dokumen Inisiasi Proyek - {{ $project->title }}</p>
    </div>

    <table class="metadata-table">
        <tr>
            <td style="width: 50%;">
                <div class="metadata-label">Nama Proyek</div>
                <div class="metadata-value" style="font-size: 13px;">{{ $project->title }}</div>
            </td>
            <td style="width: 25%;">
                <div class="metadata-label">Tanggal Mulai</div>
                <div class="metadata-value">{{ $project->start_date ? $project->start_date->format('d M Y') : '-' }}</div>
            </td>
            <td style="width: 25%;">
                <div class="metadata-label">Status Dokumen</div>
                <div class="metadata-value" style="text-transform: uppercase;">{{ $proposal->status }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="metadata-label">Dibuat Oleh</div>
                <div class="metadata-value">{{ $proposal->creator ? $proposal->creator->name : '-' }}</div>
            </td>
            <td>
                <div class="metadata-label">Terakhir Diperbarui</div>
                <div class="metadata-value">{{ $proposal->updated_at->format('d M Y') }}</div>
            </td>
            <td>
                <div class="metadata-label">Project Manager</div>
                <div class="metadata-value">{{ $project->owner ? $project->owner->name : '-' }}</div>
            </td>
        </tr>
    </table>

    <div class="budget-box">
        <div class="budget-title">Perkiraan Anggaran</div>
        <div class="budget-amount">
            @if($proposal->estimated_budget !== null)
                Rp {{ number_format($proposal->estimated_budget, 2, ',', '.') }}
            @else
                Rp -
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Latar Belakang</div>
        <div class="section-content">{{ $proposal->background ?: 'Tidak ada detail latar belakang.' }}</div>
    </div>

    <div class="section">
        <div class="section-title">Tujuan Strategis</div>
        <div class="section-content">{{ $proposal->objectives ?: 'Tidak ada detail tujuan proyek.' }}</div>
    </div>

    <div class="section">
        <div class="section-title">Kebutuhan Awal</div>
        <div class="tag-list">
            @if($proposal->initial_needs)
                @php
                    $tags = array_filter(array_map('trim', explode(',', $proposal->initial_needs)));
                @endphp
                @forelse($tags as $tag)
                    <span class="tag">{{ $tag }}</span>
                @empty
                    <span style="font-style: italic; color: #94A3B8;">Tidak ada detail kebutuhan awal.</span>
                @endforelse
            @else
                <span style="font-style: italic; color: #94A3B8;">Tidak ada detail kebutuhan awal.</span>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Gambaran Umum Proyek</div>
        <div class="section-content">{{ $proposal->project_overview ?: 'Tidak ada gambaran umum proyek.' }}</div>
    </div>

    <div class="section">
        <div class="section-title">Gambaran Ruang Lingkup (Scope)</div>
        <div class="section-content">{{ $proposal->scope_overview ?: 'Tidak ada gambaran ruang lingkup.' }}</div>
    </div>

    @if($proposal->feedback_notes)
        <div class="section" style="background-color: #FFFBEB; border: 1px solid #FDE68A; padding: 15px; border-radius: 8px;">
            <div class="section-title" style="color: #92400E; border-bottom-color: #FDE68A;">Catatan & Umpan Balik Manager</div>
            <div class="section-content" style="color: #78350F;">{{ $proposal->feedback_notes }}</div>
        </div>
    @endif

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh sistem KelolaIN Project Management. Halaman 1 dari 1
    </div>
</body>
</html>
