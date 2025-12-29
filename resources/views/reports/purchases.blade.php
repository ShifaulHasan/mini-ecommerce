<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-bag"></i> Purchase Report
            </h2>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
    </x-slot>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('reports.purchases') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Supplier</label>
                                    <select name="supplier_id" class="form-select">
                                        <option value="">All Suppliers</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Payment Status</label>
                                    <select name="payment_status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-funnel"></i> Filter
                                    </button>
                                    <a href="{{ route('reports.purchases') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 opacity-75">Total Purchases</h6>
                                        <h3 class="card-title mb-0">৳{{ number_format($totals['total_purchases'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 opacity-75">Total Paid</h6>
                                        <h3 class="card-title mb-0">৳{{ number_format($totals['total_paid'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 opacity-75">Total Due</h6>
                                        <h3 class="card-title mb-0">৳{{ number_format($totals['total_due'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Report Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>SL</th>
                                        <th>Date</th>
                                        <th>Reference No</th>
                                        <th>Supplier</th>
                                        <th>Warehouse</th>
                                        <th class="text-end">Total Amount</th>
                                        <th class="text-end">Paid</th>
                                        <th class="text-end">Due</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Payment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($purchases as $index => $purchase)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ date('d-m-Y', strtotime($purchase->purchase_date)) }}</td>
                                        <td><span class="badge bg-secondary">{{ $purchase->reference_no }}</span></td>
                                        <td>{{ $purchase->supplier_name }}</td>
                                        <td>{{ $purchase->warehouse_name ?? 'N/A' }}</td>
                                        <td class="text-end">৳{{ number_format($purchase->grand_total, 2) }}</td>
                                        <td class="text-end text-success">৳{{ number_format($purchase->paid_amount, 2) }}</td>
                                        <td class="text-end text-danger">৳{{ number_format($purchase->due_amount, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $purchase->purchase_status == 'received' ? 'success' : 'warning' }}">
                                                {{ ucfirst($purchase->purchase_status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $purchase->payment_status == 'paid' ? 'success' : ($purchase->payment_status == 'partial' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($purchase->payment_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1"></i>
                                            <p class="mb-0">No purchases found</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if(count($purchases) > 0)
                                <tfoot class="table-secondary">
                                    <tr class="fw-bold">
                                        <td colspan="5" class="text-end">Grand Total:</td>
                                        <td class="text-end">৳{{ number_format($totals['total_purchases'], 2) }}</td>
                                        <td class="text-end text-success">৳{{ number_format($totals['total_paid'], 2) }}</td>
                                        <td class="text-end text-danger">৳{{ number_format($totals['total_due'], 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .btn, form, .navbar, .sidebar { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</x-app-layout>