<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">Sale Return Details</h2>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-sm btn-secondary">
                    <i class="bi bi-printer"></i> Print
                </button>
                <a href="{{ route('sale-returns.index') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-md-8">
            <!-- Return Information -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Return Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Return ID:</strong>
                            <p>#{{ $saleReturn->id }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Return Date:</strong>
                            <p>{{ date('M d, Y', strtotime($saleReturn->return_date)) }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Original Sale:</strong>
                            <p>{{ $saleReturn->sale->reference_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Customer:</strong>
                            <p>{{ $saleReturn->sale->customer->name ?? 'Walk-in Customer' }}</p>
                        </div>
                    </div>

                    @if($saleReturn->reason)
                    <div class="row">
                        <div class="col-12">
                            <strong>Reason:</strong>
                            <p>{{ $saleReturn->reason }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Returned Items -->
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Returned Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($saleReturn->saleReturnItems as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->price, 2) }}</td>
                                    <td>${{ number_format($item->quantity * $item->price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total Return Amount:</strong></td>
                                    <td><strong class="text-danger">${{ number_format($saleReturn->total_amount, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Return Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Amount:</span>
                        <strong class="text-danger">${{ number_format($saleReturn->total_amount, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Refund Status:</span>
                        <span class="badge bg-success">Processed</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid">
                        <form action="{{ route('sale-returns.destroy', $saleReturn) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Delete Return
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>