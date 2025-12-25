<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\User;
use App\Models\Account;
use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $suppliers = $query->latest()->get();

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|unique:suppliers,email',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string|max:100',
            'country'        => 'nullable|string|max:100',
            'postal_code'    => 'nullable|string|max:20',
            'company'        => 'nullable|string|max:255',
            'vat_number'     => 'nullable|string|max:50',
            'status'         => 'required|in:active,inactive',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'bank_name'      => 'nullable|string|max:255',
            'branch_name'    => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'routing_number' => 'nullable|string|max:100',
            'swift_code'     => 'nullable|string|max:50',
            'iban'           => 'nullable|string|max:100',
            'currency_type'  => 'nullable|string|max:20',
            'bank_address'   => 'nullable|string',
            'mobile_banking' => 'nullable|string|max:100',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('suppliers', 'public');
        }

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully!');
    }

    /**
     * Show the form for editing the specified supplier
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|unique:suppliers,email,' . $supplier->id,
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string|max:100',
            'country'        => 'nullable|string|max:100',
            'postal_code'    => 'nullable|string|max:20',
            'company'        => 'nullable|string|max:255',
            'vat_number'     => 'nullable|string|max:50',
            'status'         => 'required|in:active,inactive',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'bank_name'      => 'nullable|string|max:255',
            'branch_name'    => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'routing_number' => 'nullable|string|max:100',
            'swift_code'     => 'nullable|string|max:50',
            'iban'           => 'nullable|string|max:100',
            'currency_type'  => 'nullable|string|max:20',
            'bank_address'   => 'nullable|string',
            'mobile_banking' => 'nullable|string|max:100',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($supplier->image) {
                Storage::disk('public')->delete($supplier->image);
            }
            $validated['image'] = $request->file('image')->store('suppliers', 'public');
        }

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully!');
    }

    /**
     * Remove the specified supplier
     */
    public function destroy(Supplier $supplier)
    {
        // Delete image if exists
        if ($supplier->image) {
            Storage::disk('public')->delete($supplier->image);
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully!');
    }

    /**
     * Show supplier due report
     */
    public function dueReport(Request $request, Supplier $supplier)
    {
        $query = Purchase::where('supplier_id', $supplier->id)
            ->where('supplier_type', 'supplier');

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('purchase_date', [$request->start_date, $request->end_date]);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%");
            });
        }

        $purchases = $query->with('items.product')
            ->latest('purchase_date')
            ->get();

        $payments = $supplier->payments()
            ->with('account', 'creator')
            ->latest('payment_date')
            ->get();

        return view('suppliers.due-report', compact('supplier', 'purchases', 'payments'));
    }

    /**
     * Load accounts for payment form (GET request)
     */
    public function addPayment(Request $request, Supplier $supplier)
    {
        // Check authentication
        if (!auth()->check()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }
            return redirect()->route('login');
        }

        // GET request - Load accounts for the payment modal
        if ($request->isMethod('GET')) {
            try {
                $accounts = Account::where('status', 'active')
                    ->select('id', 'name', 'account_no', 'current_balance')
                    ->get()
                    ->map(function($account) {
                        return [
                            'id' => $account->id,
                            'account_name' => $account->name,
                            'account_number' => $account->account_no,
                            'current_balance' => $account->current_balance ?? 0
                        ];
                    });
                
                // Get accurate total due from purchases
                $totalDue = $supplier->calculated_total_due;
                
                return response()->json([
                    'success' => true,
                    'accounts' => $accounts->values(),
                    'supplier' => [
                        'id' => $supplier->id,
                        'name' => $supplier->name,
                        'total_due' => $totalDue
                    ]
                ]);
            } catch (\Exception $e) {
                Log::error('Account loading error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error loading accounts: ' . $e->getMessage()
                ], 500);
            }
        }

        // POST request - Process payment
        return $this->storePayment($request, $supplier);
    }

    /**
     * Store supplier payment (POST request)
     */
    public function storePayment(Request $request, Supplier $supplier)
    {
        // Check authentication
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        try {
            // Validate request
            $validated = $request->validate([
                'amount'         => 'required|numeric|min:0.01',
                'payment_date'   => 'required|date',
                'account_id'     => 'required|exists:accounts,id',
                'payment_method' => 'nullable|string',
                'reference_no'   => 'nullable|string|max:100',
                'notes'          => 'nullable|string'
            ]);

            // Get accurate total due from purchases
            $currentTotalDue = $supplier->calculated_total_due;

            // Check if amount exceeds due
            if ($validated['amount'] > $currentTotalDue) {
                return response()->json([
                    'success' => false,
                    'message' => "Payment amount cannot exceed total due amount ({$currentTotalDue})"
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Get account with lock for concurrent safety
                $account = Account::lockForUpdate()->findOrFail($validated['account_id']);
                
                // Check if account has sufficient balance
                if ($account->current_balance < $validated['amount']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient account balance'
                    ], 422);
                }

                // Create supplier payment record
                $payment = SupplierPayment::create([
                    'supplier_id'    => $supplier->id,
                    'account_id'     => $validated['account_id'],
                    'amount'         => $validated['amount'],
                    'payment_date'   => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'] ?? 'bank_transfer',
                    'reference_no'   => $validated['reference_no'] ?? null,
                    'notes'          => $validated['notes'] ?? null,
                    'created_by'     => auth()->id()
                ]);

                // Create account transaction using the new model method
                AccountTransaction::createDebit([
                    'account_id'      => $validated['account_id'],
                    'amount'          => $validated['amount'],
                    'reference_type'  => 'supplier_payment',
                    'reference_id'    => $payment->id,
                    'description'     => "Payment to supplier: {$supplier->name}",
                    'transaction_date'=> $validated['payment_date'],
                    'payment_method'  => $validated['payment_method'] ?? 'bank_transfer',
                    'created_by'      => auth()->id()
                ]);

                // Update account balance
                $account->decrement('current_balance', $validated['amount']);

                // Update related purchases' due amounts (proportionally)
                $this->updatePurchasesDueAmount($supplier->id, $validated['amount']);

                // CRITICAL: Recalculate and sync supplier's total_due from purchases
                $newTotalDue = $supplier->recalculateTotalDue();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully!',
                    'payment' => $payment,
                    'new_due' => $newTotalDue
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update purchase due amounts after supplier payment
     */
    private function updatePurchasesDueAmount($supplierId, $paidAmount)
    {
        // Get all purchases with due amount for this supplier
        $purchases = Purchase::where('supplier_id', $supplierId)
            ->where('supplier_type', 'supplier')
            ->where('due_amount', '>', 0)
            ->orderBy('purchase_date', 'asc')
            ->get();

        $remainingPayment = $paidAmount;

        foreach ($purchases as $purchase) {
            if ($remainingPayment <= 0) {
                break;
            }

            $purchaseDue = $purchase->due_amount;

            if ($remainingPayment >= $purchaseDue) {
                // Payment covers this purchase completely
                $purchase->due_amount = 0;
                $purchase->paid_amount += $purchaseDue;
                $purchase->payment_status = 'paid';
                $remainingPayment -= $purchaseDue;
            } else {
                // Payment partially covers this purchase
                $purchase->due_amount -= $remainingPayment;
                $purchase->paid_amount += $remainingPayment;
                $purchase->payment_status = 'partial';
                $remainingPayment = 0;
            }

            $purchase->save();
        }
    }

    /**
     * Get all suppliers (from both suppliers table and users with role Supplier)
     */
    public function getAllSuppliers(Request $request)
    {
        try {
            // Get suppliers from suppliers table
            $suppliersFromTable = Supplier::where('status', 'active')
                ->select('id', 'name', 'company', 'phone', 'email')
                ->get()
                ->map(function($supplier) {
                    return [
                        'id' => 'supplier_' . $supplier->id,
                        'actual_id' => $supplier->id,
                        'type' => 'supplier',
                        'name' => $supplier->name,
                        'display_name' => $supplier->name . ($supplier->company ? " ({$supplier->company})" : '') . ' [Supplier]',
                        'phone' => $supplier->phone,
                        'email' => $supplier->email,
                    ];
                });

            // Get users with role 'Supplier'
            $userSuppliers = User::where('role', 'Supplier')
                ->where('status', 'active')
                ->select('id', 'name', 'phone', 'email')
                ->get()
                ->map(function($user) {
                    return [
                        'id' => 'user_' . $user->id,
                        'actual_id' => $user->id,
                        'type' => 'user',
                        'name' => $user->name,
                        'display_name' => $user->name . ' [User Account]',
                        'phone' => $user->phone,
                        'email' => $user->email,
                    ];
                });

            // Merge both collections
            $allSuppliers = $suppliersFromTable->concat($userSuppliers)
                ->sortBy('name')
                ->values();

            // Return JSON for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'suppliers' => $allSuppliers,
                    'count' => $allSuppliers->count()
                ]);
            }

            return $allSuppliers;

        } catch (\Exception $e) {
            Log::error('Error fetching suppliers: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error fetching suppliers: ' . $e->getMessage()
                ], 500);
            }

            return collect([]);
        }
    }
}