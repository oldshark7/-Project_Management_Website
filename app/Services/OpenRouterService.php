<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenRouterService
{

    // core function to call openApi
    public function chat(string $prompt, string $system = null): string
    {
        $apiKey = config('services.openrouter.api_key');
        $baseUrl = config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $model = config('services.openrouter.model', 'openai/gpt-oss-20b:free');

        if (empty($apiKey)) {
            throw new \Exception('API Key OpenRouter belum dikonfigurasi.');
        }

        $messages = [];

        if ($system) {
            $messages[] = [
                'role' => 'system',
                'content' => $system
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $prompt
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => config('app.url'),
            'X-Title' => 'Task Insight',
        ])
        ->timeout(15)
        ->connectTimeout(5)
        ->post(rtrim($baseUrl, '/') . '/chat/completions', [
            'model' => $model,
            'messages' => $messages,
        ]);

        if ($response->failed()) {
            throw new \Exception('OpenRouter error: ' . $response->body());
        }

        return $response->json()['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Generate suggestions for a Project Charter using OpenRouter API.
     *
     * @param Project $project
     * @return string
     * @throws Exception
     */
    public function generateCharterSuggestions(Project $project): string
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }

        $apiKey = config('services.openrouter.api_key');
        $baseUrl = config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $model = config('services.openrouter.model', 'openai/gpt-oss-20b:free');

        if (empty($apiKey)) {
            Log::error('OpenRouter API key is missing or not configured.');
            throw new Exception('API Key OpenRouter belum dikonfigurasi. Silakan periksa file .env Anda.');
        }

        $proposal = $project->proposal;
        $proposalText = '';
        if ($proposal) {
            $proposalText = sprintf(
                "Latar Belakang: %s\nTujuan: %s\nRuang Lingkup: %s",
                $proposal->background ? mb_substr(trim($proposal->background), 0, 500) : '-',
                $proposal->objectives ? mb_substr(trim($proposal->objectives), 0, 500) : '-',
                $proposal->scope_overview ? mb_substr(trim($proposal->scope_overview), 0, 500) : '-'
            );
        } else {
            $proposalText = "Tidak ada proposal.";
        }

        $charter = $project->charter;
        $charterText = '';
        if ($charter) {
            $charterText = sprintf(
                "Draf Charter Saat Ini:\nTujuan Proyek: %s\nKasus Bisnis: %s\nSasaran Proyek: %s\nRingkasan Ruang Lingkup: %s\nKriteria Keberhasilan: %s\nAsumsi: %s\nBatasan: %s\nPemangku Kepentingan: %s\nMilestone: %s\nAnggaran: %s",
                $charter->project_purpose ? mb_substr(trim($charter->project_purpose), 0, 300) : '-',
                $charter->business_case ? mb_substr(trim($charter->business_case), 0, 300) : '-',
                $charter->project_objectives ? mb_substr(trim($charter->project_objectives), 0, 300) : '-',
                $charter->scope_summary ? mb_substr(trim($charter->scope_summary), 0, 300) : '-',
                $charter->success_criteria ? mb_substr(trim($charter->success_criteria), 0, 300) : '-',
                $charter->assumptions ? mb_substr(trim($charter->assumptions), 0, 300) : '-',
                $charter->constraints ? mb_substr(trim($charter->constraints), 0, 300) : '-',
                $charter->stakeholder_summary ? mb_substr(trim($charter->stakeholder_summary), 0, 300) : '-',
                $charter->milestone_summary ? mb_substr(trim($charter->milestone_summary), 0, 300) : '-',
                $charter->budget_summary ? number_format($charter->budget_summary, 2) : '-'
            );
        } else {
            $charterText = "Charter belum diisi.";
        }

        $prompt = <<<EOT
Anda adalah AI Asisten Manajemen Proyek. Bantu Manager menyusun Project Charter berdasarkan informasi proyek berikut:

Nama Proyek: {$project->title}
Deskripsi Proyek: {$project->description}

Informasi Proposal Proyek:
{$proposalText}

{$charterText}

Tolong berikan draf rekomendasi untuk Project Charter. Anda WAJIB menjawab hanya dalam format JSON valid dengan struktur objek berikut:
{
  "project_purpose": "rekomendasi tujuan proyek...",
  "business_case": "rekomendasi kasus bisnis...",
  "project_objectives": "rekomendasi sasaran proyek...",
  "scope_summary": "rekomendasi ringkasan ruang lingkup...",
  "success_criteria": "rekomendasi kriteria keberhasilan...",
  "assumptions": "rekomendasi asumsi...",
  "constraints": "rekomendasi batasan...",
  "stakeholder_summary": "rekomendasi pemangku kepentingan...",
  "milestone_summary": "rekomendasi milestone...",
  "budget_summary": "angka atau penjelasan rekomendasi anggaran..."
}

ATURAN PENTING:
1. JANGAN memberikan teks pembuka atau penutup apa pun diluar JSON. Kembalikan HANYA JSON.
2. JANGAN gunakan tabel Markdown. JANGAN gunakan baris tabel atau visualisasi kolom tabel.
3. Jawab dalam Bahasa Indonesia formal dan profesional.
4. Pastikan teks rekomendasi mudah dipahami dan siap digunakan atau diedit lebih lanjut (jangan menggunakan tabel atau grafik yang sulit di-copypaste).
5. Pastikan semua tanda petik dua di dalam nilai teks di-escape dengan benar agar JSON tetap valid.
EOT;

        return $this->executeOpenRouterCall($prompt, $apiKey, $baseUrl, $model, $project->id);
    }

    /**
     * Generate suggestions for a Project Proposal using OpenRouter API.
     *
     * @param Project $project
     * @return string
     * @throws Exception
     */
    public function generateProposalSuggestions(Project $project): string
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }

        $apiKey = config('services.openrouter.api_key');
        $baseUrl = config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $model = config('services.openrouter.model', 'openai/gpt-oss-20b:free');

        if (empty($apiKey)) {
            Log::error('OpenRouter API key is missing or not configured.');
            throw new Exception('API Key OpenRouter belum dikonfigurasi. Silakan periksa file .env Anda.');
        }

        $proposal = $project->proposal;
        $proposalText = '';
        if ($proposal) {
            $proposalText = sprintf(
                "Latar Belakang saat ini: %s\nTujuan saat ini: %s\nKebutuhan Awal saat ini: %s\nGambaran Umum saat ini: %s\nRuang Lingkup saat ini: %s",
                $proposal->background ? mb_substr(trim($proposal->background), 0, 300) : '-',
                $proposal->objectives ? mb_substr(trim($proposal->objectives), 0, 300) : '-',
                $proposal->initial_needs ? mb_substr(trim($proposal->initial_needs), 0, 300) : '-',
                $proposal->project_overview ? mb_substr(trim($proposal->project_overview), 0, 300) : '-',
                $proposal->scope_overview ? mb_substr(trim($proposal->scope_overview), 0, 300) : '-'
            );
        } else {
            $proposalText = "Proposal belum diisi.";
        }

        $prompt = <<<EOT
