<x-app-sidebar>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">Purchase List</h2>
            <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Purchase
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $purchase)
                        <tr>
                            <td>#{{ $purchase->id }}</td>
                            <td>{{ $purchase->supplier->name }}</td>
                            <td>{{ date('M d, Y', strtotime($purchase->purchase_date)) }}</td>
                            <td>${{ number_format($purchase->total_amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $purchase->status == 'received' ? 'success' : 'warning' }}">
                                    {{ ucfirst($purchase->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $purchases->links() }}
        </div>
    </div>
</x-app-sidebar>