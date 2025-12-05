<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">Add Sale Return</h2>
            <a href="{{ route('sale-returns.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </x-slot>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('sale-returns.store') }}" method="POST" id="returnForm">
        @csrf

        <!-- Sale Information -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Sale Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Select Sale <span class="text-danger">*</span></label>
                        <select name="sale_id" id="saleSelect" class="form-select @error('sale_id') is-invalid @enderror" required>
                            <option value="">Choose a completed sale...</option>
                            @foreach($sales as $sale)
                            <option value="{{ $sale->id }}">
                                {{ $sale->reference_number }} - {{ $sale->customer->name ?? 'Walk-in' }} - ${{ number_format($sale->grand_total, 2) }}
                            </option>
                            @endforeach
                        </select>
                        @error('sale_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Return Date <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" class="form-control @error('return_date') is-invalid @enderror" 
                               value="{{ old('return_date', date('Y-m-d')) }}" required>
                        @error('return_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Customer</label>
                        <input type="text" id="customerName" class="form-control" readonly placeholder="Select sale first">
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Reason for Return</label>
                    <textarea name="reason" class="form-control" rows="2" placeholder="Enter reason for return...">{{ old('reason') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Sale Items -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Items to Return</h5>
            </div>
            <div class="card-body">
                <div id="itemsContainer">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Please select a sale to view items
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Total Return Amount:</strong>
                            <strong class="text-danger" id="totalAmount">$0.00</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="mb-3">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle"></i> Process Return
            </button>
            <a href="{{ route('sale-returns.index') }}" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Cancel
            </a>
        </div>
    </form>

    <script>
    document.getElementById('saleSelect').addEventListener('change', function() {
        const saleId = this.value;
        
        if (!saleId) {
            document.getElementById('itemsContainer').innerHTML = '<div class="alert alert-info"><i class="bi bi-info-circle"></i> Please select a sale to view items</div>';
            document.getElementById('customerName').value = '';
            return;
        }

        // Show loading
        document.getElementById('itemsContainer').innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Loading items...</div>';

        // Fetch sale items - FIXED URL
        fetch(`{{ url('/sale-returns/get-items') }}/${saleId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('customerName').value = data.customer;
                renderItems(data.items);
            } else {
                document.getElementById('itemsContainer').innerHTML = '<div class="alert alert-danger">Error loading items</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('itemsContainer').innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Failed to load sale items. Please try again.</div>';
        });
    });

    function renderItems(items) {
        if (items.length === 0) {
            document.getElementById('itemsContainer').innerHTML = '<div class="alert alert-warning">No items found in this sale</div>';
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-bordered"><thead class="table-light"><tr><th width="5%"><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th><th>Product</th><th width="15%">Sold Qty</th><th width="15%">Return Qty</th><th width="15%">Price</th><th width="15%">Subtotal</th></tr></thead><tbody>';

        items.forEach((item, index) => {
            html += `
                <tr id="row_${index}">
                    <td class="text-center">
                        <input type="checkbox" class="item-checkbox" data-index="${index}" checked>
                    </td>
                    <td>${item.product_name}</td>
                    <td>${item.quantity_sold}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm return-qty" 
                               id="qty_${index}" min="1" max="${item.quantity_sold}" 
                               value="1" data-index="${index}" data-price="${item.price}"
                               onchange="calculateSubtotal(${index})">
                    </td>
                    <td>$${parseFloat(item.price).toFixed(2)}</td>
                    <td class="subtotal" id="subtotal_${index}">$${parseFloat(item.price).toFixed(2)}</td>
                    <input type="hidden" name="products[${index}][product_id]" value="${item.product_id}">
                    <input type="hidden" name="products[${index}][quantity]" id="hidden_qty_${index}" value="1">
                    <input type="hidden" name="products[${index}][price]" value="${item.price}">
                </tr>
            `;
        });

        html += '</tbody></table></div>';
        document.getElementById('itemsContainer').innerHTML = html;
        calculateTotal();
    }

    function toggleAll(checkbox) {
        document.querySelectorAll('.item-checkbox').forEach(cb => {
            cb.checked = checkbox.checked;
        });
        calculateTotal();
    }

    function calculateSubtotal(index) {
        const qty = parseFloat(document.getElementById(`qty_${index}`).value) || 0;
        const price = parseFloat(document.getElementById(`qty_${index}`).dataset.price) || 0;
        const subtotal = qty * price;
        
        document.getElementById(`subtotal_${index}`).textContent = '$' + subtotal.toFixed(2);
        document.getElementById(`hidden_qty_${index}`).value = qty;
        
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
            const index = checkbox.dataset.index;
            const qty = parseFloat(document.getElementById(`qty_${index}`).value) || 0;
            const price = parseFloat(document.getElementById(`qty_${index}`).dataset.price) || 0;
            total += qty * price;
        });
        
        document.getElementById('totalAmount').textContent = '$' + total.toFixed(2);
    }

    // Form validation
    document.getElementById('returnForm').addEventListener('submit', function(e) {
        const checkedItems = document.querySelectorAll('.item-checkbox:checked').length;
        
        if (checkedItems === 0) {
            e.preventDefault();
            alert('Please select at least one item to return!');
            return false;
        }

        // Remove unchecked items from submission
        document.querySelectorAll('.item-checkbox:not(:checked)').forEach(checkbox => {
            const index = checkbox.dataset.index;
            const row = document.getElementById(`row_${index}`);
            if (row) {
                row.querySelectorAll('input[name^="products"]').forEach(input => {
                    input.remove();
                });
            }
        });
    });
</script>
</x-app-layout>