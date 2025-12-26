<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\CashAccount;
use App\Models\TransactionCategory;
use App\Models\CashFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Transaction::with(['cashAccount', 'category'])
            ->latest('transaction_date')
            ->paginate(10);

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cashAccounts = CashAccount::where('is_active', true)
            ->get();

        $categories = TransactionCategory::where('is_active', true)
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
        // If payload contains multiple transactions, treat as a CashFlow master + details
        if ($request->has('transactions') && is_array($request->input('transactions'))) {
            $validatedMaster = $request->validate([
                'transaction_date' => 'required|date',
                'reference_number' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'attachment' => 'nullable|file',
                'transactions' => 'required|array|min:1',
                'transactions.*.cash_account_id' => 'required|exists:cash_accounts,id',
                'transactions.*.transaction_category_id' => 'required|exists:transaction_categories,id',
                'transactions.*.type' => 'required|in:income,expense',
                'transactions.*.amount' => 'required|numeric|min:0',
                'transactions.*.reference_number' => 'nullable|string|max:255',
                'transactions.*.description' => 'nullable|string',
            ]);

            DB::transaction(function () use ($request, $validatedMaster) {
                $attachmentPath = null;
                if ($request->hasFile('attachment')) {
                    $attachmentPath = $request->file('attachment')->store('attachments', 'public');
                }

                $cashFlow = CashFlow::create([
                    'user_id' => Auth::id(),
                    'transaction_date' => $validatedMaster['transaction_date'],
                    'reference_number' => $validatedMaster['reference_number'] ?? null,
                    'description' => $validatedMaster['description'] ?? null,
                    'attachment' => $attachmentPath,
                ]);

                foreach ($validatedMaster['transactions'] as $detail) {
                    // create transaction linked to cash flow
                    $tx = new Transaction([
                        'cash_account_id' => $detail['cash_account_id'],
                        'transaction_category_id' => $detail['transaction_category_id'],
                        'type' => $detail['type'],
                        'amount' => $detail['amount'],
                        'transaction_date' => $validatedMaster['transaction_date'],
                        'reference_number' => $detail['reference_number'] ?? null,
                        'description' => $detail['description'] ?? null,
                    ]);

                    // attach cash_flow_id if column exists on model
                    $tx->setAttribute('cash_flow_id', $cashFlow->id);
                    $tx->save();

                    // Update cash account balance
                    $cashAccount = CashAccount::findOrFail($detail['cash_account_id']);
                    if ($detail['type'] === 'income') {
                        $cashAccount->current_balance += $detail['amount'];
                    } else {
                        $cashAccount->current_balance -= $detail['amount'];
                    }
                    $cashAccount->save();
                }
            });

            return redirect()->route('transactions.index')
                ->with('success', 'Cash flow recorded successfully.');
        }

        // Fallback: single transaction (legacy behavior)
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
        // $this->authorize('view', $transaction);

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
        // $this->authorize('update', $transaction);

        $cashAccounts = CashAccount::where('is_active', true)
            ->get();

        $categories = TransactionCategory::where('is_active', true)
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
        // If updating with multiple details (CashFlow style), replace the single transaction
        if ($request->has('transactions') && is_array($request->input('transactions'))) {
            $validatedMaster = $request->validate([
                'transaction_date' => 'required|date',
                'reference_number' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'attachment' => 'nullable|file',
                'transactions' => 'required|array|min:1',
                'transactions.*.cash_account_id' => 'required|exists:cash_accounts,id',
                'transactions.*.transaction_category_id' => 'required|exists:transaction_categories,id',
                'transactions.*.type' => 'required|in:income,expense',
                'transactions.*.amount' => 'required|numeric|min:0',
                'transactions.*.reference_number' => 'nullable|string|max:255',
                'transactions.*.description' => 'nullable|string',
            ]);

            DB::transaction(function () use ($transaction, $request, $validatedMaster) {
                // Revert and remove the existing transaction
                $oldCashAccount = CashAccount::findOrFail($transaction->cash_account_id);
                if ($transaction->type === 'income') {
                    $oldCashAccount->current_balance -= $transaction->amount;
                } else {
                    $oldCashAccount->current_balance += $transaction->amount;
                }
                $oldCashAccount->save();

                $transaction->delete();

                // Store attachment if provided
                $attachmentPath = null;
                if ($request->hasFile('attachment')) {
                    $attachmentPath = $request->file('attachment')->store('attachments', 'public');
                }

                $cashFlow = CashFlow::create([
                    'user_id' => Auth::id(),
                    'transaction_date' => $validatedMaster['transaction_date'],
                    'reference_number' => $validatedMaster['reference_number'] ?? null,
                    'description' => $validatedMaster['description'] ?? null,
                    'attachment' => $attachmentPath,
                ]);

                foreach ($validatedMaster['transactions'] as $detail) {
                    $tx = new Transaction([
                        'user_id' => Auth::id(),
                        'cash_account_id' => $detail['cash_account_id'],
                        'transaction_category_id' => $detail['transaction_category_id'],
                        'type' => $detail['type'],
                        'amount' => $detail['amount'],
                        'transaction_date' => $validatedMaster['transaction_date'],
                        'reference_number' => $detail['reference_number'] ?? null,
                        'description' => $detail['description'] ?? null,
                    ]);
                    $tx->setAttribute('cash_flow_id', $cashFlow->id);
                    $tx->save();

                    $cashAccount = CashAccount::findOrFail($detail['cash_account_id']);
                    if ($detail['type'] === 'income') {
                        $cashAccount->current_balance += $detail['amount'];
                    } else {
                        $cashAccount->current_balance -= $detail['amount'];
                    }
                    $cashAccount->save();
                }
            });

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction(s) updated successfully.');
        }

        // Legacy single-transaction update
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
