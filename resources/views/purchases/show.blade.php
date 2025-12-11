<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">Purchase Details</h2>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-sm btn-secondary">
                    <i class="bi bi-printer"></i> Print
                </button>
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
                            <p>{{ $purchase->purchase_date->format('d M Y') }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Supplier:</strong>
                            <p>{{ $purchase->supplier->name ?? 'N/A' }}</p>
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

            <!-- Purchase Items -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Purchase Items</h5>
                </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Net Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->net_unit_cost,2) }}</td>
                                    <td>{{ number_format($item->subtotal,2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>{{ number_format($purchase->total,2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

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

                    <div class="d-flex justify-content-between mb-2">
                        <span>Total:</span>
                        <strong>{{ number_format($purchase->total, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Paid:</span>
                        <strong class="text-success">{{ number_format($purchase->paid_amount, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Due:</span>
                        <strong class="text-danger">{{ number_format($purchase->total - $purchase->paid_amount, 2) }}</strong>
                    </div>

                </div>
            </div>

            @if($purchase->notes)
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Notes</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $purchase->notes }}</p>
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
                              onsubmit="return confirm('Are you sure?')">
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
