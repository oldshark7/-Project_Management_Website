<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use App\Jobs\GenerateRiskSuggestionJob;

class RiskSuggestionHelper
{
    public static function get(int $projectId)
    {
        $cacheKey = "risk_suggestion_{$projectId}";
        $lockKey  = "risk_suggestion_processing_{$projectId}";

        // Sudah ada hasil AI
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Pasang lock supaya request lain tidak ikut generate
        if (Cache::add($lockKey, true, now()->addMinutes(10))) {
            GenerateRiskSuggestionJob::dispatch($projectId);
        }

        return [
            'loading' => true,
            'summary' => 'AI sedang menganalisis kondisi proyek...',
            'overall_risk_level' => null,
            'predicted_risks' => [],
        ];
    }

    public static function refresh(int $projectId)
    {
        Cache::forget("risk_suggestion_{$projectId}");
        Cache::forget("risk_suggestion_processing_{$projectId}");
        
        GenerateRiskSuggestionJob::dispatch($projectId);
    }

    public static function clear(int $projectId)
    {
        Cache::forget("risk_suggestion_{$projectId}");
        Cache::forget("risk_suggestion_processing_{$projectId}");
    }

    public static function status(int $projectId): array
    {
        $cacheKey = "risk_suggestion_{$projectId}";

        if (Cache::has($cacheKey)) {
            return [
                'loading' => false,
                'data' => Cache::get($cacheKey),
            ];
        }

        return [
            'loading' => true,
            'data' => null,
        ];
    }
}
