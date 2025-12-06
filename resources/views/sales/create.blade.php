<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">Add Sale</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-calculator"></i> POS
                </a>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
        @csrf

        <!-- Sale Information Card -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Sale Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="sale_date" class="form-control @error('sale_date') is-invalid @enderror" 
                               value="{{ old('sale_date', date('Y-m-d')) }}" required>
                        @error('sale_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                        <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Biller <span class="text-danger">*</span></label>
                        <input type="text" name="biller" class="form-control @error('biller') is-invalid @enderror" 
                               value="{{ old('biller', Auth::user()->name) }}" required>
                        @error('biller')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Select Product -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Select Product</h5>
            </div>
            <div class="card-body">
                <select id="productSelect" class="form-select">
                    <option value="">Please type product code and select...</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}" 
                            data-name="{{ $product->name }}" 
                            data-code="PRD-{{ $product->id }}" 
                            data-price="{{ $product->price }}"
                            data-stock="{{ $product->stock }}">
                        PRD-{{ $product->id }} - {{ $product->name }} (Stock: {{ $product->stock }}) - ${{ $product->price }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Order Table -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="orderTable">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                                <th width="80">Action</th>
                            </tr>
                        </thead>
                        <tbody id="orderTableBody">
                            <!-- Products will be added here -->
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="text-end"><strong>Total</strong></td>
                                <td><strong id="totalQuantity">0</strong></td>
                                <td></td>
                                <td><strong id="totalAmount">$0.00</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Additional Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Shipping Cost</label>
                        <input type="number" name="shipping_cost" id="shippingCost" class="form-control" 
                               step="0.01" value="0" min="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Sale Status <span class="text-danger">*</span></label>
                        <select name="sale_status" class="form-select" required>
                            <option value="completed">Completed</option>
                            <option value="pending" selected>Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Payment Status <span class="text-danger">*</span></label>
                        <select name="payment_status" class="form-select" required>
                            <option value="paid">Paid</option>
                            <option value="partial">Partial</option>
                            <option value="unpaid" selected>Unpaid</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Sale Note</label>
                        <textarea name="sale_note" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Staff Note</label>
                        <textarea name="staff_note" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Submit Sale
            </button>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Cancel
            </a>
        </div>
    </form>

    <script>
        let orderItems = [];

        // Add product to order
        document.getElementById('productSelect').addEventListener('change', function() {
            if (!this.value) return;
            
            const option = this.options[this.selectedIndex];
            const productId = this.value;
            const productName = option.dataset.name;
            const productCode = option.dataset.code;
            const productPrice = parseFloat(option.dataset.price);

            // Check if product already exists
            const existingItem = orderItems.find(item => item.productId === productId);
            if (existingItem) {
                alert('Product already added!');
                this.value = '';
                return;
            }

            // Add to order items
            orderItems.push({
                productId: productId,
                name: productName,
                code: productCode,
                quantity: 1,
                price: productPrice,
                subtotal: productPrice
            });

            renderOrderTable();
            this.value = '';
        });

        // Render order table
        function renderOrderTable() {
            const tbody = document.getElementById('orderTableBody');
            tbody.innerHTML = '';

            orderItems.forEach((item, index) => {
                const row = `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.code}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm" value="${item.quantity}" 
                                   min="1" onchange="updateQuantity(${index}, this.value)">
                            <input type="hidden" name="products[${index}][product_id]" value="${item.productId}">
                            <input type="hidden" name="products[${index}][quantity]" value="${item.quantity}">
                            <input type="hidden" name="products[${index}][price]" value="${item.price}">
                        </td>
                        <td>$${item.price.toFixed(2)}</td>
                        <td>$${item.subtotal.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${index})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            calculateTotals();
        }

        // Update quantity
        function updateQuantity(index, quantity) {
            orderItems[index].quantity = parseInt(quantity) || 1;
            orderItems[index].subtotal = orderItems[index].quantity * orderItems[index].price;
            renderOrderTable();
        }

        // Remove item
        function removeItem(index) {
            orderItems.splice(index, 1);
            renderOrderTable();
        }

        // Calculate totals
        function calculateTotals() {
            let totalQuantity = 0;
            let totalAmount = 0;

            orderItems.forEach(item => {
                totalQuantity += item.quantity;
                totalAmount += item.subtotal;
            });

            document.getElementById('totalQuantity').textContent = totalQuantity;
            document.getElementById('totalAmount').textContent = '$' + totalAmount.toFixed(2);
        }

        // Form validation
        document.getElementById('saleForm').addEventListener('submit', function(e) {
            if (orderItems.length === 0) {
                e.preventDefault();
                alert('Please add at least one product!');
                return false;
            }
        });
    </script>
    <script>
    // Warehouse change handler
    document.querySelector('select[name="warehouse_id"]').addEventListener('change', function() {
        const warehouseId = this.value;
        
        if (!warehouseId) {
            return;
        }

        // Show loading
        const productSelect = document.getElementById('productSelect');
        productSelect.innerHTML = '<option value="">Loading products...</option>';
        productSelect.disabled = true;

        // Fetch products for selected warehouse
        fetch(`/sales/get-warehouse-products?warehouse_id=${warehouseId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateProductDropdown(data.products);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load products');
        })
        .finally(() => {
            productSelect.disabled = false;
        });
    });

    function updateProductDropdown(products) {
        const productSelect = document.getElementById('productSelect');
        productSelect.innerHTML = '<option value="">Please type product code and select...</option>';
        
        products.forEach(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.dataset.name = product.name;
            option.dataset.code = `PRD-${product.id}`;
            option.dataset.price = product.price;
            option.dataset.stock = product.stock;
            option.textContent = `PRD-${product.id} - ${product.name} (Stock: ${product.stock}) - $${product.price}`;
            productSelect.appendChild(option);
        });
    }
</script>
</x-app-layout>