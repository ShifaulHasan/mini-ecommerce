<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">Purchase List</h2>
            <div>
                <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm me-2">
                    <i class="bi bi-plus-circle"></i> Add Purchase
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mt-3 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('purchases.index') }}">
                <div class="row g-3">

                    <div class="col-md-2">
                        <label class="form-label small">Start Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm"
                               value="{{ request('start_date') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small">End Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm"
                               value="{{ request('end_date') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small">Warehouse</label>
                        <select name="warehouse_id" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" 
                                    {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small">Payment Status</label>
                        <select name="payment_status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="unpaid" {{ request('payment_status')=='unpaid'?'selected':'' }}>Unpaid</option>
                            <option value="partial" {{ request('payment_status')=='partial'?'selected':'' }}>Partial</option>
                            <option value="paid" {{ request('payment_status')=='paid'?'selected':'' }}>Paid</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body">

            <!-- Search -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <form method="GET" class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm"
                               name="search" value="{{ request('search') }}"
                               placeholder="Search reference / supplier">

                        <button class="btn btn-sm btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>

                <div class="col-md-8 text-end">
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Supplier</th>
                            <th>Warehouse</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->purchase_date->format('d M Y') }}</td>

                            <td><b>{{ $purchase->reference_no }}</b></td>

                            <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>

                            <td>{{ $purchase->warehouse->name ?? 'N/A' }}</td>

                            <td>৳{{ number_format($purchase->total,2) }}</td>

                            <td>৳{{ number_format($purchase->paid_amount,2) }}</td>

                            <td>৳{{ number_format($purchase->due_amount,2) }}</td>

                            <td>
                                @if($purchase->payment_status=='paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($purchase->payment_status=='partial')
                                    <span class="badge bg-info">Partial</span>
                                @else
                                    <span class="badge bg-danger">Unpaid</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('purchases.show',$purchase) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="{{ route('purchases.edit',$purchase) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="{{ route('purchases.destroy',$purchase) }}" method="POST" 
                                      class="d-inline-block"
                                      onsubmit="return confirm('Delete this purchase?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p>No purchases found</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>

</x-app-layout>
