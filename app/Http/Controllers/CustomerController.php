<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\CustomerPayment;
use App\Models\User;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * Display customer list with search and filters
     */
    public function index(Request $request)
    {
        $query = Customer::with(['customerGroup', 'user']);

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } else {
                $query->where('is_active', false);
            }
        }

        // Customer group filter
        if ($request->has('customer_group_id') && $request->customer_group_id != '') {
            $query->where('customer_group_id', $request->customer_group_id);
        }

        // Supplier filter
        if ($request->has('is_supplier') && $request->is_supplier != '') {
            $query->where('is_supplier', $request->is_supplier);
        }

        $perPage = $request->get('per_page', 20);
        $customers = $query->latest()->paginate($perPage);
        
        // 🔥 SYNC all customers' due amounts before display
        foreach ($customers as $customer) {
            $customer->syncTotalDue();
        }
        
        $accounts = Account::where('status', 'active')->get();
        $customerGroups = CustomerGroup::where('is_active', true)->get();

        return view('customers.index', compact('customers', 'customerGroups', 'accounts'));
    }

    /**
     * Show create customer form
     */
    public function create()
    {
        $customerGroups = CustomerGroup::where('is_active', true)->get();
        return view('customers.create', compact('customerGroups'));
    }

    /**
     * Store new customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|unique:customers,email',
            'company_name' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'is_supplier' => 'nullable|boolean',
            'add_user' => 'nullable|boolean',
            'user_password' => 'required_if:add_user,1|nullable|min:6',
        ]);

        DB::beginTransaction();
        try {
            $validated['customer_group_id'] = null;
            $validated['discount_percentage'] = 0;
            $validated['created_by'] = auth()->id();
            $validated['is_supplier'] = $request->has('is_supplier');
            $validated['total_due'] = 0; // Initialize

            $customer = Customer::create($validated);

            if ($request->has('add_user') && $request->add_user) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? $validated['phone'] . '@customer.local',
                    'phone' => $validated['phone'],
                    'password' => Hash::make($validated['user_password']),
                    'role' => 'Customer',
                    'is_active' => true,
                ]);

                $customer->user_id = $user->id;
                $customer->save();
            }

            DB::commit();

            return redirect()
                ->route('customers.index')
                ->with('success', 'Customer created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Customer Create Error: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Failed to create customer: ' . $e->getMessage());
        }
    }

    /**
     * Show customer details
     */
    public function show(Customer $customer)
    {
        // 🔥 Sync due before showing
        $customer->syncTotalDue();
        
        $customer->load(['customerGroup', 'user', 'payments']);
        
        $salesCount = $customer->sales()->count();
        $totalSales = $customer->sales()->sum('grand_total');
        
        return view('customers.show', compact('customer', 'salesCount', 'totalSales'));
    }

    /**
     * Show edit form
     */
    public function edit(Customer $customer)
    {
        $customerGroups = CustomerGroup::where('is_active', true)->get();
        return view('customers.edit', compact('customer', 'customerGroups'));
    }

    /**
     * Update customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'company_name' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'is_supplier' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_supplier'] = $request->boolean('is_supplier');
        $validated['is_active'] = $request->boolean('is_active', true);

        $customer->update($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer updated successfully!');
    }

    /**
     * Delete customer
     */
    public function destroy(Customer $customer)
    {
        if ($customer->sales()->count() > 0) {
            return back()->with('error', 'Cannot delete customer with existing sales!');
        }

        if ($customer->payments()->count() > 0) {
            return back()->with('error', 'Cannot delete customer with existing payments!');
        }

        if ($customer->total_due > 0 || $customer->deposited_balance > 0) {
            return back()->with('error', 'Cannot delete customer with outstanding balance!');
        }

        DB::beginTransaction();
        try {
            if ($customer->user_id) {
                $user = User::find($customer->user_id);
                if ($user) {
                    $user->delete();
                }
            }

            $customer->delete();
            
            DB::commit();

            return redirect()
                ->route('customers.index')
                ->with('success', 'Customer deleted successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Customer Delete Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete customer: ' . $e->getMessage());
        }
    }

    /**
     * Show payment form
     */
    public function showPaymentForm(Customer $customer)
    {
        // 🔥 Sync due before showing payment form
        $customer->syncTotalDue();
        
        if ($customer->total_due <= 0) {
            return back()->with('error', 'Customer has no due amount!');
        }

        $accounts = Account::where('status', 'active')->get();
        return view('customers.payment', compact('customer', 'accounts'));
    }

    /**
     * 🔥 Process customer payment with FIFO logic
     */
    public function processPayment(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
            'payment_method' => 'required|string|in:cash,bank_transfer,check,card,mobile_payment,other',
            'payment_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ]);

        // 🔥 Sync customer due first
        $customer->syncTotalDue();

        if ($validated['amount'] > $customer->total_due) {
            return back()
                ->withInput()
                ->with('error', 'Payment amount (৳' . number_format($validated['amount'], 2) . ') cannot exceed total due (৳' . number_format($customer->total_due, 2) . ')!');
        }

        DB::beginTransaction();
        try {
            $paymentReference = $this->generatePaymentReference();

            Log::info('=== PAYMENT PROCESSING START ===', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'payment_amount' => $validated['amount'],
                'customer_total_due' => $customer->total_due,
            ]);

            // Create payment record
            $payment = CustomerPayment::create([
                'payment_reference' => $paymentReference,
                'customer_id' => $customer->id,
                'account_id' => $validated['account_id'],
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? 'Payment received',
                'created_by' => auth()->id(),
            ]);

            // Update account balance
            $account = Account::lockForUpdate()->find($validated['account_id']);
            $balanceBefore = $account->current_balance;
            $account->current_balance += $validated['amount'];
            $account->save();

            // Create account transaction
            AccountTransaction::create([
                'account_id' => $account->id,
                'reference_type' => 'customer_payment',
                'reference_id' => $payment->id,
                'transaction_type' => 'credit',
                'amount' => $validated['amount'],
                'balance_before' => $balanceBefore,
                'balance_after' => $account->current_balance,
                'description' => "Customer payment from {$customer->name} - {$paymentReference}",
                'transaction_date' => $validated['payment_date'],
                'created_by' => auth()->id(),
            ]);

            // 🔥 FIFO: Apply payment to sales (oldest first)
            $remainingAmount = $validated['amount'];

            $sales = Sale::where('customer_id', $customer->id)
                ->whereIn('payment_status', ['pending', 'partial'])
                ->where('due_amount', '>', 0)
                ->orderBy('sale_date', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            Log::info('Sales found for FIFO payment', [
                'count' => $sales->count(),
                'sales' => $sales->map(fn($s) => [
                    'id' => $s->id,
                    'ref' => $s->reference_number,
                    'due' => $s->due_amount,
                    'status' => $s->payment_status,
                ])->toArray(),
            ]);

            $salesUpdated = 0;
            foreach ($sales as $sale) {
                if ($remainingAmount <= 0) break;

                $oldDue = $sale->due_amount;
                $oldPaid = $sale->paid_amount;
                $oldStatus = $sale->payment_status;

                if ($remainingAmount >= $sale->due_amount) {
                    // Full payment
                    $paymentForSale = $sale->due_amount;
                    $remainingAmount -= $sale->due_amount;
                    
                    $sale->paid_amount = $sale->grand_total;
                    $sale->due_amount = 0;
                    $sale->payment_status = 'paid';
                } else {
                    // Partial payment
                    $paymentForSale = $remainingAmount;
                    
                    $sale->paid_amount += $remainingAmount;
                    $sale->due_amount -= $remainingAmount;
                    $sale->payment_status = ($sale->due_amount > 0) ? 'partial' : 'paid';
                    $remainingAmount = 0;
                }

                $sale->save();
                $salesUpdated++;

                Log::info('Sale updated via FIFO', [
                    'sale_id' => $sale->id,
                    'reference' => $sale->reference_number,
                    'payment_applied' => $paymentForSale,
                    'old_paid' => $oldPaid,
                    'new_paid' => $sale->paid_amount,
                    'old_due' => $oldDue,
                    'new_due' => $sale->due_amount,
                    'old_status' => $oldStatus,
                    'new_status' => $sale->payment_status,
                ]);
            }

            // 🔥 SYNC customer total_due from sales (SINGLE SOURCE OF TRUTH)
            $customer->syncTotalDue();

            DB::commit();

            Log::info('=== PAYMENT PROCESSING SUCCESS ===', [
                'payment_reference' => $paymentReference,
                'sales_updated' => $salesUpdated,
                'customer_total_due_after' => $customer->total_due,
            ]);

            return redirect()
                ->route('customers.index')
                ->with('success', 'Payment of ৳' . number_format($validated['amount'], 2) . ' processed successfully! ' . $salesUpdated . ' sale(s) updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== PAYMENT PROCESSING FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    /**
     * Customer ledger
     */
    public function ledger(Customer $customer)
    {
        // 🔥 Sync due before showing ledger
        $customer->syncTotalDue();
        
        $sales = $customer->sales()->latest('sale_date')->latest('id')->get();
        $payments = $customer->payments()->latest('payment_date')->latest('id')->get();

        $transactions = collect();

        foreach ($sales as $sale) {
            $transactions->push([
                'date' => $sale->sale_date,
                'type' => 'sale',
                'reference' => $sale->reference_number ?? 'SALE-' . $sale->id,
                'debit' => $sale->grand_total,
                'credit' => 0,
                'balance' => 0,
                'details' => 'Sale Transaction',
            ]);
        }

        foreach ($payments as $payment) {
            $transactions->push([
                'date' => $payment->payment_date,
                'type' => 'payment',
                'reference' => $payment->payment_reference,
                'debit' => 0,
                'credit' => $payment->amount,
                'balance' => 0,
                'details' => $payment->notes ?? 'Payment Received',
            ]);
        }

        $transactions = $transactions->sortBy('date');

        $balance = 0;
        $transactions = $transactions->map(function($transaction) use (&$balance) {
            $balance += ($transaction['debit'] - $transaction['credit']);
            $transaction['balance'] = $balance;
            return $transaction;
        });

        return view('customers.ledger', compact('customer', 'transactions'));
    }

    private function generateCustomerCode()
    {
        $lastCustomer = Customer::latest('id')->first();
        $nextId = $lastCustomer ? $lastCustomer->id + 1 : 1;
        
        return 'CUST-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    private function generatePaymentReference()
    {
        $date = now()->format('Ymd');
        $lastPayment = CustomerPayment::whereDate('created_at', today())
            ->latest('id')
            ->first();
        
        $sequence = $lastPayment ? (int) substr($lastPayment->payment_reference, -4) + 1 : 1;
        
        return 'CP-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function toggleStatus(Customer $customer)
    {
        $customer->is_active = !$customer->is_active;
        $customer->save();

        $status = $customer->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Customer {$status} successfully!");
    }

    public function export(Request $request)
    {
        return back()->with('info', 'Export functionality coming soon!');
    }
}