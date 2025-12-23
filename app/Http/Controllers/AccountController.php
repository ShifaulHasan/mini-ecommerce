<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts
     */
    public function index()
    {
        $accounts = Account::orderBy('created_at', 'desc')->get();

        // 🔥 total current balance
        $totalBalance = $accounts->sum('current_balance');

        return view('accounts.index', compact('accounts', 'totalBalance'));
    }

    /**
     * Show the form for creating a new account
     */
    public function create()
    {
        // Auto account number
        $lastAccount = Account::latest('id')->first();
        $accountNo = $lastAccount
            ? 'ACC-' . str_pad($lastAccount->id + 1, 6, '0', STR_PAD_LEFT)
            : 'ACC-000001';

        return view('accounts.create', compact('accountNo'));
    }

    /**
     * Store a newly created account
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_no'      => 'required|string|unique:accounts,account_no',
            'name'            => 'required|string|max:255',
            'branch'          => 'nullable|string|max:255',
            'swift_code'      => 'nullable|string|max:255',
            'initial_balance' => 'required|numeric|min:0',
            'note'            => 'nullable|string',
            'is_default'      => 'nullable|boolean',
            'status'          => 'nullable|in:active,inactive',
        ]);

        // Handle default account
        if ($request->has('is_default') && $request->is_default) {
            Account::where('is_default', true)->update(['is_default' => false]);
        }

        Account::create([
            'account_no'      => $validated['account_no'],
            'name'            => $validated['name'],
            'branch'          => $validated['branch'] ?? null,
            'swift_code'      => $validated['swift_code'] ?? null,
            'initial_balance' => $validated['initial_balance'],
            'current_balance' => $validated['initial_balance'], // 🔥 IMPORTANT
            'note'            => $validated['note'] ?? null,
            'is_default'      => $validated['is_default'] ?? false,
            'status'          => $validated['status'] ?? 'active',
        ]);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account created successfully.');
    }

    /**
     * Show the form for editing the specified account
     */
    public function edit(Account $account)
    {
        return view('accounts.edit', compact('account'));
    }

    /**
     * Update the specified account
     */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'account_no'      => 'required|string|unique:accounts,account_no,' . $account->id,
            'name'            => 'required|string|max:255',
            'branch'          => 'nullable|string|max:255',
            'swift_code'      => 'nullable|string|max:255',
            'initial_balance' => 'required|numeric|min:0',
            'note'            => 'nullable|string',
            'is_default'      => 'nullable|boolean',
            'status'          => 'nullable|in:active,inactive',
        ]);

        // Handle default account
        if ($request->has('is_default') && $request->is_default) {
            Account::where('id', '!=', $account->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $account->update([
            'account_no'      => $validated['account_no'],
            'name'            => $validated['name'],
            'branch'          => $validated['branch'] ?? null,
            'swift_code'      => $validated['swift_code'] ?? null,
            'initial_balance' => $validated['initial_balance'],
            'note'            => $validated['note'] ?? null,
            'is_default'      => $validated['is_default'] ?? false,
            'status'          => $validated['status'] ?? 'active',
        ]);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    /**
     * Remove the specified account
     */
    public function destroy(Account $account)
    {
        if ($account->transactions()->count() > 0) {
            return redirect()
                ->route('accounts.index')
                ->with('error', 'Cannot delete account with existing transactions.');
        }

        $account->delete();

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account deleted successfully.');
    }

    /**
     * Toggle default account
     */
    public function toggleDefault(Account $account)
    {
        Account::where('is_default', true)->update(['is_default' => false]);
        $account->update(['is_default' => true]);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Default account updated successfully.');
    }
}
