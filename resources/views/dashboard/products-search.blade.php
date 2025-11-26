<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">Search Results for "{{ $query }}"</h2>
    </x-slot>

    <div class="card">
        <div class="card-body">

            @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name }}</td>
                            <td>${{ number_format($product->price,2) }}</td>
                            <td>{{ $product->stock }}</td>
                            <td>
                                @if($product->image)
                                <img src="{{ asset('images/products/'.$product->image) }}" width="50" alt="product">
                                @else
                                N/A
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <p class="text-center">No products found.</p>
            @endif

            <a href="{{ route('dashboard') }}" class="btn btn-secondary mt-3">Back to Dashboard</a>

        </div>
    </div>
</x-app-layout>
