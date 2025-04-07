<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $budgets = $request->user()->budgets()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->period_type, function ($query, $period_type) {
                $query->where('period_type', $period_type);
            })
            ->when($request->is_active !== null, function ($query) use ($request) {
                $query->where('is_active', $request->is_active);
            })
            ->orderBy('start_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Budgets/Index', [
            'budgets' => $budgets,
            'filters' => $request->only(['search', 'period_type', 'is_active']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = auth()->user()->transactionCategories()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Budgets/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'period_type' => 'required|in:monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'budget_items' => 'required|array|min:1',
            'budget_items.*.transaction_category_id' => 'required|exists:transaction_categories,id',
            'budget_items.*.planned_amount' => 'required|numeric|min:0',
            'budget_items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $budget = new Budget([
                'user_id' => $request->user()->id,
                'name' => $request->name,
                'period_type' => $request->period_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'is_active' => true,
            ]);

            $budget->save();

            $totalAmount = 0;

            foreach ($request->budget_items as $item) {
                $budgetItem = new BudgetItem([
                    'budget_id' => $budget->id,
                    'transaction_category_id' => $item['transaction_category_id'],
                    'planned_amount' => $item['planned_amount'],
                    'actual_amount' => 0, // Initial actual amount is 0
                    'notes' => $item['notes'] ?? null,
                ]);

                $budgetItem->save();
                $totalAmount += $item['planned_amount'];
            }

            $budget->total_amount = $totalAmount;
            $budget->save();

            DB::commit();

            return redirect()->route('budgets.show', $budget->id)
                ->with('success', 'Budget created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create budget. ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Budget $budget)
    {
        // Ensure the user can only view their own budgets
        $this->authorize('view', $budget);

        $budget->load(['budgetItems.category']);

        // Calculate actual amounts for each budget item based on transactions
        foreach ($budget->budgetItems as $item) {
            $actualAmount = DB::table('transactions')
                ->where('user_id', auth()->id())
                ->where('transaction_category_id', $item->transaction_category_id)
                ->whereBetween('transaction_date', [$budget->start_date, $budget->end_date])
                ->sum('amount');
            
            $item->actual_amount = $actualAmount;
            $item->save();
        }
        foreach ($budget->budgetItems as $item) {
            $item->remaining_amount = $item->planned_amount - $item->actual_amount;
            $item->percentage_used = $item->actual_amount / $item->planned_amount * 100;
        }

        return Inertia::render('Budgets/Show', [
            'budget' => $budget,
            'totalPlanned' => $budget->calculateTotalPlannedAmount(),
            'totalActual' => $budget->calculateTotalActualAmount(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Budget $budget)
    {
        // Ensure the user can only edit their own budgets
        $this->authorize('update', $budget);

        $budget->load('budgetItems');
        
        $categories = auth()->user()->transactionCategories()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Budgets/Edit', [
            'budget' => $budget,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Budget $budget)
    {
        // Ensure the user can only update their own budgets
        $this->authorize('update', $budget);

        $request->validate([
            'name' => 'required|string|max:255',
            'period_type' => 'required|in:monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'budget_items' => 'required|array|min:1',
            'budget_items.*.id' => 'nullable|exists:budget_items,id',
            'budget_items.*.transaction_category_id' => 'required|exists:transaction_categories,id',
            'budget_items.*.planned_amount' => 'required|numeric|min:0',
            'budget_items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $budget->update([
                'name' => $request->name,
                'period_type' => $request->period_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'is_active' => $request->is_active,
            ]);

            // Delete existing budget items not in the request
            $existingItemIds = collect($request->budget_items)
                ->pluck('id')
                ->filter()
                ->toArray();

            $budget->budgetItems()
                ->whereNotIn('id', $existingItemIds)
                ->delete();

            $totalAmount = 0;

            foreach ($request->budget_items as $item) {
                if (isset($item['id'])) {
                    // Update existing budget item
                    $budgetItem = BudgetItem::find($item['id']);
                    $budgetItem->update([
                        'transaction_category_id' => $item['transaction_category_id'],
                        'planned_amount' => $item['planned_amount'],
                        'notes' => $item['notes'] ?? null,
                    ]);
                } else {
                    // Create new budget item
                    $budgetItem = new BudgetItem([
                        'budget_id' => $budget->id,
                        'transaction_category_id' => $item['transaction_category_id'],
                        'planned_amount' => $item['planned_amount'],
                        'actual_amount' => 0,
                        'notes' => $item['notes'] ?? null,
                    ]);
                    $budgetItem->save();
                }

                $totalAmount += $item['planned_amount'];
            }

            $budget->total_amount = $totalAmount;
            $budget->save();

            DB::commit();

            return redirect()->route('budgets.show', $budget->id)
                ->with('success', 'Budget updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update budget. ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Budget $budget)
    {
        // Ensure the user can only delete their own budgets
        $this->authorize('delete', $budget);

        try {
            $budget->delete();
            return redirect()->route('budgets.index')
                ->with('success', 'Budget deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete budget. ' . $e->getMessage()]);
        }
    }
}
