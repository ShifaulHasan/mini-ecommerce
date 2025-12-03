<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">Sale Details</h2>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-sm btn-secondary">
                    <i class="bi bi-printer"></i> Print
                </button>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <!-- Sale Information -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Sale Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Reference Number:</strong>
                            <p class="mb-2">{{ $sale->reference_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Sale Date:</strong>
                            <p class="mb-2">{{ date('M d, Y', strtotime($sale->sale_date)) }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Customer:</strong>
                            <p class="mb-2">{{ $sale->customer->name ?? 'Walk-in Customer' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Warehouse:</strong>
                            <p class="mb-2">{{ $sale->warehouse->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Biller:</strong>
                            <p class="mb-2">{{ $sale->biller ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Sale Status:</strong>
                            <p class="mb-2">
                                <span class="badge bg-{{ $sale->sale_status == 'completed' ? 'success' : ($sale->sale_status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($sale->sale_status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sale Items -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Sale Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Code</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->saleItems as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>PRD-{{ $item->product->id }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->price, 2) }}</td>
                                    <td>${{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                                    <td><strong>${{ number_format($sale->grand_total, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Payment Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Grand Total:</span>
                        <strong>${{ number_format($sale->grand_total, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping Cost:</span>
                        <strong>${{ number_format($sale->shipping_cost ?? 0, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Order Tax:</span>
                        <strong>${{ number_format($sale->order_tax ?? 0, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Discount:</span>
                        <strong>${{ number_format($sale->order_discount ?? 0, 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Paid Amount:</span>
                        <strong class="text-success">${{ number_format($sale->paid_amount, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Due Amount:</span>
                        <strong class="text-danger">${{ number_format($sale->due_amount, 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span><strong>Payment Status:</strong></span>
                        <span class="badge bg-{{ $sale->payment_status == 'paid' ? 'success' : ($sale->payment_status == 'partial' ? 'warning' : 'danger') }}">
                            {{ ucfirst($sale->payment_status) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span><strong>Payment Method:</strong></span>
                        <span>{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes Card -->
            @if($sale->sale_note || $sale->staff_note)
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Notes</h6>
                </div>
                <div class="card-body">
                    @if($sale->sale_note)
                    <div class="mb-2">
                        <strong>Sale Note:</strong>
                        <p class="mb-0 text-muted">{{ $sale->sale_note }}</p>
                    </div>
                    @endif
                    
                    @if($sale->staff_note)
                    <div>
                        <strong>Staff Note:</strong>
                        <p class="mb-0 text-muted">{{ $sale->staff_note }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Sale
                        </a>
                        <form action="{{ route('sales.destroy', $sale) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this sale?')">
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

    <!-- Print Styles -->
    <style>
        @media print {
            .btn, .card-header { display: none !important; }
            body { background: white !important; }
            .card { border: 1px solid #ddd !important; box-shadow: none !important; }
        }
    </style>
</x-app-layout>