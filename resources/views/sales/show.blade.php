<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-receipt"></i> Sale Details
            </h2>
            <!-- <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-sm btn-secondary">
                    <i class="bi bi-printer"></i> Print
                </button> -->
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-md-8">
            <!-- Sale Information -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Sale Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Reference No:</strong>
                            <p>{{ $sale->reference_number }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Date:</strong>
                            <p>{{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Customer:</strong>
                            <p>{{ optional($sale->customer)->name ?? 'Walk-in Customer' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Biller:</strong>
                            <p>{{ $sale->biller ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Warehouse:</strong>
                            <p>{{ optional($sale->warehouse)->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong>
                            <p>
                                @if($sale->sale_status === 'completed')
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
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-cart3"></i> Sale Items</h5>
                </div>
                <div class="card-body">
                    @if($sale->items && count($sale->items) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50" class="text-center">#</th>
                                        <th>Product</th>
                                        <th width="120" class="text-center">Quantity</th>
                                        <th width="150" class="text-end">Unit Price</th>
                                        <th width="150" class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->items as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ optional($item->product)->name ?? 'N/A' }}</strong>
                                            @if($item->product && $item->product->product_code)
                                                <br><small class="text-muted">Code: {{ $item->product->product_code }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary fs-6">{{ number_format($item->quantity, 0) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <strong>৳{{ number_format($item->unit_price ?? $item->price ?? 0, 2) }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-primary fs-6">
                                                ৳{{ number_format($item->quantity * ($item->unit_price ?? $item->price ?? 0), 2) }}
                                            </strong>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total Amount:</strong></td>
                                        <td class="text-end">
                                            <strong class="text-primary fs-5">
                                                ৳{{ number_format($sale->grand_total ?? 0, 2) }}
                                            </strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mb-0">No items found in this sale</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Payment Summary -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Payment Summary</h5>
                </div>
                <div class="card-body">
                    @php
                        // Calculate subtotal from items
                        $itemsSubtotal = 0;
                        foreach($sale->items as $item) {
                            $itemsSubtotal += $item->quantity * ($item->unit_price ?? $item->price ?? 0);
                        }
                        
                        // Use database subtotal if available, otherwise use calculated
                        $subtotal = ($sale->subtotal && $sale->subtotal > 0) ? $sale->subtotal : $itemsSubtotal;
                        
                        // Get values from database (default to 0)
                        $taxAmount = $sale->tax_amount ?? 0;
                        $discountAmount = $sale->discount_amount ?? 0;
                        $shippingAmount = $sale->shipping_amount ?? 0;
                        
                        // Calculate tax percentage if tax exists
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

            @if($sale->payment_method)
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-credit-card"></i> Payment Info</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Payment Method:</strong>
                        <p class="mb-0">{{ ucfirst($sale->payment_method) }}</p>
                    </div>
                    <!-- @if($sale->account)
                    <div>
                        <strong>Account:</strong>
                        <p class="mb-0">{{ $sale->account->account_name }}</p>
                    </div>
                    @endif -->
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Sale
                        </a>
                        <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this sale?')">
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
    </div>
</x-app-layout>