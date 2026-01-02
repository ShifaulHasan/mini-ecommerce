<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark"><i class="bi bi-sliders"></i> Stock Adjustments</h2>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        @can('create adjustments')
        <a href="{{ route('adjustments.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Adjustment
        </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date & Time</th>
                            <th>Product</th>
                            <th>Warehouse</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Previous Stock</th>
                            <th>New Stock</th>
                            <th>Reason</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adjustment)
                            <tr>
                                <td>{{ $adjustment->id }}</td>
                                <td>
                                    <small>{{ $adjustment->created_at->format('d M Y') }}</small><br>
                                    <small class="text-muted">{{ $adjustment->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $adjustment->product->name }}</strong><br>
                                    <small class="text-muted"><i class="bi bi-upc"></i> {{ $adjustment->product->product_code ?? 'N/A' }}</small>
                                </td>
                                <td><i class="bi bi-building"></i> {{ $adjustment->warehouse->name }}</td>
                                <td>
                                    @if($adjustment->adjustment_type == 'addition')
                                        <span class="badge bg-success"><i class="bi bi-plus-circle"></i> Addition</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-dash-circle"></i> Subtraction</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="fs-5 {{ $adjustment->adjustment_type == 'addition' ? 'text-success' : 'text-danger' }}">
                                        {{ $adjustment->adjustment_type == 'addition' ? '+' : '-' }}{{ $adjustment->quantity }}
                                    </strong>
                                </td>
                                <td><span class="badge bg-secondary">{{ number_format($adjustment->current_stock) }}</span></td>
                                <td><span class="badge bg-primary">{{ number_format($adjustment->new_stock) }}</span></td>
                                <td>
                                    @if($adjustment->reason)
                                        <small class="text-muted">{{ Str::limit($adjustment->reason, 40) }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($adjustment->creator)
                                        <i class="bi bi-person-circle"></i> {{ $adjustment->creator->name }}
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No adjustments found</p>
                                    <a href="{{ route('adjustments.create') }}" class="btn btn-sm btn-primary mt-2">
                                        <i class="bi bi-plus-circle"></i> Create First Adjustment
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $adjustments->links() }}
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
