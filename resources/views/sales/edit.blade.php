<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-pencil"></i> Edit Sale
            </h2>
            <a href="{{ route('sales.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </x-slot>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('sales.update', $sale->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Sale Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Reference No</label>
                                <input type="text" class="form-control" value="{{ $sale->reference_number }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                                <input type="date" name="sale_date" class="form-control" value="{{ $sale->sale_date ? $sale->sale_date->format('Y-m-d') : '' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Warehouse <span class="text-danger">*</span></label>
                                <select name="warehouse_id" class="form-select" required>
                                    @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ $sale->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Customer</label>
                                <select name="customer_id" class="form-select">
                                    <option value="">Walk-in Customer</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Sale Status <span class="text-danger">*</span></label>
                                <select name="sale_status" class="form-select" required>
                                    <option value="completed" {{ $sale->sale_status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="pending" {{ $sale->sale_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Status <span class="text-danger">*</span></label>
                                <select name="payment_status" class="form-select" required>
                                    <option value="paid" {{ $sale->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="partial" {{ $sale->payment_status == 'partial' ? 'selected' : '' }}>Partial</option>
                                    <option value="pending" {{ $sale->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="">Select Method</option>
                                    <option value="cash" {{ $sale->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="card" {{ $sale->payment_method == 'card' ? 'selected' : '' }}>Card</option>
                                    <option value="bank_transfer" {{ $sale->payment_method == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="mobile_banking" {{ $sale->payment_method == 'mobile_banking' ? 'selected' : '' }}>Mobile Banking</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Notes</label>
                                <textarea name="notes" class="form-control" rows="4">{{ $sale->notes }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Sale Items (Read Only)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ optional($item->product)->name ?? 'N/A' }}</strong>
                                            @if($item->product && $item->product->product_code)
                                                <br><small class="text-muted">Code: {{ $item->product->product_code }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ number_format($item->quantity, 0) }}</span>
                                        </td>
                                        <td class="text-end">৳{{ number_format($item->unit_price ?? $item->price ?? 0, 2) }}</td>
                                        <td class="text-end">
                                            <strong>৳{{ number_format($item->quantity * ($item->unit_price ?? $item->price ?? 0), 2) }}</strong>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                        <td class="text-end">
                                            <strong class="text-primary fs-6">৳{{ number_format($sale->grand_total ?? 0, 2) }}</strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> To modify items, please delete this sale and create a new one.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-calculator"></i> Payment Summary</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $itemsSubtotal = 0;
                            foreach($sale->items as $item) {
                                $itemsSubtotal += $item->quantity * ($item->unit_price ?? $item->price ?? 0);
                            }
                            
                            $subtotal = ($sale->subtotal && $sale->subtotal > 0) ? $sale->subtotal : $itemsSubtotal;
                            $taxAmount = $sale->tax_amount ?? 0;
                            $discountAmount = $sale->discount_amount ?? 0;
                            $shippingAmount = $sale->shipping_amount ?? 0;
                            
                            $taxPercentage = 0;
                            if ($subtotal > 0 && $taxAmount > 0) {
                                $taxPercentage = ($taxAmount / $subtotal) * 100;
                            }
                        @endphp
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong>৳{{ number_format($subtotal, 2) }}</strong>
                        </div>
                       
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax @if($taxPercentage > 0)({{ number_format($taxPercentage, 2) }}%)@endif:</span>
                            <strong class="text-success">৳{{ number_format($taxAmount, 2) }}</strong>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount:</span>
                            <strong class="text-danger">-৳{{ number_format($discountAmount, 2) }}</strong>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <strong>৳{{ number_format($shippingAmount, 2) }}</strong>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Grand Total:</strong>
                            <strong class="text-primary fs-5">৳{{ number_format($sale->grand_total ?? 0, 2) }}</strong>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Paid Amount:</span>
                            <strong class="text-success">৳{{ number_format($sale->paid_amount ?? 0, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Due Amount:</span>
                            <strong class="text-danger">৳{{ number_format($sale->due_amount ?? 0, 2) }}</strong>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h5 class="mb-0"><i class="bi bi-save"></i> Actions</h5>
                    </div>
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-check-circle"></i> Update Sale
                        </button>
                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-secondary w-100">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>