<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-truck"></i> Supplier Report
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
                        <form method="GET" action="{{ route('reports.suppliers') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-10">
                                    <label class="form-label">Search Supplier</label>
                                    <input type="text" name="search" class="form-control" placeholder="Search by name, company, or phone" value="{{ request('search') }}">
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
                                        <th>Name</th>
                                        <th>Company</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>City</th>
                                        <th class="text-center">Total Orders</th>
                                        <th class="text-end">Total Purchases</th>
                                        <th class="text-end">Total Paid</th>
                                        <th class="text-end">Total Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($suppliers as $index => $supplier)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $supplier->name }}</td>
                                        <td>{{ $supplier->company ?? 'N/A' }}</td>
                                        <td>{{ $supplier->email ?? 'N/A' }}</td>
                                        <td>{{ $supplier->phone ?? 'N/A' }}</td>
                                        <td>{{ $supplier->city ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $supplier->total_orders }}</span>
                                        </td>
                                        <td class="text-end">৳{{ number_format($supplier->total_purchases, 2) }}</td>
                                        <td class="text-end text-success fw-bold">৳{{ number_format($supplier->total_paid, 2) }}</td>
                                        <td class="text-end text-danger fw-bold">৳{{ number_format($supplier->total_due, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1"></i>
                                            <p class="mb-0">No suppliers found</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if(count($suppliers) > 0)
                                <tfoot class="table-secondary">
                                    <tr class="fw-bold">
                                        <td colspan="7" class="text-end">Grand Total:</td>
                                        <td class="text-end">৳{{ number_format($totals['total_purchases'], 2) }}</td>
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