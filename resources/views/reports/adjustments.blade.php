<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-arrow-left-right"></i> Stock Adjustment Report
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
                        <form method="GET" action="{{ route('reports.adjustments') }}" class="mb-4">
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
                                    <label class="form-label">Product</label>
                                    <select name="product_id" class="form-select">
                                        <option value="">All Products</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Adjustment Type</label>
                                    <select name="adjustment_type" class="form-select">
                                        <option value="">All Types</option>
                                        <option value="addition" {{ request('adjustment_type') == 'addition' ? 'selected' : '' }}>Addition</option>
                                        <option value="subtraction" {{ request('adjustment_type') == 'subtraction' ? 'selected' : '' }}>Subtraction</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-funnel"></i> Filter
                                    </button>
                                    <a href="{{ route('reports.adjustments') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Report Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>SL</th>
                                        <th>Date</th>
                                        <th>Product Code</th>
                                        <th>Product Name</th>
                                        <th>Warehouse</th>
                                        <th class="text-center">Type</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-center">Current Stock</th>
                                        <th class="text-center">New Stock</th>
                                        <th>Reason</th>
                                        <th>Created By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($adjustments as $index => $adjustment)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ date('d-m-Y H:i', strtotime($adjustment->adjustment_date)) }}</td>
                                        <td><span class="badge bg-secondary">{{ $adjustment->product_code }}</span></td>
                                        <td>{{ $adjustment->product_name }}</td>
                                        <td>{{ $adjustment->warehouse_name }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $adjustment->adjustment_type == 'addition' ? 'success' : 'danger' }}">
                                                <i class="bi bi-{{ $adjustment->adjustment_type == 'addition' ? 'plus-circle' : 'dash-circle' }}"></i>
                                                {{ ucfirst($adjustment->adjustment_type) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $adjustment->adjustment_type == 'addition' ? 'success' : 'danger' }}">
                                                {{ $adjustment->adjustment_type == 'addition' ? '+' : '-' }}{{ $adjustment->quantity }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $adjustment->current_stock }}</td>
                                        <td class="text-center">
                                            <strong>{{ $adjustment->new_stock }}</strong>
                                        </td>
                                        <td>{{ $adjustment->reason ?? 'N/A' }}</td>
                                        <td>{{ $adjustment->created_by_name ?? 'System' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1"></i>
                                            <p class="mb-0">No adjustments found</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
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