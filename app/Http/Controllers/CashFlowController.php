<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\CashFlow;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CashFlowController extends Controller
{
    public function index()
    {
        $cashFlows = CashFlow::where('user_id', Auth::id())
            ->with(['transactions.cashAccount', 'transactions.category'])
            ->withSum('transactions', 'amount')
            ->latest('transaction_date')
            ->paginate(10);

        return Inertia::render('CashFlows/Index', [
            'cashFlows' => $cashFlows,
        ]);
    }

    public function create()
    {
        $cashAccounts = Auth::user()->cashAccounts()->where('is_active', true)->get();
        $categories = Auth::user()->transactionCategories()->where('is_active', true)->get();

        return Inertia::render('CashFlows/Create', [
            'cashAccounts' => $cashAccounts,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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

        DB::transaction(function () use ($request, $validated) {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('attachments', 'public');
            }

            $cashFlow = CashFlow::create([
                'user_id' => Auth::id(),
                'transaction_date' => $validated['transaction_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'description' => $validated['description'] ?? null,
                'attachment' => $attachmentPath,
            ]);

            foreach ($validated['transactions'] as $detail) {
                $tx = new Transaction([
                    'user_id' => Auth::id(),
                    'cash_account_id' => $detail['cash_account_id'],
                    'transaction_category_id' => $detail['transaction_category_id'],
                    'type' => $detail['type'],
                    'amount' => $detail['amount'],
                    'transaction_date' => $validated['transaction_date'],
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

        return redirect()->route('cash-flows.index')
            ->with('success', 'Cash flow recorded successfully.');
    }

    public function show(CashFlow $cashFlow)
    {
        $this->authorize('view', $cashFlow);
        $cashFlow->load('transactions.cashAccount', 'transactions.category');

        return Inertia::render('CashFlows/Show', [
            'cashFlow' => $cashFlow,
        ]);
    }

    public function edit(CashFlow $cashFlow)
    {
        $this->authorize('update', $cashFlow);
        $cashAccounts = Auth::user()->cashAccounts()->where('is_active', true)->get();
        $categories = Auth::user()->transactionCategories()->where('is_active', true)->get();

        $cashFlow->load('transactions');

        return Inertia::render('CashFlows/Edit', [
            'cashFlow' => $cashFlow,
            'cashAccounts' => $cashAccounts,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, CashFlow $cashFlow)
    {
        $this->authorize('update', $cashFlow);

        $validated = $request->validate([
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

        DB::transaction(function () use ($request, $validated, $cashFlow) {
            // Revert balances for existing transactions
            foreach ($cashFlow->transactions as $oldTx) {
                $acc = CashAccount::findOrFail($oldTx->cash_account_id);
                if ($oldTx->type === 'income') {
                    $acc->current_balance -= $oldTx->amount;
                } else {
                    $acc->current_balance += $oldTx->amount;
                }
                $acc->save();
            }

            // Delete old transactions
            $cashFlow->transactions()->delete();

            // Handle attachment
            if ($request->hasFile('attachment')) {
                if ($cashFlow->attachment) {
                    Storage::disk('public')->delete($cashFlow->attachment);
                }
                $cashFlow->attachment = $request->file('attachment')->store('attachments', 'public');
            }

            $cashFlow->transaction_date = $validated['transaction_date'];
            $cashFlow->reference_number = $validated['reference_number'] ?? null;
            $cashFlow->description = $validated['description'] ?? null;
            $cashFlow->save();

            // Create new detail transactions and update balances
            foreach ($validated['transactions'] as $detail) {
                $tx = new Transaction([
                    'user_id' => Auth::id(),
                    'cash_account_id' => $detail['cash_account_id'],
                    'transaction_category_id' => $detail['transaction_category_id'],
                    'type' => $detail['type'],
                    'amount' => $detail['amount'],
                    'transaction_date' => $validated['transaction_date'],
                    'reference_number' => $detail['reference_number'] ?? null,
                    'description' => $detail['description'] ?? null,
                ]);
                $tx->setAttribute('cash_flow_id', $cashFlow->id);
                $tx->save();

                $acc = CashAccount::findOrFail($detail['cash_account_id']);
                if ($detail['type'] === 'income') {
                    $acc->current_balance += $detail['amount'];
                } else {
                    $acc->current_balance -= $detail['amount'];
                }
                $acc->save();
            }
        });

        return redirect()->route('cash-flows.index')
            ->with('success', 'Cash flow updated successfully.');
    }

    public function destroy(CashFlow $cashFlow)
    {
        $this->authorize('delete', $cashFlow);

        DB::transaction(function () use ($cashFlow) {
            foreach ($cashFlow->transactions as $tx) {
                $acc = CashAccount::findOrFail($tx->cash_account_id);
                if ($tx->type === 'income') {
                    $acc->current_balance -= $tx->amount;
                } else {
                    $acc->current_balance += $tx->amount;
                }
                $acc->save();
            }

            $cashFlow->transactions()->delete();
            if ($cashFlow->attachment) {
                Storage::disk('public')->delete($cashFlow->attachment);
            }
            $cashFlow->delete();
        });

        return redirect()->route('cash-flows.index')
            ->with('success', 'Cash flow deleted successfully.');
    }
}
