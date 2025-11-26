<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">Order #{{ $order->id }}</h2>
    </x-slot>

    <div class="card">
        <div class="card-body">

            <h5>Customer: {{ $order->user->name }}</h5>
            <h5>Total Amount: ${{ number_format($order->total_amount,2) }}</h5>
            <h5>Status: 
                <span class="badge 
                    bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'warning') }}">
                    {{ ucfirst($order->status) }}
                </span>
            </h5>

            <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="mt-3">
                @csrf
                @method('PATCH')
                <label class="form-label">Update Status</label>
                <select name="status" class="form-control w-25 d-inline-block">
                    <option value="pending" {{ $order->status=='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ $order->status=='processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ $order->status=='completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $order->status=='cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Update</button>
            </form>

            <hr>

            <h5>Order Items:</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->orderItems as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>${{ number_format($item->price,2) }}</td>
                                <td>${{ number_format($item->price * $item->quantity,2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <a href="{{ route('orders.index') }}" class="btn btn-secondary mt-3">Back to Orders</a>

        </div>
    </div>

</x-app-layout>
