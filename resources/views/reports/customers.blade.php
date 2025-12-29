<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-people"></i> Customer Report
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
                        <form method="GET" action="{{ route('reports.customers') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-10">
                                    <label class="form-label">Search Customer</label>
                                    <input type="text" name="search" class="form-control" placeholder="Search by name, code, or phone" value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 opacity-75">Total Sales</h6>
                                        <h3 class="card-title mb-0">৳{{ number_format($totals['total_sales'], 2) }}</h3>
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
                                        <th>Customer Code</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>City</th>
                                        <th class="text-center">Total Orders</th>
                                        <th class="text-end">Total Sales</th>
                                        <th class="text-end">Total Paid</th>
                                        <th class="text-end">Total Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customers as $index => $customer)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge bg-secondary">{{ $customer->customer_code }}</span></td>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->email ?? 'N/A' }}</td>
                                        <td>{{ $customer->phone ?? 'N/A' }}</td>
                                        <td>{{ $customer->city ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $customer->total_orders }}</span>
                                        </td>
                                        <td class="text-end">৳{{ number_format($customer->total_sales, 2) }}</td>
                                        <td class="text-end text-success fw-bold">৳{{ number_format($customer->total_paid, 2) }}</td>
                                        <td class="text-end text-danger fw-bold">৳{{ number_format($customer->total_due, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1"></i>
                                            <p class="mb-0">No customers found</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if(count($customers) > 0)
                                <tfoot class="table-secondary">
                                    <tr class="fw-bold">
                                        <td colspan="7" class="text-end">Grand Total:</td>
                                        <td class="text-end">৳{{ number_format($totals['total_sales'], 2) }}</td>
                                        <td class="text-end text-success">৳{{ number_format($totals['total_paid'], 2) }}</td>
                                        <td class="text-end text-danger">৳{{ number_format($totals['total_due'], 2) }}</td>
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