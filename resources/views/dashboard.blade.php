<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">Dashboard</h2>
    </x-slot>

    <div class="card">
        <div class="card-body">

            <!-- Live Search -->
            <input type="text" id="search" class="form-control mb-3" placeholder="Search products...">

            <!-- Products Table -->
            <div class="table-responsive">
                <table class="table table-hover" id="productsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Models\Product::with('category')->get() as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category->name }}</td>
                                <td>${{ number_format($product->price,2) }}</td>
                                <td>{{ $product->stock }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- AJAX Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const tableBody = document.querySelector('#productsTable tbody');

            searchInput.addEventListener('keyup', function() {
                const query = this.value;

                fetch(`{{ route('dashboard.products.ajaxSearch') }}?query=${query}`)
                    .then(response => response.json())
                    .then(data => {
                        tableBody.innerHTML = '';

                        if(data.length > 0){
                            data.forEach(product => {
                                const row = `<tr>
                                    <td>${product.id}</td>
                                    <td>${product.name}</td>
                                    <td>${product.category ? product.category.name : 'N/A'}</td>
                                    <td>$${parseFloat(product.price).toFixed(2)}</td>
                                    <td>${product.stock}</td>
                                </tr>`;
                                tableBody.innerHTML += row;
                            });
                        } else {
                            tableBody.innerHTML = `<tr><td colspan="5" class="text-center">No products found</td></tr>`;
                        }
                    });
            });
        });
    </script>

</x-app-layout>
