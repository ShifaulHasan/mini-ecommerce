<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">Sale Returns</h2>
            <a href="{{ route('sale-returns.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Add Sale Return
            </a>
        </div>
    </x-slot>

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

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('sale-returns.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Start Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small">End Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label small">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Search by sale reference..." value="{{ request('search') }}">
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size: 0.875rem;">Show</span>
                    <form action="{{ route('sale-returns.index') }}" method="GET">
                        <select name="per_page" class="form-select form-select-sm" style="width: 80px;" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </form>
                    <span style="font-size: 0.875rem;">entries</span>
                </div>

                <div>
                    <span style="font-size: 0.875rem;">
                        Showing {{ $saleReturns->firstItem() ?? 0 }} to {{ $saleReturns->lastItem() ?? 0 }} of {{ $saleReturns->total() }} entries
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Return Date</th>
                            <th>Sale Reference</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($saleReturns as $return)
                        <tr>
                            <td>#{{ $return->id }}</td>
                            <td>{{ date('M d, Y', strtotime($return->return_date)) }}</td>
                            <td><strong>{{ $return->sale->reference_number }}</strong></td>
                            <td>{{ $return->sale->customer->name ?? 'Walk-in Customer' }}</td>
                            <td>${{ number_format($return->total_amount, 2) }}</td>
                            <td>{{ Str::limit($return->reason, 50) ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('sale-returns.show', $return) }}" class="btn btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="{{ route('sale-returns.destroy', $return) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No sale returns found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $saleReturns->links() }}
            </div>
        </div>
    </div>
</x-app-layout>