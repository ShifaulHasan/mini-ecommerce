<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        // Calculate paid_amount for each payroll dynamically
        foreach ($payrolls as $payroll) {
            $payroll->paid_amount = $this->calculatePaidAmount($payroll);
        }

        $totalAmount = Payroll::sum('amount');

        return view('payrolls.index', compact('payrolls', 'totalAmount'));
    }

    /**
     * Calculate total paid amount for a payroll's employee in the same month
     * Including both approved AND pending payrolls up to this payroll
     */
    private function calculatePaidAmount($payroll)
    {
        $paymentDate = Carbon::parse($payroll->payment_date);
        $startOfMonth = $paymentDate->copy()->startOfMonth();
        $endOfMonth = $paymentDate->copy()->endOfMonth();

        // Sum all approved payrolls for this employee in this month up to this record
        $totalPaid = Payroll::where('employee_id', $payroll->employee_id)
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->where('is_approve', 1)
            ->where('id', '<=', $payroll->id)
            ->sum('amount');

        return $totalPaid;
    }

    /**
     * Get paid amount for an employee in a specific month (AJAX)
     * Returns total of ALL payrolls (approved + pending) in the selected month
     */
    public function getPaidAmount(Request $request)
    {
        try {
            $employeeId = $request->input('employee_id');
            $paymentDate = $request->input('payment_date');
            $excludeId = $request->input('exclude_id'); // For edit mode
            
            // Validation
            if (!$employeeId || !$paymentDate) {
                return response()->json([
                    'paid_amount' => 0,
                    'error' => 'Missing employee_id or payment_date'
                ], 400);
            }

            $date = Carbon::parse($paymentDate);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            // Count ALL payrolls (both approved AND pending) in this month for THIS EMPLOYEE
            $query = Payroll::where('employee_id', $employeeId)
                ->whereBetween('payment_date', [$startOfMonth, $endOfMonth]);

            // Exclude specific payroll (for edit mode)
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            $paidAmount = $query->sum('amount');
            $count = $query->count();

            // Debug log
            \Log::info('getPaidAmount called', [
                'employee_id' => $employeeId,
                'payment_date' => $paymentDate,
                'month_range' => [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')],
                'total_found' => $paidAmount,
                'record_count' => $count,
                'query_sql' => $query->toSql(),
                'query_bindings' => $query->getBindings()
            ]);

            return response()->json([
                'paid_amount' => $paidAmount,
                'count' => $count,
                'month' => $date->format('F Y'),
                'employee_id' => $employeeId,
                'debug' => [
                    'start_date' => $startOfMonth->format('Y-m-d'),
                    'end_date' => $endOfMonth->format('Y-m-d')
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('getPaidAmount error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'paid_amount' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
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
            // Get employee
            $employee = Employee::findOrFail($validated['employee_id']);
            
            // Calculate paid amount this month (including pending)
            $paymentDate = Carbon::parse($validated['payment_date']);
            $startOfMonth = $paymentDate->copy()->startOfMonth();
            $endOfMonth = $paymentDate->copy()->endOfMonth();

            $paidThisMonth = Payroll::where('employee_id', $employee->id)
                ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
                ->sum('amount'); // ALL payrolls (approved + pending)

            // Calculate total with new amount
            $totalPaidAmount = $paidThisMonth + $validated['amount'];

            // Create payroll record with is_approve = 0 (pending)
            $payroll = Payroll::create([
                'employee_id' => $validated['employee_id'],
                'account_id' => $validated['account_id'],
                'amount' => $validated['amount'],
                'paid_amount' => 0, // Will be calculated dynamically or on approval
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'note' => $validated['note'],
                'is_approve' => 0,
                'created_by' => auth()->id() ?? 1,
            ]);

            DB::commit();

            return redirect()->route('payrolls.index')
                ->with('success', 'Payroll added successfully! Click approve button to process payment.');

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
        $payroll->paid_amount = $this->calculatePaidAmount($payroll);
        return view('payrolls.show', compact('payroll'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $payroll = Payroll::findOrFail($id);
        
        // Check if already approved
        if ($payroll->is_approve == 1) {
            return back()->withErrors(['error' => 'Cannot edit approved payroll!']);
        }
        
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
            
            // Check if payroll is already approved
            if ($payroll->is_approve == 1) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Cannot edit approved payroll!']);
            }

            // Update payroll (paid_amount will be calculated dynamically)
            $payroll->update($validated);

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

            // If payroll is approved, restore account balance
            if ($payroll->is_approve == 1) {
                $account = Account::findOrFail($payroll->account_id);
                $account->current_balance += $payroll->amount;
                $account->save();

                // Delete related transaction
                AccountTransaction::where('reference_type', 'payroll')
                    ->where('reference_id', $payroll->id)
                    ->delete();
            }

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

    /**
     * Approve payroll and deduct amount from account
     */
    public function approve($id)
    {
        DB::beginTransaction();

        try {
            $payroll = Payroll::with('employee')->findOrFail($id);

            // Check if already approved
            if ($payroll->is_approve == 1) {
                return back()->with('error', 'This payroll is already approved!');
            }

            // Check if account has sufficient balance
            $account = Account::findOrFail($payroll->account_id);
            
            if ($account->current_balance < $payroll->amount) {
                return back()->withErrors(['amount' => 'Insufficient account balance!']);
            }

            // Calculate paid amount for this month (approved only)
            $paymentDate = Carbon::parse($payroll->payment_date);
            $startOfMonth = $paymentDate->copy()->startOfMonth();
            $endOfMonth = $paymentDate->copy()->endOfMonth();

            $paidThisMonth = Payroll::where('employee_id', $payroll->employee_id)
                ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
                ->where('is_approve', 1)
                ->sum('amount');

            $totalAfterApproval = $paidThisMonth + $payroll->amount;

            // Update payroll status to approved
            $payroll->is_approve = 1;
            $payroll->paid_amount = $totalAfterApproval;
            $payroll->save();

            // Create account transaction (debit - money out)
            $transaction = AccountTransaction::createDebit([
                'account_id' => $payroll->account_id,
                'reference_type' => 'payroll',
                'reference_id' => $payroll->id,
                'amount' => $payroll->amount,
                'description' => 'Payroll payment for ' . $payroll->employee->name . ' - ' . $payroll->payroll_reference,
                'transaction_date' => $payroll->payment_date,
                'payment_method' => $payroll->payment_method,
                'created_by' => auth()->id() ?? 1,
            ]);

            // Update account balance
            $account->current_balance = $transaction->balance_after;
            $account->save();

            DB::commit();

            return redirect()->route('payrolls.index')
                ->with('success', 'Payroll approved and payment processed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to approve payroll: ' . $e->getMessage()]);
        }
    }
}