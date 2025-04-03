<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CashAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cashAccounts = Auth::user()->cashAccounts()->get();
        
        return Inertia::render('CashAccounts/Index', [
            'cashAccounts' => $cashAccounts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('CashAccounts/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'initial_balance' => 'required|numeric',
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['current_balance'] = $validated['initial_balance'];

        $cashAccount = CashAccount::create($validated);

        return redirect()->route('cash-accounts.index')
            ->with('success', 'Cash account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CashAccount $cashAccount)
    {
        $this->authorize('view', $cashAccount);
        
        $transactions = $cashAccount->transactions()
            ->with('category')
            ->latest('transaction_date')
            ->paginate(10);
            
        return Inertia::render('CashAccounts/Show', [
            'cashAccount' => $cashAccount,
            'transactions' => $transactions
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CashAccount $cashAccount)
    {
        $this->authorize('update', $cashAccount);
        
        return Inertia::render('CashAccounts/Edit', [
            'cashAccount' => $cashAccount
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CashAccount $cashAccount)
    {
        $this->authorize('update', $cashAccount);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $cashAccount->update($validated);

        return redirect()->route('cash-accounts.index')
            ->with('success', 'Cash account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CashAccount $cashAccount)
    {
        $this->authorize('delete', $cashAccount);
        
        $cashAccount->delete();

        return redirect()->route('cash-accounts.index')
            ->with('success', 'Cash account deleted successfully.');
    }
}
