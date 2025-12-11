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
                               value="{{ old('purchase_date', $purchase->purchase_date) }}" required>
                        @error('purchase_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" 
                                    {{ old('supplier_id', $purchase->supplier_id) == $supplier->id ? 'selected' : '' }}>
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
                            <option value="pending" {{ $purchase->purchase_status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="received" {{ $purchase->purchase_status == 'received' ? 'selected' : '' }}>Received</option>
                            <option value="cancelled" {{ $purchase->purchase_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Paid Amount</label>
                        <input type="number" name="paid_amount" class="form-control" 
                               step="0.01" value="{{ old('paid_amount', $purchase->amount_paid ?? 0) }}">
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
                                <th>Quantity</th>
                                <th>Cost Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items ?? [] as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->cost_price, 2) }}</td>
                                <td>{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td><strong>{{ number_format($purchase->total, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> To modify purchase items, please delete and create a new purchase.
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="card mb-3">
            <div class="card-body">
                <h6>Payment Summary</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Amount:</span>
                    <strong>{{ number_format($purchase->total, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Paid:</span>
                    <strong class="text-success">{{ number_format($purchase->amount_paid ?? 0, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Due:</span>
                    <strong class="text-danger">{{ number_format(($purchase->total - ($purchase->amount_paid ?? 0)), 2) }}</strong>
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
