<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">Edit Purchase</h2>
            <a href="{{ route('purchases.index') }}" class="btn btn-secondary btn-sm">
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

    <form action="{{ route('purchases.update', $purchase) }}" method="POST">
        @csrf
        @method('PUT')
        
        <!-- Purchase Information -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Purchase Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Reference Number</label>
                        <input type="text" class="form-control" value="{{ $purchase->reference_no }}" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" 
                               class="form-control @error('purchase_date') is-invalid @enderror" 
                               value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}" required>
                        @error('purchase_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                @php
                                    // Build the current purchase's supplier ID for comparison
                                    $currentSupplierCombinedId = $purchase->supplier_type . '_' . $purchase->supplier_id;
                                @endphp
                                <option value="{{ $supplier->id }}" 
                                        {{ old('supplier_id', $currentSupplierCombinedId) == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                        <select name="warehouse_id" class="form-select" required>
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" 
                                    {{ old('warehouse_id', $purchase->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Purchase Status</label>
                        <select name="purchase_status" class="form-select">
                            <option value="pending" {{ old('purchase_status', $purchase->purchase_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="received" {{ old('purchase_status', $purchase->purchase_status) == 'received' ? 'selected' : '' }}>Received</option>
                            <option value="cancelled" {{ old('purchase_status', $purchase->purchase_status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Paid Amount</label>
                        <input type="number" name="paid_amount" class="form-control" 
                               step="0.01" min="0" max="{{ $purchase->grand_total }}"
                               value="{{ old('paid_amount', $purchase->paid_amount ?? 0) }}">
                        <small class="text-muted">Max: {{ number_format($purchase->grand_total, 2) }}</small>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $purchase->notes) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Purchase Items -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Purchase Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th width="100">Quantity</th>
                                <th width="150">Cost Price</th>
                                <th width="150">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchase->items ?? [] as $item)
                            <tr>
                                <td>
                                    {{ $item->product->name ?? 'N/A' }}
                                    @if($item->product->sku)
                                        <br><small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->cost_price, 2) }}</td>
                                <td class="text-end">{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No items found</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end"><strong>{{ number_format($purchase->subtotal ?? 0, 2) }}</strong></td>
                            </tr>
                            @if($purchase->tax_amount > 0)
                            <tr>
                                <td colspan="3" class="text-end">Tax ({{ $purchase->tax_percentage }}%):</td>
                                <td class="text-end">{{ number_format($purchase->tax_amount, 2) }}</td>
                            </tr>
                            @endif
                            @if($purchase->discount_amount > 0)
                            <tr>
                                <td colspan="3" class="text-end">Discount:</td>
                                <td class="text-end text-danger">-{{ number_format($purchase->discount_amount, 2) }}</td>
                            </tr>
                            @endif
                            @if($purchase->shipping_cost > 0)
                            <tr>
                                <td colspan="3" class="text-end">Shipping:</td>
                                <td class="text-end">{{ number_format($purchase->shipping_cost, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                                <td class="text-end"><strong>{{ number_format($purchase->grand_total ?? 0, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> To modify purchase items, quantities, or prices, please delete this purchase and create a new one.
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="card mb-3">
            <div class="card-header bg-warning">
                <h6 class="mb-0">Payment Summary</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between">
                            <span>Grand Total:</span>
                            <strong>{{ number_format($purchase->grand_total ?? 0, 2) }}</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between">
                            <span>Paid Amount:</span>
                            <strong class="text-success">{{ number_format($purchase->paid_amount ?? 0, 2) }}</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between">
                            <span>Due Amount:</span>
                            <strong class="text-danger">{{ number_format($purchase->due_amount ?? 0, 2) }}</strong>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Payment Status:</span>
                    <span class="badge 
                        @if($purchase->payment_status == 'paid') bg-success
                        @elseif($purchase->payment_status == 'partial') bg-warning
                        @else bg-danger
                        @endif">
                        {{ ucfirst($purchase->payment_status ?? 'unpaid') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Update Purchase
            </button>
            <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Cancel
            </a>
        </div>
    </form>
</x-app-layout>