<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['warehouse', 'customer', 'creator', 'items.product'])
            ->latest()
            ->paginate(20);

        $warehouses = Warehouse::all();

        return view('sales.index', compact('sales', 'warehouses'));
    }

    public function create()
    {
        $referenceNo = Sale::generateReferenceNo();

        $products   = Product::all();
        $warehouses = Warehouse::all();
        
        $customers = collect();
        
        try {
            $customersFromTable = Customer::where('is_active', true)
                ->select('id', 'name', 'email', 'phone', 'company_name')
                ->get()
                ->map(function($customer) {
                    $displayName = $customer->name;
                    if ($customer->company_name) {
                        $displayName .= ' (' . $customer->company_name . ')';
                    }
                    return (object)[
                        'id' => 'customer_' . $customer->id,
                        'name' => $displayName,
                        'email' => $customer->email ?? '',
                        'phone' => $customer->phone ?? '',
                        'type' => 'customer'
                    ];
                });
            $customers = $customers->merge($customersFromTable);
        } catch (\Exception $e) {
            Log::warning('Could not load customers from Customer table: ' . $e->getMessage());
        }
        
        $usersAsCustomers = User::where('role', 'Customer')
            ->select('id', 'name', 'email')
            ->get()
            ->map(function($user) {
                return (object)[
                    'id' => 'user_' . $user->id,
                    'name' => $user->name . ' [User Account]',
                    'email' => $user->email ?? '',
                    'type' => 'user'
                ];
            });
        
        $customers = $customers->merge($usersAsCustomers);
        
        $accounts = \App\Models\Account::where('status', 'active')->get();

        return view('sales.create', compact(
            'referenceNo',
            'products',
            'warehouses',
            'customers',
            'accounts'
        ));
    }

    public function store(Request $request)
    {
        if ($request->isJson()) {
            $request->merge($request->json()->all());
        }

        $validated = $request->validate([
            'reference_number' => 'nullable|string|unique:sales,reference_number',
            'sale_date'      => 'required|date',
            'warehouse_id'   => 'required|exists:warehouses,id',
            'customer_id'    => 'nullable|string',

            'sale_status'    => 'required|in:completed,pending',
            'payment_status' => 'required|in:paid,partial,pending',
            'payment_method' => 'nullable|string',
            'account_id'     => 'required|exists:accounts,id',
            'delivery_status' => 'nullable|in:pending,processing,delivered,cancelled',

            'subtotal'       => 'nullable|numeric|min:0',
            'tax_amount'     => 'nullable|numeric|min:0',
            'discount_amount'=> 'nullable|numeric|min:0',
            'shipping_amount'=> 'nullable|numeric|min:0',
            'grand_total'    => 'required|numeric|min:0',
            'amount_paid'    => 'nullable|numeric|min:0',

            'notes'          => 'nullable|string',

            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|numeric|min:1',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.discount'       => 'nullable|numeric|min:0',
            'items.*.tax'            => 'nullable|numeric|min:0',
        ]);

        $customerId = null;
        $customerRecord = null;
        
        if (!empty($validated['customer_id'])) {
            if (strpos($validated['customer_id'], 'customer_') === 0) {
                $customerId = (int) str_replace('customer_', '', $validated['customer_id']);
                $customerRecord = Customer::find($customerId);
                
                if (!$customerRecord) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected customer not found'
                    ], 422);
                }
            } elseif (strpos($validated['customer_id'], 'user_') === 0) {
                $userId = (int) str_replace('user_', '', $validated['customer_id']);
                $user = User::find($userId);
                
                if (!$user || $user->role !== 'Customer') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected user is not a customer'
                    ], 422);
                }
                
                $customerRecord = Customer::where('user_id', $userId)->first();
                
                if (!$customerRecord) {
                    $customerRecord = Customer::create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone ?? '',
                        'user_id' => $userId,
                        'is_active' => true,
                        'created_by' => auth()->id(),
                    ]);
                }
                
                $customerId = $customerRecord->id;
            } else {
                $customerId = (int) $validated['customer_id'];
                $customerRecord = Customer::find($customerId);
                
                if (!$customerRecord) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Customer not found'
                    ], 422);
                }
            }
        }

        $paidAmount = $validated['amount_paid'] ?? 0;
        $dueAmount  = max(0, $validated['grand_total'] - $paidAmount);
        $billerName = auth()->user()->name ?? 'Admin';
        $deliveryStatus = $validated['delivery_status'] ?? null;
        
        if (!$deliveryStatus) {
            $deliveryStatus = $validated['sale_status'] === 'completed' ? 'delivered' : 'pending';
        }

        DB::beginTransaction();

        try {
            $referenceNumber = $validated['reference_number'] ?? 'SAL-' . date('Ymd') . '-' . str_pad(
                Sale::whereDate('created_at', date('Y-m-d'))->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

            $sale = Sale::create([
                'reference_number' => $referenceNumber,
                'sale_date'        => $validated['sale_date'],
                'warehouse_id'     => $validated['warehouse_id'],
                'customer_id'      => $customerId,

                'biller'           => $billerName,
                'sale_status'      => $validated['sale_status'],
                'payment_status'   => $validated['payment_status'],
                'payment_method'   => $validated['payment_method'] ?? null,
                'account_id'       => $validated['account_id'],
                'delivery_status'  => $deliveryStatus,

                'subtotal'         => $validated['subtotal'] ?? 0,
                'tax_amount'       => $validated['tax_amount'] ?? 0,
                'discount_amount'  => $validated['discount_amount'] ?? 0,
                'shipping_amount'  => $validated['shipping_amount'] ?? 0,
                'grand_total'      => $validated['grand_total'],
                'paid_amount'      => $paidAmount,
                'amount_paid'      => $paidAmount,
                'due_amount'       => $dueAmount,
                'amount_due'       => $dueAmount,

                'notes'            => $validated['notes'] ?? null,
                'created_by'       => auth()->id(),
            ]);

            Log::info('Sale created', [
                'sale_id' => $sale->id,
                'reference' => $sale->reference_number,
                'customer_id' => $customerId,
                'grand_total' => $validated['grand_total'],
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
            ]);

            foreach ($validated['items'] as $item) {
                $subtotal = ($item['quantity'] * $item['unit_price'])
                    - ($item['discount'] ?? 0)
                    + ($item['tax'] ?? 0);

                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['unit_price'],
                    'unit_price' => $item['unit_price'],
                    'discount'   => $item['discount'] ?? 0,
                    'tax'        => $item['tax'] ?? 0,
                    'subtotal'   => $subtotal,
                ]);

                if ($validated['sale_status'] === 'completed') {
                    $product = Product::lockForUpdate()->find($item['product_id']);

                    if (!$product) {
                        throw new \Exception("Product not found: {$item['product_id']}");
                    }

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }

                    $product->decrement('stock', $item['quantity']);
                }
            }

            if ($customerRecord) {
                $customerRecord->syncTotalDue();
                
                Log::info('Customer due synced after sale creation', [
                    'customer_id' => $customerRecord->id,
                    'customer_name' => $customerRecord->name,
                    'total_due' => $customerRecord->total_due,
                ]);
            }

            if ($paidAmount > 0 && !empty($validated['account_id'])) {
                $account = \App\Models\Account::lockForUpdate()->find($validated['account_id']);
                
                if (!$account) {
                    throw new \Exception("Account not found");
                }

                $balanceBefore = $account->current_balance;
                $account->current_balance += $paidAmount;
                $account->save();

                \App\Models\AccountTransaction::create([
                    'account_id'       => $account->id,
                    'reference_type'   => 'sale',
                    'reference_id'     => $sale->id,
                    'transaction_type' => 'credit',
                    'amount'           => $paidAmount,
                    'balance_before'   => $balanceBefore,
                    'balance_after'    => $account->current_balance,
                    'description'      => "Sale payment - {$sale->reference_number}",
                    'transaction_date' => $validated['sale_date'],
                    'created_by'       => auth()->id(),
                ]);

                Log::info('Account transaction created', [
                    'account_id' => $account->id,
                    'amount' => $paidAmount,
                    'balance_after' => $account->current_balance,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Sale created successfully',
                'sale_id'  => $sale->id,
                'redirect' => route('sales.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SALE STORE ERROR: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['warehouse', 'customer', 'creator', 'items.product']);
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $products   = Product::all();
        $warehouses = Warehouse::all();
        
        $customers = collect();
        
        try {
            $customersFromTable = Customer::where('is_active', true)
                ->select('id', 'name', 'email', 'phone', 'company_name')
                ->get()
                ->map(function($customer) {
                    $displayName = $customer->name;
                    if ($customer->company_name) {
                        $displayName .= ' (' . $customer->company_name . ')';
                    }
                    return (object)[
                        'id' => 'customer_' . $customer->id,
                        'name' => $displayName,
                        'email' => $customer->email ?? '',
                        'phone' => $customer->phone ?? '',
                        'type' => 'customer'
                    ];
                });
            $customers = $customers->merge($customersFromTable);
        } catch (\Exception $e) {
            Log::warning('Could not load customers from Customer table: ' . $e->getMessage());
        }
        
        $usersAsCustomers = User::where('role', 'Customer')
            ->select('id', 'name', 'email')
            ->get()
            ->map(function($user) {
                return (object)[
                    'id' => 'user_' . $user->id,
                    'name' => $user->name . ' [User Account]',
                    'email' => $user->email ?? '',
                    'type' => 'user'
                ];
            });
        
        $customers = $customers->merge($usersAsCustomers);

        return view('sales.edit', compact(
            'sale',
            'products',
            'warehouses',
            'customers'
        ));
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'sale_date'      => 'required|date',
            'warehouse_id'   => 'required|exists:warehouses,id',
            'customer_id'    => 'nullable|string',
            'sale_status'    => 'required|in:completed,pending',
            'payment_status' => 'required|in:paid,partial,pending',
            'payment_method' => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldCustomerId = $sale->customer_id;
            
            $sale->update($validated);

            if ($oldCustomerId && $oldCustomerId != $sale->customer_id) {
                $oldCustomer = Customer::find($oldCustomerId);
                if ($oldCustomer) {
                    $oldCustomer->syncTotalDue();
                }
            }
            
            if ($sale->customer_id) {
                $newCustomer = Customer::find($sale->customer_id);
                if ($newCustomer) {
                    $newCustomer->syncTotalDue();
                }
            }

            DB::commit();

            return redirect()
                ->route('sales.index')
                ->with('success', 'Sale updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale update error: ' . $e->getMessage());
            return back()->with('error', 'Failed to update sale');
        }
    }

    public function destroy(Sale $sale)
    {
        DB::beginTransaction();

        try {
            $customerId = $sale->customer_id;

            if ($sale->sale_status === 'completed') {
                foreach ($sale->items as $item) {
                    Product::where('id', $item->product_id)
                        ->increment('stock', $item->quantity);
                }
            }

            if ($sale->paid_amount > 0 && $sale->account_id) {
                $account = \App\Models\Account::lockForUpdate()->find($sale->account_id);
                if ($account) {
                    $balanceBefore = $account->current_balance;
                    $account->current_balance -= $sale->paid_amount;
                    $account->save();

                    \App\Models\AccountTransaction::create([
                        'account_id'       => $account->id,
                        'reference_type'   => 'sale_delete',
                        'reference_id'     => $sale->id,
                        'transaction_type' => 'debit',
                        'amount'           => $sale->paid_amount,
                        'balance_before'   => $balanceBefore,
                        'balance_after'    => $account->current_balance,
                        'description'      => "Sale deleted - {$sale->reference_number}",
                        'transaction_date' => now(),
                        'created_by'       => auth()->id(),
                    ]);
                }
            }

            $sale->delete();

            if ($customerId) {
                $customer = Customer::find($customerId);
                if ($customer) {
                    $customer->syncTotalDue();
                }
            }

            DB::commit();

            return redirect()
                ->route('sales.index')
                ->with('success', 'Sale deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SALE DELETE ERROR: ' . $e->getMessage());
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}