Anda adalah AI Asisten Manajemen Proyek. Bantu Manager menyusun Project Proposal berdasarkan informasi proyek berikut:

Nama Proyek: {$project->title}
Deskripsi Proyek: {$project->description}

{$proposalText}

Tolong berikan draf rekomendasi untuk Project Proposal. Anda WAJIB menjawab hanya dalam format JSON valid dengan struktur objek berikut:
{
  "background": "rekomendasi latar belakang proyek...",
  "objectives": "rekomendasi tujuan proyek...",
  "initial_needs": "rekomendasi kebutuhan awal...",
  "project_overview": "rekomendasi gambaran umum proyek...",
  "scope_overview": "rekomendasi gambaran ruang lingkup...",
  "estimated_budget": "rekomendasi perkiraan anggaran dalam bentuk teks atau angka..."
}

ATURAN PENTING:
1. JANGAN memberikan teks pembuka atau penutup apa pun diluar JSON. Kembalikan HANYA JSON.
2. JANGAN gunakan tabel Markdown. JANGAN gunakan baris tabel atau visualisasi kolom tabel.
3. Jawab dalam Bahasa Indonesia formal dan profesional.
4. Pastikan teks rekomendasi mudah dipahami, lugas, dan siap digunakan atau diedit lebih lanjut (jangan menggunakan tabel atau grafik yang sulit di-copypaste).
5. Pastikan semua tanda petik dua di dalam nilai teks di-escape dengan benar agar JSON tetap valid.
EOT;

        return $this->executeOpenRouterCall($prompt, $apiKey, $baseUrl, $model, $project->id);
    }

    /**
     * Generate suggestions for Project Risk Management using OpenRouter API.
     *
     * @param Project $project
     * @return string
     * @throws Exception
     */
    public function generateRiskManagementSuggestions(Project $project): string
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }

        $apiKey = config('services.openrouter.api_key');
        $baseUrl = config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $model = config('services.openrouter.model', 'openai/gpt-oss-20b:free');

        if (empty($apiKey)) {
            Log::error('OpenRouter API key is missing or not configured.');
            throw new Exception('API Key OpenRouter belum dikonfigurasi. Silakan periksa file .env Anda.');
        }

        $scope = $project->scope;
        $scopeText = $scope ? sprintf(
            "Objective: %s\nDescription: %s\nIn Scope: %s\nDeliverables: %s\nConstraints: %s",
            $scope->objective,
            $scope->scope_description,
            $scope->in_scope,
            $scope->deliverables,
            $scope->constraints
        ) : "Belum diisi.";

        $wbsCount = $project->wbsItems()->count();
        $wbsItemsText = $project->wbsItems()->take(15)->pluck('title')->implode(', ');
        $wbsContext = "Total WBS: {$wbsCount} items. Contoh item: {$wbsItemsText}.";

        $timelineCount = $project->timelineItems()->count();
        $timelineContext = "Total Timeline Items: {$timelineCount} tasks scheduled.";

        $totalBudget = $project->budgetPlan ? number_format($project->budgetPlan->total_budget, 2) : '0.00';
        $budgetContext = "Total Budget RAB: {$totalBudget}";

        $hrPlan = $project->humanResourcePlan;
        $hrRoles = $hrPlan ? $hrPlan->humanResourceItems()->pluck('role_name')->unique()->implode(', ') : '-';
        $hrCount = $hrPlan ? $hrPlan->humanResourceItems()->sum('quantity') : 0;
        $hrContext = "Total SDM: {$hrCount} orang. Peran utama: {$hrRoles}.";

        $prompt = <<<EOT
