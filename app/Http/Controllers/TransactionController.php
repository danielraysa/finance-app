<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\CashAccount;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Auth::user()->transactions()
            ->with(['cashAccount', 'category'])
            ->latest('transaction_date')
            ->paginate(15);
        
        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cashAccounts = Auth::user()->cashAccounts()
            ->where('is_active', true)
            ->get();
            
        $categories = Auth::user()->transactionCategories()
            ->where('is_active', true)
            ->get();
            
        return Inertia::render('Transactions/Create', [
            'cashAccounts' => $cashAccounts,
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cash_account_id' => 'required|exists:cash_accounts,id',
            'transaction_category_id' => 'required|exists:transaction_categories,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'attachment' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        
        // Get the cash account
        $cashAccount = CashAccount::findOrFail($validated['cash_account_id']);
        
        // Update the cash account balance
        if ($validated['type'] === 'income') {
            $cashAccount->current_balance += $validated['amount'];
        } else {
            $cashAccount->current_balance -= $validated['amount'];
        }
        
        $cashAccount->save();
        
        // Create the transaction
        $transaction = Transaction::create($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        
        $transaction->load(['cashAccount', 'category']);
        
        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        $this->authorize('update', $transaction);
        
        $cashAccounts = Auth::user()->cashAccounts()
            ->where('is_active', true)
            ->get();
            
        $categories = Auth::user()->transactionCategories()
            ->where('is_active', true)
            ->get();
            
        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction,
            'cashAccounts' => $cashAccounts,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);
        
        $validated = $request->validate([
            'cash_account_id' => 'required|exists:cash_accounts,id',
            'transaction_category_id' => 'required|exists:transaction_categories,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'attachment' => 'nullable|string',
        ]);
        
        // Revert the old transaction effect on balance
        $oldCashAccount = CashAccount::findOrFail($transaction->cash_account_id);
        if ($transaction->type === 'income') {
            $oldCashAccount->current_balance -= $transaction->amount;
        } else {
            $oldCashAccount->current_balance += $transaction->amount;
        }
        $oldCashAccount->save();
        
        // Apply the new transaction effect on balance
        $newCashAccount = CashAccount::findOrFail($validated['cash_account_id']);
        if ($validated['type'] === 'income') {
            $newCashAccount->current_balance += $validated['amount'];
        } else {
            $newCashAccount->current_balance -= $validated['amount'];
        }
        $newCashAccount->save();
        
        // Update the transaction
        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        
        // Revert the transaction effect on balance
        $cashAccount = CashAccount::findOrFail($transaction->cash_account_id);
        if ($transaction->type === 'income') {
            $cashAccount->current_balance -= $transaction->amount;
        } else {
            $cashAccount->current_balance += $transaction->amount;
        }
        $cashAccount->save();
        
        // Delete the transaction
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }
}
