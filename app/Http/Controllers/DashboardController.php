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
        
        // Get summary data
        $totalCashBalance = $user->cashAccounts()->sum('current_balance');
        $totalIncome = $user->transactions()->where('type', 'income')->sum('amount');
        $totalExpense = $user->transactions()->where('type', 'expense')->sum('amount');
        
        // Get recent transactions
        $recentTransactions = $user->transactions()
            ->with(['cashAccount', 'category'])
            ->latest('transaction_date')
            ->take(5)
            ->get();
            
        // Get cash accounts
        $cashAccounts = $user->cashAccounts()->get();
        
        // Get monthly income and expense for the last 6 months
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        
        $monthlyStats = $user->transactions()
            ->select(
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
        $query = $user->transactions()
            ->with(['cashAccount', 'category'])
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
        $cashAccounts = $user->cashAccounts()->get();
        
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
}
