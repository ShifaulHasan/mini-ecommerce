<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-cart-check"></i> Sales List
            </h2>
            <div>
                @can('create pos')
                <a href="{{ route('pos.index') }}" class="btn btn-success me-2">
                    <i class="bi bi-shop"></i> POS
                </a>
                @endcan
                @can('create sales')
                <a href="{{ route('sales.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Sale
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <style>
        :root {
            --purple-primary: #6f42c1;
            --purple-light: #8b5cf6;
            --green-success: #10b981;
            --red-danger: #ef4444;
            --blue-info: #3b82f6;
            --orange-warning: #f59e0b;
        }

        body {
            background: #f8f9fa;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .filter-section .form-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 0.4rem;
        }

        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--purple-primary);
            box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.1);
        }

        .btn-primary {
            background: var(--purple-primary);
            border-color: var(--purple-primary);
            border-radius: 6px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: var(--purple-light);
            border-color: var(--purple-light);
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            padding: 0.75rem;
            white-space: nowrap;
        }

        .table tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }

        .table tbody td {
            padding: 0.875rem 0.75rem;
            font-size: 0.875rem;
            vertical-align: middle;
        }

        .badge {
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-completed { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        .badge-paid { background: #d1fae5; color: #065f46; }
        .badge-partial { background: #dbeafe; color: #1e40af; }
        .badge-unpaid { background: #fee2e2; color: #991b1b; }

        /* Pagination styling - arrows removed via custom blade file */
        .pagination {
            margin: 0;
            display: flex;
            gap: 5px;
        }

        .page-link {
            border-radius: 6px;
            margin: 0 2px;
            border: 1px solid #e5e7eb;
            color: #374151;
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }

        .page-link:hover {
            background: #f3f4f6;
            border-color: var(--purple-primary);
            color: var(--purple-primary);
        }

        .page-item.active .page-link {
            background: var(--purple-primary);
            border-color: var(--purple-primary);
            color: white;
        }

        .page-item.disabled .page-link {
            color: #6c757d;
            cursor: not-allowed;
        }

        .search-box {
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            padding: 0.5rem 1rem;
            padding-left: 2.5rem;
            background: white;
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
    </style>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filter Section -->
    <div class="card mb-3 filter-section">
        <div class="card-body">
            <form action="{{ route('sales.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Warehouse</label>
                        <select name="warehouse_id" class="form-select">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Sale Status</label>
                        <select name="sale_status" class="form-select">
                            <option value="">All Status</option>
                            <option value="completed" {{ request('sale_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending" {{ request('sale_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="cancelled" {{ request('sale_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <option value="">All Status</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Submit
                        </button>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-2">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                            <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="mobile_payment" {{ request('payment_method') == 'mobile_payment' ? 'selected' : '' }}>Mobile Payment</option>
                        </select>
                    </div>

                    <div class="col-md-8"></div>

                    <div class="col-md-2">
                        <label class="form-label">Search</label>
                        <div class="position-relative">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" name="search" class="form-control search-box" placeholder="Search..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size: 0.875rem; color: #6b7280;">Show</span>
                    <form action="{{ route('sales.index') }}" method="GET">
                        <select name="per_page" class="form-select form-select-sm" style="width: 80px;" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </form>
                    <span style="font-size: 0.875rem; color: #6b7280;">entries</span>
                </div>

                <div>
                    <span style="font-size: 0.875rem; color: #6b7280;">
                        Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} entries
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="form-check-input"></th>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Biller</th>
                            <th>Customer</th>
                            <th>Sale Status</th>
                            <th>Payment Status</th>
                            <th>Payment Method</th>
                            <th>Delivery Status</th>
                            <th>Grand Total</th>
                            <th>Returned</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td><input type="checkbox" class="form-check-input"></td>
                            <td>{{ $sale->sale_date ? $sale->sale_date->format('M d, Y') : 'N/A' }}</td>
                            <td><strong>{{ $sale->reference_number ?? $sale->reference_no }}</strong></td>
                            <td>{{ $sale->biller ?? 'N/A' }}</td>
                            <td>{{ optional($sale->customer)->name ?? 'Walk-in' }}</td>
                            <td>
                                @if($sale->sale_status == 'completed' || $sale->status == 'completed')
                                <span class="badge badge-completed">Completed</span>
                                @elseif($sale->sale_status == 'pending' || $sale->status == 'pending')
                                <span class="badge badge-pending">Pending</span>
                                @else
                                <span class="badge badge-cancelled">Cancelled</span>
                                @endif
                            </td>
                            <td>
                                @if($sale->payment_status === 'paid')
                                <span class="badge badge-paid">Paid</span>
                                @elseif($sale->payment_status === 'partial')
                                <span class="badge badge-partial">Partial</span>
                                @else
                                <span class="badge badge-unpaid">Unpaid</span>
                                @endif
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $sale->payment_method ?? 'N/A')) }}</td>
                            <td>{{ $sale->delivery_status ?? 'N/A' }}</td>
                            <td>৳{{ number_format($sale->grand_total ?? 0, 2) }}</td>
                            <td>৳{{ number_format($sale->returned_amount ?? 0, 2) }}</td>
                            <td>৳{{ number_format($sale->paid_amount ?? 0, 2) }}</td>
                            <td>৳{{ number_format($sale->due_amount ?? 0, 2) }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this sale?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="14" class="text-center text-muted py-4">No sales found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} entries
                </div>
                <div>
                    {{ $sales->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    </div> 

    <!-- Footer Note -->
    <div class="row mt-4 mb-3">
        <div class="col-12">
            <p class="text-center text-muted small mb-0">
                Developed by Shifaul Hasan &copy; 2026
            </p>
        </div>
    </div>

</div>

</x-app-layout>