Anda adalah AI Asisten Manajemen Risiko Proyek. Bantu PMO (Project Management Officer) menganalisis potensi risiko proyek berdasarkan konteks proyek berikut:

Nama Proyek: {$project->title}
Deskripsi Proyek: {$project->description}

Konteks Scope Proyek:
{$scopeText}

Konteks WBS Proyek:
{$wbsContext}

Konteks Jadwal (Timeline):
{$timelineContext}

Konteks Anggaran (Budget):
{$budgetContext}

Konteks Sumber Daya Manusia (HR):
{$hrContext}

Tolong berikan draf rekomendasi minimal 3 potensi risiko utama untuk proyek ini beserta rencana penanganannya.
Anda WAJIB menjawab HANYA dalam format JSON valid berupa array dari objek dengan struktur persis seperti berikut:
[
  {
    "risk_title": "Judul Risiko...",
    "risk_description": "Deskripsi potensi risiko secara detail...",
    "risk_cause": "Faktor penyebab risiko terjadi...",
    "impact": "Dampak risiko terhadap biaya/waktu/kualitas...",
    "probability": "low/medium/high",
    "severity": "low/medium/high",
    "mitigation_plan": "Rencana tindakan preventif untuk meminimalisir peluang terjadinya risiko...",
    "contingency_plan": "Rencana tindakan jika risiko tersebut benar-benar terjadi...",
    "risk_owner": "Peran PIC/Pemilik Risiko (misal: Project Manager, Lead Developer)..."
  }
]

ATURAN PENTING:
1. JANGAN memberikan teks pembuka, penjelasan, atau penutup apa pun diluar JSON array. Kembalikan HANYA JSON array valid.
2. Nilai untuk 'probability' dan 'severity' harus tepat berupa string lowercase salah satu dari: 'low', 'medium', atau 'high'.
3. Jawab dalam Bahasa Indonesia formal dan profesional.
4. Pastikan teks rekomendasi mudah dipahami, logis, dan siap digunakan atau disesuaikan.
5. Pastikan semua tanda petik dua di dalam nilai teks di-escape dengan benar agar format JSON tidak rusak.
EOT;

        return $this->executeOpenRouterCall($prompt, $apiKey, $baseUrl, $model, $project->id);
    }

    /**
     * Reusable OpenRouter completion caller helper.
     *
     * @param string $prompt
     * @param string $apiKey
     * @param string $baseUrl
     * @param string $model
     * @param int $projectId
     * @return string
     * @throws Exception
     */
    protected function executeOpenRouterCall(string $prompt, string $apiKey, string $baseUrl, string $model, int $projectId): string
    {
        try {
            $endpoint = rtrim($baseUrl, '/') . '/chat/completions';
            
            $response = Http::connectTimeout(15)
                ->timeout(120)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => config('app.url', 'http://localhost'),
                    'X-Title' => 'Project Management Assistant',
                ])
                ->post($endpoint, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ]
                    ],
                ]);

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->body();
                Log::error('OpenRouter API error status: ' . $status, ['response' => $body]);
                
                if ($status === 401) {
                    throw new Exception('API Key OpenRouter tidak valid atau tidak memiliki akses.');
                }
                if ($status === 429) {
                    throw new Exception('Limit kuota atau rate limit OpenRouter telah habis.');
                }
                if ($status >= 500) {
                    throw new Exception('Server OpenRouter mengalami error internal (status ' . $status . ').');
                }
                
                throw new Exception('Layanan OpenRouter mengembalikan error status ' . $status . '.');
            }

            $responseData = $response->json();
            $suggestion = $responseData['choices'][0]['message']['content'] ?? '';

            if (empty(trim($suggestion))) {
                Log::warning('OpenRouter API returned an empty suggestion.', ['response' => $responseData]);
                throw new Exception('Respons dari AI kosong. Silakan coba lagi.');
            }

            return $suggestion;

        } catch (\Illuminate\Http\Client\ConnectionException $ce) {
            Log::error('OpenRouter connection/timeout error: ' . $ce->getMessage());
            throw new Exception('Koneksi ke OpenRouter terputus atau timeout (batas 120 detik terlampaui).');
        } catch (Exception $e) {
            Log::error('OpenRouter API Exception: ' . $e->getMessage(), [
                'project_id' => $projectId,
                'exception' => $e
            ]);
            throw new Exception($e->getMessage());
        }
    }
}
