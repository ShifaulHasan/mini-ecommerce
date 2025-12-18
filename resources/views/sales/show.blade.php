<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-receipt"></i> Sale Details
            </h2>
            <a href="{{ route('sales.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-md-8">
            <!-- Sale Information -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Sale Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Reference No:</strong>
                            <p>{{ $sale->reference_no }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Date:</strong>
                            <p>{{ $sale->sale_date ? $sale->sale_date->format('d M Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Customer:</strong>
                            <p>{{ optional($sale->customer)->name ?? 'Walk-in Customer' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Biller:</strong>
                            <p>{{ optional($sale->biller)->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Warehouse:</strong>
                            <p>{{ optional($sale->warehouse)->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong>
                            <p>
                                @if($sale->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                                @else
                                <span class="badge bg-warning">Pending</span>
                                @endif
                            </p>
                        </div>
                        @if($sale->notes)
                        <div class="col-12 mb-3">
                            <strong>Notes:</strong>
                            <p>{{ $sale->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sale Items -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Sale Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Discount</th>
                                    <th>Tax</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sale->items as $item)
                                <tr>
                                    <td>{{ optional($item->product)->name ?? 'N/A' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->unit_price, 2) }}</td>
                                    <td>${{ number_format($item->discount ?? 0, 2) }}</td>
                                    <td>${{ number_format($item->tax ?? 0, 2) }}</td>
                                    <td>${{ number_format(($item->quantity * $item->unit_price) - ($item->discount ?? 0) + ($item->tax ?? 0), 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No items found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Payment Summary -->
            <div class="card mb-3">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="mb-0">Payment Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong>${{ number_format(($sale->grand_total ?? 0) - ($sale->tax_amount ?? 0) + ($sale->discount_amount ?? 0), 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax ({{ $sale->tax_percentage ?? 0 }}%):</span>
                        <strong>${{ number_format($sale->tax_amount ?? 0, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Discount:</span>
                        <strong>-${{ number_format($sale->discount_amount ?? 0, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <strong>${{ number_format($sale->shipping_cost ?? 0, 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Grand Total:</strong>
                        <strong class="text-primary" style="font-size: 20px;">${{ number_format($sale->grand_total ?? 0, 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Paid Amount:</span>
                        <strong class="text-success">${{ number_format($sale->paid_amount ?? 0, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Due Amount:</span>
                        <strong class="text-danger">${{ number_format($sale->due_amount ?? 0, 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment Method:</span>
                        <strong>{{ ucfirst($sale->payment_method ?? 'N/A') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Payment Status:</span>
                        <span>
                            @if($sale->payment_status === 'paid')
                            <span class="badge bg-success">Paid</span>
                            @elseif($sale->payment_status === 'partial')
                            <span class="badge bg-warning">Partial</span>
                            @else
                            <span class="badge bg-danger">Unpaid</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-warning w-100 mb-2">
                        <i class="bi bi-pencil"></i> Edit Sale
                    </a>
                    <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Delete Sale
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>