<?php

namespace App\Jobs;

use App\Models\Issue;
use App\Models\ProjectScope;
use App\Models\RiskItem;
use App\Models\RiskManagementPlan;
use App\Models\WbsItem;
use App\Services\RiskSuggestionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateRiskSuggestionJob implements ShouldQueue
{
    use Queueable;

    public int $projectId;

    public $timeout = 360;
    public $tries = 2;

    public function __construct(int $projectId)
    {
        $this->projectId = $projectId;
    }

    public function handle(RiskSuggestionService $service)
    {
        $cacheKey = "risk_suggestion_{$this->projectId}";
        $lockKey  = "risk_suggestion_processing_{$this->projectId}";

        try {
            Log::info("RiskSuggestionJob started", [
                'projectId' => $this->projectId
            ]);

            $projectScope = ProjectScope::where('project_id', $this->projectId)->first();
            $tasks = WbsItem::where('project_id', $this->projectId)->get();
            $issues = Issue::where('project_id', $this->projectId)->get();

            $riskPlan = RiskManagementPlan::where('project_id', $this->projectId)->first();

            $risks = collect();
            if ($riskPlan) {
                $risks = RiskItem::where('risk_management_plan_id', $riskPlan->id)->get();
            }

            Log::info('Risk AI INPUT CHECK', [
                'projectId' => $this->projectId,
                'scope_exists' => !is_null($projectScope),
                'tasks_count' => $tasks->count(),
                'issues_count' => $issues->count(),
                'risks_count' => $risks->count(),
            ]);

            $result = $service->generate(
                $projectScope,
                $tasks,
                $issues,
                $risks
            );

            // safety check
            if (empty($result)) {
                Log::warning("RiskSuggestionJob returned empty result", [
                    'projectId' => $this->projectId
                ]);
                return;
            }

            Cache::put($cacheKey, $result, now()->addDays(7));

            Log::info("RiskSuggestionJob finished", [
                'projectId' => $this->projectId
            ]);
        } catch (\Throwable $e) {
            Log::error("RiskSuggestionJob failed", [
                'projectId' => $this->projectId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // penting: biar queue aware ini gagal
            throw $e;
        } finally {
            Cache::forget($lockKey);
        }
    }

    public function failed(\Throwable $exception)
    {
        $lockKey = "risk_suggestion_processing_{$this->projectId}";
        Cache::forget($lockKey);

        Log::error("RiskSuggestionJob permanently failed", [
            'projectId' => $this->projectId,
            'message' => $exception->getMessage(),
        ]);
    }
}
