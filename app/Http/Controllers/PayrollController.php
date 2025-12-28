<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * Display payroll list
     */
    public function index()
    {
        $payrolls = Payroll::with(['employee', 'account'])
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        $totalAmount = Payroll::sum('amount');

        return view('payrolls.index', compact('payrolls', 'totalAmount'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $employees = Employee::orderBy('name')->get();
        $accounts = Account::active()->orderBy('name')->get();
        $paymentMethods = Payroll::getPaymentMethods();

        return view('payrolls.create', compact('employees', 'accounts', 'paymentMethods'));
    }

    /**
     * Store new payroll
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'payment_date' => 'required|date',
            'note' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();

        try {
            // Check if account has sufficient balance
            $account = Account::findOrFail($validated['account_id']);
            
            if ($account->current_balance < $validated['amount']) {
                return back()->withErrors(['amount' => 'Insufficient account balance!'])->withInput();
            }

            // Create payroll record
            $payroll = Payroll::create([
                'employee_id' => $validated['employee_id'],
                'account_id' => $validated['account_id'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'note' => $validated['note'],
                'created_by' => auth()->id() ?? 1,
            ]);

            // Create account transaction (debit - money out)
            $transaction = AccountTransaction::createDebit([
                'account_id' => $validated['account_id'],
                'reference_type' => 'payroll',
                'reference_id' => $payroll->id,
                'amount' => $validated['amount'],
                'description' => 'Payroll payment for ' . $payroll->employee->name . ' - ' . $payroll->payroll_reference,
                'transaction_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'created_by' => auth()->id() ?? 1,
            ]);

            // Update account balance
            $account->current_balance = $transaction->balance_after;
            $account->save();

            DB::commit();

            return redirect()->route('payrolls.index')
                ->with('success', 'Payroll added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create payroll: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display payroll details
     */
    public function show($id)
    {
        $payroll = Payroll::with(['employee', 'account', 'transaction', 'creator'])->findOrFail($id);
        return view('payrolls.show', compact('payroll'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $payroll = Payroll::findOrFail($id);
        $employees = Employee::orderBy('name')->get();
        $accounts = Account::active()->orderBy('name')->get();
        $paymentMethods = Payroll::getPaymentMethods();

        return view('payrolls.edit', compact('payroll', 'employees', 'accounts', 'paymentMethods'));
    }

    /**
     * Update payroll
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'payment_date' => 'required|date',
            'note' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();

        try {
            $payroll = Payroll::findOrFail($id);
            $oldAmount = $payroll->amount;
            $oldAccountId = $payroll->account_id;
            $newAccountId = $validated['account_id'];
            $newAmount = $validated['amount'];

            // Restore old account balance
            $oldAccount = Account::findOrFail($oldAccountId);
            $oldAccount->current_balance += $oldAmount;
            $oldAccount->save();

            // Check if new account has sufficient balance
            $newAccount = Account::findOrFail($newAccountId);
            if ($newAccount->current_balance < $newAmount) {
                DB::rollBack();
                return back()->withErrors(['amount' => 'Insufficient account balance!'])->withInput();
            }

            // Update payroll
            $payroll->update($validated);

            // Delete old transaction
            AccountTransaction::where('reference_type', 'payroll')
                ->where('reference_id', $payroll->id)
                ->delete();

            // Create new transaction
            $transaction = AccountTransaction::createDebit([
                'account_id' => $newAccountId,
                'reference_type' => 'payroll',
                'reference_id' => $payroll->id,
                'amount' => $newAmount,
                'description' => 'Payroll payment for ' . $payroll->employee->name . ' - ' . $payroll->payroll_reference,
                'transaction_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'created_by' => auth()->id() ?? 1,
            ]);

            // Update new account balance
            $newAccount->current_balance = $transaction->balance_after;
            $newAccount->save();

            DB::commit();

            return redirect()->route('payrolls.index')
                ->with('success', 'Payroll updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update payroll: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Delete payroll
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $payroll = Payroll::findOrFail($id);

            // Restore account balance
            $account = Account::findOrFail($payroll->account_id);
            $account->current_balance += $payroll->amount;
            $account->save();

            // Delete related transaction
            AccountTransaction::where('reference_type', 'payroll')
                ->where('reference_id', $payroll->id)
                ->delete();

            // Delete payroll
            $payroll->delete();

            DB::commit();

            return redirect()->route('payrolls.index')
                ->with('success', 'Payroll deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete payroll: ' . $e->getMessage()]);
        }
    }
}