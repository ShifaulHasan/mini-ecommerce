<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">Purchase Details</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-md-8">

            <!-- Purchase Information -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Purchase Information</h5>
                </div>
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Reference Number:</strong>
                            <p>{{ $purchase->reference_no }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Purchase Date:</strong>
                            <p>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Supplier:</strong>
                            <p>
                                @if($purchase->supplier_type === 'supplier')
                                    {{ $purchase->supplierModel->name ?? 'N/A' }}
                                    @if($purchase->supplierModel && $purchase->supplierModel->company)
                                        <small class="text-muted">({{ $purchase->supplierModel->company }})</small>
                                    @endif
                                @else
                                    {{ $purchase->userSupplier->name ?? 'N/A' }}
                                    <small class="text-muted">(User Account)</small>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Warehouse:</strong>
                            <p>{{ $purchase->warehouse->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <p>
                                <span class="badge bg-{{ $purchase->purchase_status === 'received' ? 'success' : ($purchase->purchase_status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($purchase->purchase_status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Payment Status:</strong>
                            <p>
                                <span class="badge bg-{{ $purchase->payment_status === 'paid' ? 'success' : ($purchase->payment_status === 'partial' ? 'info' : 'danger') }}">
                                    {{ ucfirst($purchase->payment_status) }}
                                </span>
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Purchased Products -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Purchased Products</h5>
                </div>
                <div class="card-body">
                    @if($purchase->items && count($purchase->items) > 0)
                        @php
                            $itemsTotal = 0;
                        @endphp
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50" class="text-center">#</th>
                                        <th>Product Name</th>
                                        <th width="120" class="text-center">Quantity</th>
                                        <th width="150" class="text-end">Unit Price</th>
                                        <th width="150" class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->items as $index => $item)
                                        @php
                                            $itemSubtotal = $item->quantity * $item->cost_price;
                                            $itemsTotal += $itemSubtotal;
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->product->name ?? 'N/A' }}</strong>
                                                @if($item->product && $item->product->product_code)
                                                    <br><small class="text-muted">Code: {{ $item->product->product_code }}</small>
                                                @endif
                                                @if($item->batch_id)
                                                    <br><small class="text-info">Batch: {{ $item->batch_id }}</small>
                                                @endif
                                                @if($item->expiry_date)
                                                    <br><small class="text-warning">Exp: {{ \Carbon\Carbon::parse($item->expiry_date)->format('d M Y') }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary fs-6">{{ number_format($item->quantity, 0) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <strong>৳{{ number_format($item->cost_price, 2) }}</strong>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-primary fs-6">
                                                    ৳{{ number_format($itemSubtotal, 2) }}
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
                                                ৳{{ number_format($itemsTotal, 2) }}
                                            </strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mb-0">No products found in this purchase</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Payment Summary Sidebar -->
        <div class="col-md-4">

            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Payment Summary</h5>
                </div>
                <div class="card-body">

                    @php
                        // Calculate subtotal from items if not set
                        $subtotal = $purchase->subtotal ?? $purchase->total ?? 0;
                        if ($subtotal == 0 && $purchase->items) {
                            foreach ($purchase->items as $item) {
                                $subtotal += ($item->quantity * $item->cost_price);
                            }
                        }

                        // Get other values
                        $taxAmount = $purchase->tax_amount ?? 0;
                        $discountAmount = $purchase->discount_amount ?? 0;
                        $shippingCost = $purchase->shipping_cost ?? 0;

                        // Calculate grand total
                        $grandTotal = $purchase->grand_total ?? ($subtotal + $taxAmount - $discountAmount + $shippingCost);

                        // Get payment amounts
                        $paidAmount = $purchase->paid_amount ?? 0;
                        $dueAmount = $purchase->due_amount ?? ($grandTotal - $paidAmount);
                    @endphp

                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong>৳{{ number_format($subtotal, 2) }}</strong>
                    </div>

                    @if($taxAmount > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax ({{ $purchase->tax_percentage ?? 0 }}%):</span>
                        <strong class="text-success">৳{{ number_format($taxAmount, 2) }}</strong>
                    </div>
                    @endif

                    @if($discountAmount > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Discount:</span>
                        <strong class="text-danger">-৳{{ number_format($discountAmount, 2) }}</strong>
                    </div>
                    @endif

                    @if($shippingCost > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <strong>৳{{ number_format($shippingCost, 2) }}</strong>
                    </div>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>Grand Total:</strong></span>
                        <strong class="text-primary fs-5">৳{{ number_format($grandTotal, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Paid:</span>
                        <strong class="text-success">৳{{ number_format($paidAmount, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Due:</span>
                        <strong class="text-danger">৳{{ number_format($dueAmount, 2) }}</strong>
                    </div>

                </div>
            </div>

            @if($purchase->payment_method)
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-credit-card"></i> Payment Info</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Payment Method:</strong>
                        <p class="mb-0">{{ ucfirst($purchase->payment_method) }}</p>
                    </div>
                    @if($purchase->account)
                    <!-- <div>
                        <strong>Account:</strong>
                        <p class="mb-0">{{ $purchase->account->account_name }}</p>
                    </div> -->
                    @endif
                </div>
            </div>
            @endif

            @if($purchase->notes)
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-file-text"></i> Notes</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $purchase->notes }}</p>
                </div>
            </div>
            @endif

            @if($purchase->document_path)
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-paperclip"></i> Attachment</h6>
                </div>
                <div class="card-body">
                    <a href="{{ asset($purchase->document_path) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-download"></i> Download Document
                    </a>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">

                        <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Purchase
                        </a>

                        <form action="{{ route('purchases.destroy', $purchase) }}" 
                              method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this purchase?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Delete Purchase
                            </button>
                        </form>

                    </div>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>