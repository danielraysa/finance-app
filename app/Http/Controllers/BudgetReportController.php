<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BudgetReportController extends Controller
{
    /**
     * Display the budget report dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get active budgets
        $activeBudgets = $user->budgets()
            ->where('is_active', true)
            ->orderBy('end_date', 'desc')
            ->get();

        // Get current budget (most recent active budget)
        $currentBudget = $activeBudgets->first();

        // Budget overview data
        $budgetOverview = null;
        if ($currentBudget) {
            // Update actual amounts for the current budget
            $this->updateBudgetActualAmounts($currentBudget);

            $budgetOverview = [
                'id' => $currentBudget->id,
                'name' => $currentBudget->name,
                'period_type' => $currentBudget->period_type,
                'start_date' => $currentBudget->start_date->format('Y-m-d'),
                'end_date' => $currentBudget->end_date->format('Y-m-d'),
                'total_planned' => $currentBudget->calculateTotalPlannedAmount(),
                'total_actual' => $currentBudget->calculateTotalActualAmount(),
                'progress_percentage' => $this->calculateProgressPercentage($currentBudget),
                'days_remaining' => $this->calculateDaysRemaining($currentBudget),
            ];
        }

        // Budget comparison data (for last 3 budgets)
        $budgetComparison = $this->getBudgetComparisonData($user);

        // Category spending distribution for current budget
        $categoryDistribution = $this->getCategoryDistributionData($currentBudget);

        return Inertia::render('BudgetReports/Index', [
            'budgetOverview' => $budgetOverview,
            'budgetComparison' => $budgetComparison,
            'categoryDistribution' => $categoryDistribution,
            'activeBudgets' => $activeBudgets->map(function ($budget) {
                return [
                    'id' => $budget->id,
                    'name' => $budget->name,
                    'period_type' => $budget->period_type,
                    'start_date' => $budget->start_date->format('Y-m-d'),
                    'end_date' => $budget->end_date->format('Y-m-d'),
                ];
            }),
        ]);
    }

    /**
     * Display detailed report for a specific budget.
     */
    public function show(Budget $budget)
    {
        // Ensure the user can only view their own budgets
        $this->authorize('view', $budget);

        // Update actual amounts for the budget
        $this->updateBudgetActualAmounts($budget);

        $budget->load(['budgetItems.category']);

        // Get daily spending trend for this budget period
        $dailySpending = $this->getDailySpendingData($budget);

        // Get category breakdown
        $categoryBreakdown = $this->getCategoryBreakdownData($budget);

        // Get top spending categories
        $topSpendingCategories = $this->getTopSpendingCategories($budget);

        return Inertia::render('BudgetReports/Show', [
            'budget' => [
                'id' => $budget->id,
                'name' => $budget->name,
                'period_type' => $budget->period_type,
                'start_date' => $budget->start_date->format('Y-m-d'),
                'end_date' => $budget->end_date->format('Y-m-d'),
                'description' => $budget->description,
                'total_planned' => $budget->calculateTotalPlannedAmount(),
                'total_actual' => $budget->calculateTotalActualAmount(),
                'progress_percentage' => $this->calculateProgressPercentage($budget),
                'days_remaining' => $this->calculateDaysRemaining($budget),
            ],
            'budgetItems' => $budget->budgetItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'category_name' => $item->category->name,
                    'category_type' => $item->category->type,
                    'category_color' => $item->category->color,
                    'planned_amount' => $item->planned_amount,
                    'actual_amount' => $item->actual_amount,
                    'percentage_used' => $item->percentage_used,
                    'remaining_amount' => $item->remaining_amount,
                    'notes' => $item->notes,
                ];
            }),
            'dailySpending' => $dailySpending,
            'categoryBreakdown' => $categoryBreakdown,
            'topSpendingCategories' => $topSpendingCategories,
        ]);
    }

    /**
     * Generate a PDF report for a specific budget.
     */
    public function generatePdf(Budget $budget)
    {
        // Ensure the user can only view their own budgets
        $this->authorize('view', $budget);

        // Update actual amounts for the budget
        $this->updateBudgetActualAmounts($budget);

        $budget->load(['budgetItems.category', 'user']);

        // Get category breakdown
        $categoryBreakdown = $this->getCategoryBreakdownData($budget);

        // Generate PDF report using a package like barryvdh/laravel-dompdf
        // This is a placeholder for the actual PDF generation code
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('reports.budget-pdf', [
            'budget' => $budget,
            'totalPlanned' => $budget->calculateTotalPlannedAmount(),
            'totalActual' => $budget->calculateTotalActualAmount(),
            'progressPercentage' => $this->calculateProgressPercentage($budget),
            'daysRemaining' => $this->calculateDaysRemaining($budget),
            'categoryBreakdown' => $categoryBreakdown,
        ]);

        // Return raw PDF bytes with attachment headers to force download
        $filename = 'budget-report-' . $budget->id . '.pdf';
        $output = $pdf->output();

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($output),
        ]);
    }

    /**
     * Update the actual amounts for all budget items in a budget.
     */
    private function updateBudgetActualAmounts(Budget $budget)
    {
        if (!$budget) return;

        $budget->load('budgetItems');
        $user = Auth::user();
        foreach ($budget->budgetItems as $item) {
            $actualAmount = DB::table('transactions')
                ->where('user_id', $user->id)
                ->where('transaction_category_id', $item->transaction_category_id)
                ->whereBetween('transaction_date', [$budget->start_date, $budget->end_date])
                ->sum('amount');

            $item->actual_amount = $actualAmount;
            $item->save();
        }
    }

    /**
     * Calculate the percentage of the budget period that has elapsed.
     */
    private function calculateProgressPercentage(Budget $budget)
    {
        if (!$budget) return 0;

        $startDate = Carbon::parse($budget->start_date);
        $endDate = Carbon::parse($budget->end_date);
        $today = Carbon::today();

        if ($today->lt($startDate)) {
            return 0;
        }

        if ($today->gt($endDate)) {
            return 100;
        }

        $totalDays = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end dates
        $daysElapsed = $startDate->diffInDays($today) + 1;

        return min(100, round(($daysElapsed / $totalDays) * 100, 2));
    }

    /**
     * Calculate the number of days remaining in the budget period.
     */
    private function calculateDaysRemaining(Budget $budget)
    {
        if (!$budget) return 0;

        $endDate = Carbon::parse($budget->end_date);
        $today = Carbon::today();

        if ($today->gt($endDate)) {
            return 0;
        }

        return $today->diffInDays($endDate);
    }

    /**
     * Get budget comparison data for the last 3 budgets.
     */
    private function getBudgetComparisonData($user)
    {
        $recentBudgets = $user->budgets()
            ->orderBy('end_date', 'desc')
            ->limit(3)
            ->get();

        $comparisonData = [];

        foreach ($recentBudgets as $budget) {
            $this->updateBudgetActualAmounts($budget);

            $comparisonData[] = [
                'id' => $budget->id,
                'name' => $budget->name,
                'period_type' => $budget->period_type,
                'start_date' => $budget->start_date->format('Y-m-d'),
                'end_date' => $budget->end_date->format('Y-m-d'),
                'total_planned' => $budget->calculateTotalPlannedAmount(),
                'total_actual' => $budget->calculateTotalActualAmount(),
            ];
        }

        return $comparisonData;
    }

    /**
     * Get category distribution data for a budget.
     */
    private function getCategoryDistributionData($budget)
    {
        if (!$budget) return [];

        $budget->load(['budgetItems.category']);

        $distributionData = [];

        foreach ($budget->budgetItems as $item) {
            $distributionData[] = [
                'category_name' => $item->category->name,
                'category_color' => $item->category->color ?? '#' . substr(md5($item->category->name), 0, 6),
                'planned_amount' => $item->planned_amount,
                'actual_amount' => $item->actual_amount,
                'percentage' => $item->percentage_used,
            ];
        }

        return $distributionData;
    }

    /**
     * Get daily spending data for a budget period.
     */
    private function getDailySpendingData(Budget $budget)
    {
        if (!$budget) return [];
        $user = Auth::user();
        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('transaction_date', [$budget->start_date, $budget->end_date])
            ->orderBy('transaction_date')
            ->get();

        $dailySpending = [];
        $currentDate = Carbon::parse($budget->start_date);
        $endDate = Carbon::parse($budget->end_date);

        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->format('Y-m-d');
            $dailyAmount = $transactions->where('transaction_date', $dateString)->sum('amount');

            $dailySpending[] = [
                'date' => $dateString,
                'amount' => $dailyAmount,
            ];

            $currentDate->addDay();
        }

        return $dailySpending;
    }

    /**
     * Get category breakdown data for a budget.
     */
    private function getCategoryBreakdownData(Budget $budget)
    {
        if (!$budget) return [];

        $budget->load(['budgetItems.category']);

        $breakdownData = [];

        foreach ($budget->budgetItems as $item) {
            $breakdownData[] = [
                'category_name' => $item->category->name,
                'category_type' => $item->category->type,
                'category_color' => $item->category->color ?? '#' . substr(md5($item->category->name), 0, 6),
                'planned_amount' => $item->planned_amount,
                'actual_amount' => $item->actual_amount,
                'percentage_used' => $item->percentage_used,
                'remaining_amount' => $item->remaining_amount,
            ];
        }

        return $breakdownData;
    }

    /**
     * Get top spending categories for a budget.
     */
    private function getTopSpendingCategories(Budget $budget)
    {
        if (!$budget) return [];

        $budget->load(['budgetItems.category']);

        $topCategories = $budget->budgetItems
            ->sortByDesc('actual_amount')
            ->take(5)
            ->map(function ($item) use ($budget) {
                return [
                    'category_name' => $item->category->name,
                    'category_color' => $item->category->color ?? '#' . substr(md5($item->category->name), 0, 6),
                    'actual_amount' => $item->actual_amount,
                    'percentage_of_total' => $item->actual_amount > 0
                        ? round(($item->actual_amount / $budget->calculateTotalActualAmount()) * 100, 2)
                        : 0,
                ];
            })
            ->values()
            ->toArray();

        return $topCategories;
    }
}
