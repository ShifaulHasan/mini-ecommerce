<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale List - Modern Layout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
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

        .top-section {
            background: white;
            padding: 1rem 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            border-radius: 8px;
        }

        .icon-btn {
            width: 38px;
            height: 38px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            background: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .icon-btn:hover {
            background: #f3f4f6;
            border-color: var(--purple-primary);
            color: var(--purple-primary);
        }

        .filter-section {
            background: white;
            padding: 1.25rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
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

        .btn-secondary {
            background: white;
            border: 1px solid #e5e7eb;
            color: #374151;
            border-radius: 6px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
        }

        .btn-secondary:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        .table-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .table {
            margin-bottom: 0;
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

        .table tbody tr:nth-child(even) {
            background: #fafafa;
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

        .pagination {
            margin: 0;
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
        }

        .dropdown-menu {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .dropdown-item {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }

        .dropdown-item:hover {
            background: #f3f4f6;
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
</head>
<body>
    @include('layouts.sidebar')

    <div style="margin-left: 250px; padding: 1.5rem;">
        <!-- Top Section -->
        <div class="top-section">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                    <a href="{{ route('sales.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Sale
                    </a>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-upload"></i> Import List
                    </button>
                </div>
                
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('pos.index') }}" class="icon-btn" title="POS">
                        <i class="bi bi-calculator"></i>
                    </a>
                    <button class="icon-btn" onclick="toggleFullscreen()" title="Fullscreen">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                    <button class="icon-btn" onclick="toggleTheme()" title="Dark Mode">
                        <i class="bi bi-moon"></i>
                    </button>
                    <button class="icon-btn" title="Notifications">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">3</span>
                    </button>
                    <div class="dropdown">
                        <button class="icon-btn" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Filter Section -->
        <div class="filter-section">
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

        <!-- Table Section -->
        <div class="table-section">
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
                            <td>{{ date('M d, Y', strtotime($sale->sale_date)) }}</td>
                            <td><strong>{{ $sale->reference_number }}</strong></td>
                            <td>{{ $sale->biller ?? 'N/A' }}</td>
                            <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                            <td>
                                <span class="badge badge-{{ $sale->sale_status }}">
                                    {{ ucfirst($sale->sale_status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $sale->payment_status }}">
                                    {{ ucfirst($sale->payment_status) }}
                                </span>
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</td>
                            <td>{{ $sale->delivery_status ?? 'N/A' }}</td>
                            <td>${{ number_format($sale->grand_total, 2) }}</td>
                            <td>${{ number_format($sale->returned_amount, 2) }}</td>
                            <td>${{ number_format($sale->paid_amount, 2) }}</td>
                            <td>${{ number_format($sale->due_amount, 2) }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                        Action
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('sales.show', $sale) }}"><i class="bi bi-eye"></i> View</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="bi bi-pencil"></i> Edit</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('sales.destroy', $sale) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="14" class="text-center text-muted">No sales found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $sales->links() }}
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Sales</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="file" class="form-control" accept=".xlsx,.csv">
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle"></i> Download sample template
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Import</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }

        function toggleTheme() {
            document.body.classList.toggle('dark-mode');
            alert('Dark mode toggle - implement with localStorage for persistence');
        }
    </script>
</body>
</html>