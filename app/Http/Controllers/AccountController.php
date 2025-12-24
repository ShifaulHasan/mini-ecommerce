<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts
     */
    public function index(Request $request)
    {
        $query = Account::query();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('account_no', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('branch', 'like', "%{$search}%");
            });
        }

        $accounts = $query->orderBy('created_at', 'desc')->get();

        // Calculate total current balance
        $totalBalance = $accounts->sum('current_balance');

        return view('accounts.index', compact('accounts', 'totalBalance'));
    }

    /**
     * Show the form for creating a new account
     */
    public function create()
    {
        // Auto generate account number
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
            'initial_balance' => 'required|numeric',
            'note'            => 'nullable|string',
            'is_default'      => 'nullable|boolean',
            'status'          => 'nullable|in:active,inactive',
        ]);

        DB::beginTransaction();
        try {
            // Handle default account
            if ($request->boolean('is_default')) {
                Account::where('is_default', true)->update(['is_default' => false]);
            }

            $account = Account::create([
                'account_no'      => $validated['account_no'],
                'name'            => $validated['name'],
                'branch'          => $validated['branch'] ?? null,
                'swift_code'      => $validated['swift_code'] ?? null,
                'initial_balance' => $validated['initial_balance'],
                'current_balance' => $validated['initial_balance'], // Set initial balance as current
                'note'            => $validated['note'] ?? null,
                'is_default'      => $request->boolean('is_default'),
                'status'          => $validated['status'] ?? 'active',
            ]);

            // Create initial balance transaction if not zero
            if ($validated['initial_balance'] != 0) {
                AccountTransaction::create([
                    'account_id' => $account->id,
                    'reference_type' => 'opening_balance',
                    'reference_id' => null,
                    'transaction_type' => $validated['initial_balance'] >= 0 ? 'credit' : 'debit',
                    'amount' => abs($validated['initial_balance']),
                    'balance_before' => 0,
                    'balance_after' => $validated['initial_balance'],
                    'description' => 'Opening Balance',
                    'transaction_date' => now(),
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('accounts.index')
                ->with('success', 'Account created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account Create Error: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Failed to create account: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified account with transactions
     */
    public function show(Account $account, Request $request)
    {
        $query = AccountTransaction::where('account_id', $account->id);

        // Date filter
        if ($request->has('start_date') && $request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        // Transaction type filter
        if ($request->has('type') && $request->type && $request->type != 'all') {
            $query->where('transaction_type', $request->type);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
                              ->orderBy('id', 'desc')
                              ->paginate(50);

        // Calculate totals
        $totalMoneyIn = AccountTransaction::where('account_id', $account->id)
            ->where('transaction_type', 'credit')
            ->sum('amount');
            
        $totalMoneyOut = AccountTransaction::where('account_id', $account->id)
            ->where('transaction_type', 'debit')
            ->sum('amount');
            
        $netBalance = $account->initial_balance + $totalMoneyIn - $totalMoneyOut;

        return view('accounts.show', compact('account', 'transactions', 'totalMoneyIn', 'totalMoneyOut', 'netBalance'));
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
            'note'            => 'nullable|string',
            'is_default'      => 'nullable|boolean',
            'status'          => 'nullable|in:active,inactive',
        ]);

        DB::beginTransaction();
        try {
            // Handle default account
            if ($request->boolean('is_default')) {
                Account::where('id', '!=', $account->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            // Update account - DO NOT change initial_balance or current_balance here
            $account->update([
                'account_no'      => $validated['account_no'],
                'name'            => $validated['name'],
                'branch'          => $validated['branch'] ?? null,
                'swift_code'      => $validated['swift_code'] ?? null,
                'note'            => $validated['note'] ?? null,
                'is_default'      => $request->boolean('is_default'),
                'status'          => $validated['status'] ?? 'active',
            ]);

            DB::commit();

            return redirect()
                ->route('accounts.index')
                ->with('success', 'Account updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account Update Error: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Failed to update account: ' . $e->getMessage());
        }
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
        DB::beginTransaction();
        try {
            Account::where('is_default', true)->update(['is_default' => false]);
            $account->update(['is_default' => true]);

            DB::commit();

            return redirect()
                ->route('accounts.index')
                ->with('success', 'Default account updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update default account.');
        }
    }

    /**
     * Show account statement/ledger
     */
    public function statement(Account $account, Request $request)
    {
        $query = AccountTransaction::where('account_id', $account->id);

        // Date filter
        if ($request->has('start_date') && $request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        // Transaction type filter
        if ($request->has('type') && $request->type && $request->type != 'all') {
            $query->where('transaction_type', $request->type);
        }

        // Get transactions with running balance calculation
        $transactions = $query->orderBy('transaction_date', 'asc')
                              ->orderBy('id', 'asc')
                              ->get();

        // Calculate running balance
        $balance = $account->initial_balance;
        $transactionsWithBalance = $transactions->map(function($transaction) use (&$balance) {
            $transaction->balance_before = $balance;
            
            if ($transaction->transaction_type == 'credit') {
                $balance += $transaction->amount;
            } else {
                $balance -= $transaction->amount;
            }
            
            $transaction->running_balance = $balance;
            return $transaction;
        });

        // Calculate totals
        $totalMoneyIn = $transactions->where('transaction_type', 'credit')->sum('amount');
        $totalMoneyOut = $transactions->where('transaction_type', 'debit')->sum('amount');
        $netBalance = $account->initial_balance + $totalMoneyIn - $totalMoneyOut;

        return view('accounts.statement', compact('account', 'transactionsWithBalance', 'totalMoneyIn', 'totalMoneyOut', 'netBalance'));
    }

    /**
     * Recalculate account balance based on transactions
     */
    public function recalculateBalance(Account $account)
    {
        DB::beginTransaction();
        try {
            // Get all transactions in order
            $transactions = AccountTransaction::where('account_id', $account->id)
                ->orderBy('transaction_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $balance = $account->initial_balance;

            foreach ($transactions as $transaction) {
                $balanceBefore = $balance;
                
                if ($transaction->transaction_type == 'credit') {
                    $balance += $transaction->amount;
                } else {
                    $balance -= $transaction->amount;
                }

                // Update transaction balance
                $transaction->update([
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balance
                ]);
            }

            // Update account current balance
            $account->update([
                'current_balance' => $balance
            ]);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Account balance recalculated successfully. New balance: ' . number_format($balance, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Balance Recalculation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to recalculate balance: ' . $e->getMessage());
        }
    }
}