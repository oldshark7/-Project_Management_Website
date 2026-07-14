<?php

namespace App\Services;

use App\Models\Project;
use App\Models\BudgetPlan;
use App\Models\BudgetItem;

class CostInsightService
{
    /**
     * Generate AI Cost Insight
     */
    public function analyze(Project $project)
    {
        $budgetPlan = $this->getBudgetPlan($project);

        if (!$budgetPlan) {
            return [
                'success' => false,
                'message' => 'Budget Plan belum tersedia atau belum Finalized.',
            ];
        }

        $budgetItems = $this->getBudgetItems($budgetPlan);

        $summary = $this->calculateSummary(
            $budgetPlan,
            $budgetItems
        );

        $breakdown = $this->buildBreakdown(
            $budgetItems
        );

        $recentExpenses = $this->getRecentExpenses(
            $budgetItems
        );

        $payload = $this->buildPayload(
            $project,
            $summary,
            $breakdown,
            $recentExpenses
        );

        return $this->generateInsight($payload);
    }

    /**
     * Get finalized budget plan
     */
    protected function getBudgetPlan(Project $project)
    {
        return BudgetPlan::where('project_id', $project->id)
            ->where('status', 'finalized')
            ->first();
    }

    /**
     * Get budget items with expenses
     */
    protected function getBudgetItems(BudgetPlan $budgetPlan)
    {
        return BudgetItem::with('expenses')
            ->where('budget_plan_id', $budgetPlan->id)
            ->get();
    }

    /**
     * Calculate project summary
     */
    protected function calculateSummary(
        BudgetPlan $budgetPlan,
        $budgetItems
    ) {
        $planned = $budgetPlan->total_budget;

        $actual = $budgetItems->sum(function ($item) {
            return $item->expenses->sum('amount');
        });

        $remaining = $planned - $actual;

        $usage = $planned > 0
            ? round(($actual / $planned) * 100, 2)
            : 0;

        return [
            'planned_budget' => $planned,
            'actual_cost' => $actual,
            'remaining_budget' => $remaining,
            'usage_percentage' => $usage,
        ];
    }

    /**
     * Build budget breakdown
     */
    protected function buildBreakdown($budgetItems)
    {
        return $budgetItems->map(function ($item) {

            $actual = $item->expenses->sum('amount');

            return [
                'id' => $item->id,
                'category' => $item->category,
                'description' => $item->description,
                'planned_budget' => $item->total_cost,
                'actual_cost' => $actual,
                'remaining_budget' => $item->total_cost - $actual,
                'usage_percentage' => $item->total_cost > 0
                    ? round(($actual / $item->total_cost) * 100, 2)
                    : 0,
            ];
        })->values()->toArray();
    }

    /**
     * Recent expenses
     */
    protected function getRecentExpenses($budgetItems)
    {
        return $budgetItems->flatMap(function ($item) {

            return $item->expenses->map(fn($expense) => [
                'title' => $expense->title,
                'category' => $item->category,
                'amount' => $expense->amount,
                'expense_date' => optional($expense->expense_date)->format('Y-m-d'),
            ]);

        })
        ->sortByDesc('expense_date')
        ->take(10)
        ->values()
        ->toArray();
    }

    /**
     * Payload for AI
     */
    protected function buildPayload(
        Project $project,
        array $summary,
        array $breakdown,
        array $recentExpenses
    ) {

        return [

            'project' => [
                'title' => $project->title,
                'start_date' => optional($project->start_date)->format('Y-m-d'),
                'end_date' => optional($project->end_date)->format('Y-m-d'),
            ],

            'summary' => $summary,

            'budget_breakdown' => $breakdown,

            'recent_expenses' => $recentExpenses,

        ];
    }

    /**
     * Call AI
     */
    protected function generateInsight(array $payload)
    {
        try {

            $openRouter = app(OpenRouterService::class);

            $result = $openRouter->chat(

                json_encode($payload, JSON_PRETTY_PRINT),

                // Prompt nanti kita isi
                'You are an experienced Project Cost Control Analyst.

Analyze the project cost data provided by the user.

Rules:
- Base every conclusion only on the provided data.
- Do not assume missing information.
- Return ONLY valid JSON.
- Do not wrap JSON inside markdown.
- Do not include explanations outside JSON.
- Gunakan bahasa indonesia

Return exactly this schema:

{
  "budget_health": {
    "status": "",
    "color": "",
    "title": "",
    "summary": ""
  },
  "executive_summary": "",
  "key_findings": [
    {
      "severity": "",
      "title": "",
      "description": ""
    }
  ],
  "recommendations": [
    {
      "title": "",
      "description": ""
    }
  ]
}'
            );

            return [
                'success' => true,
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
