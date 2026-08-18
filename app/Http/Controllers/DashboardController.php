<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Get cash accounts
        $cashAccounts = CashAccount::all();
        // Get summary data
        $totalCashBalance = $cashAccounts->sum('current_balance');
        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');

        // Get recent transactions
        $recentTransactions = Transaction::with(['cashAccount', 'category'])
            ->latest('transaction_date')
            ->take(5)
            ->get();

        // Get monthly income and expense for the last 6 months
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();

        $monthlyStats = Transaction::select(
                DB::raw('YEAR(transaction_date) as year'),
                DB::raw('MONTH(transaction_date) as month'),
                DB::raw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income'),
                DB::raw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense')
            )
            ->where('transaction_date', '>=', $sixMonthsAgo)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Format the monthly stats for the chart
        $chartLabels = [];
        $incomeData = [];
        $expenseData = [];

        foreach ($monthlyStats as $stat) {
            $date = Carbon::createFromDate($stat->year, $stat->month, 1);
            $chartLabels[] = $date->format('M Y');
            $incomeData[] = $stat->income;
            $expenseData[] = $stat->expense;
        }

        return Inertia::render('Dashboard', [
            'summary' => [
                'totalCashBalance' => $totalCashBalance,
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'netBalance' => $totalIncome - $totalExpense
            ],
            'recentTransactions' => $recentTransactions,
            'cashAccounts' => $cashAccounts,
            'chartData' => [
                'labels' => $chartLabels,
                'incomeData' => $incomeData,
                'expenseData' => $expenseData
            ]
        ]);
    }

    /**
     * Display the financial reports.
     */
    public function reports(Request $request)
    {
        $user = Auth::user();

        // Get filter parameters
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $cashAccountId = $request->input('cash_account_id');
        $type = $request->input('type');

        // Build the query
        $query = Transaction::with(['cashAccount', 'category'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->latest('transaction_date');

        if ($cashAccountId) {
            $query->where('cash_account_id', $cashAccountId);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->get();

        // Calculate totals
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');

        // Get category breakdown
        $categoryBreakdown = $transactions
            ->groupBy('transaction_category_id')
            ->map(function ($items, $categoryId) {
                $category = $items->first()->category;
                return [
                    'category' => $category,
                    'total' => $items->sum('amount'),
                    'count' => $items->count()
                ];
            })
            ->sortByDesc('total')
            ->values();

        // Get cash accounts for the filter
        $cashAccounts = CashAccount::all();

        return Inertia::render('Reports', [
            'filters' => [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'cashAccountId' => $cashAccountId,
                'type' => $type
            ],
            'cashAccounts' => $cashAccounts,
            'transactions' => $transactions,
            'summary' => [
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'netBalance' => $totalIncome - $totalExpense
            ],
            'categoryBreakdown' => $categoryBreakdown
        ]);
    }

    public function profitLoss(Request $request)
    {
        $filters = $this->buildReportFilters($request);
        $transactions = $this->filteredTransactions($filters);

        $totalIncome = (float) $transactions->where('type', 'income')->sum('amount');
        $totalExpense = (float) $transactions->where('type', 'expense')->sum('amount');

        return Inertia::render('Reports/ProfitLoss', [
            'filters' => $filters,
            'cashAccounts' => CashAccount::all(),
            'summary' => [
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'netProfit' => $totalIncome - $totalExpense,
            ],
            'incomeBreakdown' => $this->buildCategoryBreakdown($transactions, 'income'),
            'expenseBreakdown' => $this->buildCategoryBreakdown($transactions, 'expense'),
        ]);
    }

    public function balanceSheet(Request $request)
    {
        $filters = $this->buildReportFilters($request);
        $cashAccounts = CashAccount::query()
            ->when($filters['cashAccountId'], fn ($query) => $query->where('id', $filters['cashAccountId']))
            ->get();

        $totalAssets = (float) $cashAccounts->sum('current_balance');
        $liabilities = 0.0;
        $equity = max($totalAssets - $liabilities, 0);

        return Inertia::render('Reports/BalanceSheet', [
            'filters' => $filters,
            'cashAccounts' => CashAccount::all(),
            'summary' => [
                'totalAssets' => $totalAssets,
                'totalLiabilities' => $liabilities,
                'totalEquity' => $equity,
                'netWorth' => $equity,
            ],
            'assets' => $cashAccounts->map(function ($account) {
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'value' => (float) $account->current_balance,
                ];
            }),
        ]);
    }

    public function cashFlow(Request $request)
    {
        $filters = $this->buildReportFilters($request);
        $transactions = $this->filteredTransactions($filters);

        $cashIn = (float) $transactions->where('type', 'income')->sum('amount');
        $cashOut = (float) $transactions->where('type', 'expense')->sum('amount');

        return Inertia::render('Reports/CashFlow', [
            'filters' => $filters,
            'cashAccounts' => CashAccount::all(),
            'summary' => [
                'cashIn' => $cashIn,
                'cashOut' => $cashOut,
                'netCashFlow' => $cashIn - $cashOut,
            ],
            'cashInTransactions' => $this->buildTransactionBreakdown($transactions, 'income'),
            'cashOutTransactions' => $this->buildTransactionBreakdown($transactions, 'expense'),
        ]);
    }

    private function buildReportFilters(Request $request): array
    {
        return [
            'startDate' => $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d')),
            'endDate' => $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d')),
            'cashAccountId' => $request->input('cash_account_id'),
        ];
    }

    private function filteredTransactions(array $filters)
    {
        $query = Transaction::with(['cashAccount', 'category'])
            ->whereBetween('transaction_date', [$filters['startDate'], $filters['endDate']])
            ->latest('transaction_date');

        if ($filters['cashAccountId']) {
            $query->where('cash_account_id', $filters['cashAccountId']);
        }

        return $query->get();
    }

    private function buildCategoryBreakdown($transactions, string $type): array
    {
        return $transactions
            ->where('type', $type)
            ->groupBy('transaction_category_id')
            ->map(function ($items) {
                $category = $items->first()->category;

                return [
                    'category' => $category ? $category->name : 'Uncategorized',
                    'color' => $category ? $category->color : '#94a3b8',
                    'total' => (float) $items->sum('amount'),
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    private function buildTransactionBreakdown($transactions, string $type): array
    {
        return $transactions
            ->where('type', $type)
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'date' => $transaction->transaction_date->format('Y-m-d'),
                    'description' => $transaction->description ?? 'No description',
                    'category' => $transaction->category?->name ?? 'Uncategorized',
                    'account' => $transaction->cashAccount?->name ?? 'N/A',
                    'amount' => (float) $transaction->amount,
                ];
            })
            ->values()
            ->all();
    }
}
