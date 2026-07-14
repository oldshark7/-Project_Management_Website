<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\BudgetPlan;
use App\Models\BudgetItem;
use App\Models\BudgetExpense;
use App\Services\CostInsightService;

class CostController extends Controller
{
    public function index()
    {
        $projects = Project::with(['owner', 'projectManager'])
            ->select(
                'id',
                'title',
                'owner_id',
                'manager_id',
                'start_date',
                'end_date'
            )
            ->get();

        return view('project-monitoring.cost-control.index', compact('projects'));
    }

    public function show(Project $project)
    {
        $projects = Project::with(['owner', 'projectManager'])
            ->select(
                'id',
                'title',
                'owner_id',
                'manager_id',
                'start_date',
                'end_date'
            )
            ->get();

        $budgetPlan = \App\Models\BudgetPlan::where('project_id', $project->id)
            ->where('status', 'finalized')
            ->first();

        if (!$budgetPlan) {
            return view('project-monitoring.cost-control.show', [
                'project' => $project,
                'planned' => 0,
                'actual' => 0,
                'remaining' => 0,
                'usage' => 0,
                'breakdown' => collect(),
                'alerts' => ['Belum ada budget plan finalized'],
            ]);
        }

        $budgetItems = \App\Models\BudgetItem::where('budget_plan_id', $budgetPlan->id)->get();
        $planned = $budgetPlan->total_budget;
        $budgetItems = BudgetItem::with('expenses')
            ->where('budget_plan_id', $budgetPlan->id)
            ->get();
        $actual = $budgetItems->sum(function ($item) {
            return $item->expenses->sum('amount');
        });
        $remaining = $planned - $actual;
        $usage = $planned > 0 ? ($actual / $planned) * 100 : 0;
        $breakdown = $budgetItems->map(function ($item) {

            $actual = $item->expenses->sum('amount');

            return [
                'id' => $item->id,
                'category' => $item->category,
                'description' => $item->description,
                'planned' => $item->total_cost,
                'actual' => $actual,
                'variance' => $item->total_cost - $actual,
            ];
        });

        $alerts = [];

        foreach ($breakdown as $category => $data) {
            if ($data['actual'] > $data['planned']) {
                $alerts[] = "$category melebihi budget";
            }
            if ($data['planned'] > 0 && ($data['actual'] / $data['planned']) > 0.8) {
                $alerts[] = "$category telah menggunakan lebih dari 80% budget";
            }
        }

        $categories = [
            'software' => ['label' => 'Software',],
            'hardware' => ['label' => 'Hardware',],
            'operational' => ['label' => 'Operational',],
            'transportation' => ['label' => 'Transportation',],
            'training' => ['label' => 'Training',],
            'other' => ['label' => 'Other',],
        ];

        return view('project-monitoring.cost-control.show', compact(
            'project',
            'planned',
            'actual',
            'remaining',
            'usage',
            'breakdown',
            'alerts',
            'categories'
        ));
    }

    public function storeItem(Request $request, Project $project)
    {
        $validated = $request->validate([
            'category'    => 'required|string',
            'description' => 'required|string|max:255',
            'quantity'    => 'required|integer|min:1',
            'unit'        => 'required|string|max:50',
            'unit_cost'   => 'required|numeric|min:0',
            'notes'       => 'nullable|string',
        ]);

        $budgetPlan = BudgetPlan::where('project_id', $project->id)->where('status', 'finalized')->first();

        if (!$budgetPlan) {
            return back()->with(
                'error',
                'Budget Plan belum tersedia atau belum Finalized.'
            );
        }

        BudgetItem::create([
            'budget_plan_id' => $budgetPlan->id,
            'category'       => $validated['category'],
            'description'    => $validated['description'],
            'quantity'       => $validated['quantity'],
            'unit'           => $validated['unit'],
            'unit_cost'      => $validated['unit_cost'],
            'total_cost'     => $validated['quantity'] * $validated['unit_cost'],
            'notes'          => $validated['notes'],
            'created_by'     => auth()->id(),
            'updated_by'     => auth()->id(),
        ]);

        return back()->with('success', 'Budget item berhasil ditambahkan.');
    }

    public function storeExpense(Request $request, Project $project)
    {
        $validated = $request->validate([
            'budget_item_id' => 'required|exists:budget_items,id',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $budgetItem = BudgetItem::findOrFail($validated['budget_item_id']);

        BudgetExpense::create([
            'budget_item_id' => $budgetItem->id,
            // isi title mengikuti budget item
            'title' => $budgetItem->category,
            'description' => $budgetItem->description,
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'created_by' => auth()->id(),
        ]);

        return back()->with(
            'success',
            'Expense berhasil dicatat.'
        );
    }

    public function generateInsight(Project $project, CostInsightService $costInsightService) {
        $result = $costInsightService->analyze($project);
        // return response()->json($result);

        if (!$result['success']) {

            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => json_decode($result['data'], true)
        ]);
    }
}
