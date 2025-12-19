<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = Account::orderBy('created_at', 'desc')->get();
        $totalBalance = $accounts->sum('initial_balance');

        return view('accounts.index', compact('accounts', 'totalBalance'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Generate auto account number
        $lastAccount = Account::latest('id')->first();
        $accountNo = $lastAccount ? 'ACC-' . str_pad(($lastAccount->id + 1), 6, '0', STR_PAD_LEFT) : 'ACC-000001';

        return view('accounts.create', compact('accountNo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_no' => 'required|string|unique:accounts,account_no',
            'name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'initial_balance' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        // If this account is set as default, remove default from other accounts
        if ($request->has('is_default') && $request->is_default) {
            Account::where('is_default', true)->update(['is_default' => false]);
        }

        Account::create([
            'account_no' => $validated['account_no'],
            'name' => $validated['name'],
            'branch' => $validated['branch'] ?? null,
            'swift_code' => $validated['swift_code'] ?? null,
            'initial_balance' => $validated['initial_balance'] ?? 0,
            'note' => $validated['note'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return redirect()->route('accounts.index')
            ->with('success', 'Account created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        return view('accounts.edit', compact('account'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'account_no' => 'required|string|unique:accounts,account_no,' . $account->id,
            'name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'initial_balance' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        // If this account is set as default, remove default from other accounts
        if ($request->has('is_default') && $request->is_default) {
            Account::where('id', '!=', $account->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $account->update([
            'account_no' => $validated['account_no'],
            'name' => $validated['name'],
            'branch' => $validated['branch'] ?? null,
            'swift_code' => $validated['swift_code'] ?? null,
            'initial_balance' => $validated['initial_balance'] ?? 0,
            'note' => $validated['note'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return redirect()->route('accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'Account deleted successfully.');
    }

    /**
     * Toggle default status
     */
    public function toggleDefault(Account $account)
    {
        // Remove default from all accounts
        Account::where('is_default', true)->update(['is_default' => false]);
        
        // Set this account as default
        $account->update(['is_default' => true]);

        return redirect()->route('accounts.index')
            ->with('success', 'Default account updated successfully.');
    }
}