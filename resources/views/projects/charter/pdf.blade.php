<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Project Charter - {{ $project->title }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #0B1329;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header-title {
            font-size: 22px;
            font-weight: bold;
            color: #0B1329;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header-subtitle {
            font-size: 13px;
            color: #555555;
            margin: 0;
        }
        .metadata-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
        }
        .metadata-table td {
            padding: 8px 12px;
            font-size: 10px;
            vertical-align: top;
        }
        .metadata-label {
            font-weight: bold;
            color: #64748B;
            text-transform: uppercase;
            font-size: 8px;
            margin-bottom: 3px;
        }
        .metadata-value {
            font-weight: bold;
            color: #1E293B;
        }
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #0B1329;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 4px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .section-content {
            text-align: justify;
            white-space: pre-wrap;
        }
        .budget-box {
            background-color: #0B1329;
            color: #FFFFFF;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .budget-title {
            font-size: 9px;
            font-weight: bold;
            color: #94A3B8;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .budget-amount {
            font-size: 18px;
            font-weight: bold;
        }
        .milestone-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            border: 1px solid #E2E8F0;
        }
        .milestone-table th {
            background-color: #F1F5F9;
            color: #475569;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 10px;
            border: 1px solid #E2E8F0;
        }
        .milestone-table td {
            padding: 8px 10px;
            border: 1px solid #E2E8F0;
            font-size: 10px;
        }
        .grid-2 {
            width: 100%;
            margin-bottom: 15px;
        }
        .grid-2 td {
            width: 50%;
            padding: 0 10px 0 0;
            vertical-align: top;
        }
        .grid-2 td.last {
            padding: 0 0 0 10px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #94A3B8;
            text-align: center;
            border-top: 1px solid #E2E8F0;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="header-title">Project Charter</h1>
        <p class="header-subtitle">Piagam Inisiasi Proyek - {{ $project->title }}</p>
    </div>

    <table class="metadata-table">
        <tr>
            <td style="width: 50%;">
                <div class="metadata-label">Nama Proyek</div>
                <div class="metadata-value" style="font-size: 12px;">{{ $project->title }}</div>
            </td>
            <td style="width: 25%;">
                <div class="metadata-label">Tanggal Mulai</div>
                <div class="metadata-value">{{ $project->start_date ? $project->start_date->format('d M Y') : '-' }}</div>
            </td>
            <td style="width: 25%;">
                <div class="metadata-label">Status Dokumen</div>
                <div class="metadata-value" style="text-transform: uppercase;">{{ $charter->status }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="metadata-label">Dibuat Oleh</div>
                <div class="metadata-value">{{ $charter->creator ? $charter->creator->name : '-' }}</div>
            </td>
            <td>
                <div class="metadata-label">Terakhir Diperbarui</div>
                <div class="metadata-value">{{ $charter->updated_at->format('d M Y') }}</div>
            </td>
            <td>
                <div class="metadata-label">Project Manager</div>
                <div class="metadata-value">{{ $project->owner ? $project->owner->name : '-' }}</div>
            </td>
        </tr>
    </table>

    <div class="budget-box">
        <div class="budget-title">Ringkasan Anggaran</div>
        <div class="budget-amount">
            @if($charter->budget_summary !== null)
                Rp {{ number_format($charter->budget_summary, 2, ',', '.') }}
            @else
                Rp -
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Ringkasan Eksekutif</div>
        <div style="margin-bottom: 12px;">
            <div class="metadata-label" style="margin-bottom: 2px;">Tujuan Proyek</div>
            <div class="section-content" style="background-color: #F8FAFC; padding: 10px; border: 1px solid #F1F5F9; border-radius: 4px;">{{ $charter->project_purpose ?: 'Tidak ada detail tujuan proyek.' }}</div>
        </div>
        <div>
            <div class="metadata-label" style="margin-bottom: 2px;">Business Case</div>
            <div class="section-content" style="background-color: #F8FAFC; padding: 10px; border: 1px solid #F1F5F9; border-radius: 4px;">{{ $charter->business_case ?: 'Tidak ada detail kasus bisnis.' }}</div>
        </div>
    </div>

    <table class="grid-2">
        <tr>
            <td>
                <div class="section">
                    <div class="section-title">Objektif Utama</div>
                    <div class="section-content" style="background-color: #F8FAFC; padding: 10px; border: 1px solid #F1F5F9; border-radius: 4px; min-height: 80px;">{{ $charter->project_objectives ?: 'Tidak ada detail sasaran proyek.' }}</div>
                </div>
            </td>
            <td class="last">
                <div class="section">
                    <div class="section-title">Kriteria Sukses</div>
                    <div class="section-content" style="background-color: #F8FAFC; padding: 10px; border: 1px solid #F1F5F9; border-radius: 4px; min-height: 80px;">{{ $charter->success_criteria ?: 'Tidak ada kriteria keberhasilan.' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="grid-2">
        <tr>
            <td>
                <div class="section">
                    <div class="section-title">Ringkasan Ruang Lingkup</div>
                    <div class="section-content" style="background-color: #F8FAFC; padding: 10px; border: 1px solid #F1F5F9; border-radius: 4px; min-height: 80px;">{{ $charter->scope_summary ?: 'Tidak ada ringkasan ruang lingkup.' }}</div>
                </div>
            </td>
            <td class="last">
                <div class="section">
                    <div class="section-title">Ringkasan Milestone</div>
                    <div class="section-content" style="background-color: #F8FAFC; padding: 10px; border: 1px solid #F1F5F9; border-radius: 4px; min-height: 80px;">{{ $charter->milestone_summary ?: 'Tidak ada ringkasan milestone.' }}</div>
                </div>
            </td>
        </tr>
    </table>

    @if(isset($actualMilestones) && $actualMilestones->isNotEmpty())
        <div class="section">
            <div class="section-title">Milestone Aktual dari WBS/Timeline</div>
            <table class="milestone-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Nama Milestone</th>
                        <th style="width: 30%;">Task WBS</th>
                        <th style="width: 25%;">Jadwal</th>
                        <th style="width: 20%; text-align: center;">Durasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($actualMilestones as $milestone)
                        <tr>
                            <td style="font-weight: bold; color: #4338CA;">{{ $milestone->milestone_name ?: '-' }}</td>
                            <td>{{ $milestone->wbsItem ? $milestone->wbsItem->title : '-' }}</td>
                            <td>
                                {{ $milestone->start_date ? $milestone->start_date->format('d M Y') : '-' }} 
                                s/d 
                                {{ $milestone->end_date ? $milestone->end_date->format('d M Y') : '-' }}
                            </td>
                            <td style="text-align: center;">{{ $milestone->duration_days }} Hari</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <table class="grid-2">
        <tr>
            <td>
                <div class="section">
                    <div class="section-title">Asumsi &amp; Batasan</div>
                    <div style="margin-bottom: 8px;">
                        <span class="metadata-label" style="font-size: 8px;">Asumsi</span>
                        <div class="section-content" style="background-color: #F8FAFC; padding: 8px; border: 1px solid #F1F5F9; border-radius: 4px;">{{ $charter->assumptions ?: 'Tidak ada detail asumsi.' }}</div>
                    </div>
                    <div>
                        <span class="metadata-label" style="font-size: 8px;">Batasan</span>
                        <div class="section-content" style="background-color: #F8FAFC; padding: 8px; border: 1px solid #F1F5F9; border-radius: 4px;">{{ $charter->constraints ?: 'Tidak ada detail batasan.' }}</div>
                    </div>
                </div>
            </td>
            <td class="last">
                <div class="section">
                    <div class="section-title">Pemangku Kepentingan Utama</div>
                    <div class="section-content" style="background-color: #F8FAFC; padding: 10px; border: 1px solid #F1F5F9; border-radius: 4px; min-height: 110px;">{{ $charter->stakeholder_summary ?: 'Tidak ada ringkasan pemangku kepentingan.' }}</div>
                </div>
            </td>
        </tr>
    </table>

    @if($charter->feedback_notes)
        <div class="section" style="background-color: #FFFBEB; border: 1px solid #FDE68A; padding: 12px; border-radius: 6px;">
            <div class="section-title" style="color: #92400E; border-bottom-color: #FDE68A; font-size: 11px;">Catatan &amp; Umpan Balik Manager</div>
            <div class="section-content" style="color: #78350F;">{{ $charter->feedback_notes }}</div>
        </div>
    @endif

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh sistem KelolaIN Project Management. Halaman 1 dari 1
    </div>
</body>
</html>
