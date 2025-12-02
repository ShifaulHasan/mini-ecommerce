<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">Add Purchase</h2>
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </x-slot>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
        @csrf
        
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Purchase Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Reference Number</label>
                                <input type="text" class="form-control" value="{{ $referenceNumber }}" readonly>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
                                <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" 
                                       value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                                @error('purchase_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Warehouse</label>
                                <select name="warehouse_id" class="form-select">
                                    <option value="">Select Warehouse</option>
                                    @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Purchase Status</label>
                            <select name="purchase_status" class="form-select">
                                <option value="pending" selected>Pending</option>
                                <option value="received">Received</option>
                            </select>
                            <small class="text-muted">Select "Received" to automatically update stock</small>
                        </div>
                    </div>
                </div>

                <!-- Products Section -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Products</h5>
                    </div>
                    <div class="card-body">
                        <div id="productRows">
                            <div class="row mb-2 product-row">
                                <div class="col-md-4">
                                    <label class="form-label small">Product</label>
                                    <select name="products[0][product_id]" class="form-select form-select-sm product-select" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                            {{ $product->name }} (Stock: {{ $product->stock }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Quantity</label>
                                    <input type="number" name="products[0][quantity]" class="form-control form-control-sm quantity-input" 
                                           placeholder="Qty" min="1" value="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Price</label>
                                    <input type="number" name="products[0][price]" class="form-control form-control-sm price-input" 
                                           placeholder="Price" step="0.01" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Subtotal</label>
                                    <input type="text" class="form-control form-control-sm subtotal" readonly>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small">&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-sm w-100 remove-row">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-secondary btn-sm mt-2" id="addRow">
                            <i class="bi bi-plus-circle"></i> Add Product
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column - Summary -->
            <div class="col-md-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Purchase Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Grand Total</label>
                            <input type="text" id="grandTotal" class="form-control fw-bold text-primary" readonly value="$0.00">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Paid Amount</label>
                            <input type="number" name="paid_amount" id="paidAmount" class="form-control" 
                                   step="0.01" min="0" value="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Due Amount</label>
                            <input type="text" id="dueAmount" class="form-control text-danger fw-bold" readonly value="$0.00">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Status</label>
                            <input type="text" id="paymentStatus" class="form-control" readonly value="Unpaid">
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Save Purchase
                            </button>
                            <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        let rowIndex = 1;

        // Add product row
        document.getElementById('addRow').addEventListener('click', function() {
            const newRow = document.querySelector('.product-row').cloneNode(true);
            newRow.querySelectorAll('input').forEach(input => input.value = '');
            newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
            newRow.querySelectorAll('select, input').forEach(el => {
                if (el.name) {
                    el.name = el.name.replace(/\[\d+\]/, `[${rowIndex}]`);
                }
            });
            document.getElementById('productRows').appendChild(newRow);
            rowIndex++;
            attachRowEvents(newRow);
        });

        // Remove row
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-row')) {
                if (document.querySelectorAll('.product-row').length > 1) {
                    e.target.closest('.product-row').remove();
                    calculateTotal();
                } else {
                    alert('At least one product is required!');
                }
            }
        });

        // Calculate subtotal and total
        function attachRowEvents(row) {
            const select = row.querySelector('.product-select');
            const quantity = row.querySelector('.quantity-input');
            const price = row.querySelector('.price-input');
            const subtotal = row.querySelector('.subtotal');

            select.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                price.value = option.dataset.price || 0;
                calculateRow(row);
            });

            quantity.addEventListener('input', () => calculateRow(row));
            price.addEventListener('input', () => calculateRow(row));
        }

        function calculateRow(row) {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const subtotal = row.querySelector('.subtotal');
            subtotal.value = '$' + (quantity * price).toFixed(2);
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal').forEach(input => {
                const value = parseFloat(input.value.replace('$', '')) || 0;
                total += value;
            });
            
            document.getElementById('grandTotal').value = '$' + total.toFixed(2);
            calculateDue();
        }

        function calculateDue() {
            const total = parseFloat(document.getElementById('grandTotal').value.replace('$', '')) || 0;
            const paid = parseFloat(document.getElementById('paidAmount').value) || 0;
            const due = total - paid;
            
            document.getElementById('dueAmount').value = '$' + due.toFixed(2);
            
            // Update payment status
            let status = 'Unpaid';
            if (paid >= total && total > 0) {
                status = 'Paid';
            } else if (paid > 0) {
                status = 'Partial';
            }
            document.getElementById('paymentStatus').value = status;
        }

        document.getElementById('paidAmount').addEventListener('input', calculateDue);

        // Initialize events for first row
        attachRowEvents(document.querySelector('.product-row'));
    </script>
</x-app-layout>