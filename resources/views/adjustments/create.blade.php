
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">
            
            <i class="bi bi-plus-circle"></i> Add Stock Adjustment
          
        </h2>

    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('adjustments.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-sliders"></i> Adjustment Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('adjustments.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <!-- Warehouse -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="bi bi-building"></i> Select Warehouse <span class="text-danger">*</span></label>
                                <select name="warehouse_id" id="warehouseSelect" class="form-select @error('warehouse_id') is-invalid @enderror" required onchange="updateStock()">
                                    <option value="">-- Choose Warehouse --</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Product -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="bi bi-box"></i> Select Product <span class="text-danger">*</span></label>
                                <select name="product_id" id="productSelect" class="form-select @error('product_id') is-invalid @enderror" required onchange="updateStock()">
                                    <option value="">-- Choose Product --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }} ({{ $product->product_code ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Stock Info -->
                            <div class="col-12">
                                <div class="alert alert-info d-none" id="stockInfo">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <strong>Product:</strong> <p class="mb-0" id="productName">-</p>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Product Code:</strong> <p class="mb-0" id="productCode">-</p>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Current Stock:</strong>
                                            <p class="mb-0 fs-4 text-primary">
                                                <i class="bi bi-box-seam"></i> <span id="currentStock">0</span> units
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Adjustment Type -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="bi bi-arrow-repeat"></i> Adjustment Type <span class="text-danger">*</span></label>
                                <select name="adjustment_type" class="form-select @error('adjustment_type') is-invalid @enderror" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="addition" {{ old('adjustment_type') == 'addition' ? 'selected' : '' }}>➕ Addition (Increase Stock)</option>
                                    <option value="subtraction" {{ old('adjustment_type') == 'subtraction' ? 'selected' : '' }}>➖ Subtraction (Decrease Stock)</option>
                                </select>
                                @error('adjustment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Quantity -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="bi bi-123"></i> Adjustment Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control form-control-lg @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" min="1" placeholder="Enter quantity to adjust" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Reason -->
                            <div class="col-12">
                                <label class="form-label fw-bold"><i class="bi bi-chat-left-text"></i> Reason for Adjustment</label>
                                <textarea name="reason" class="form-control" rows="3" placeholder="e.g., Damaged goods, Stock correction, Returns...">{{ old('reason') }}</textarea>
                                <small class="text-muted"><i class="bi bi-info-circle"></i> Optional: Provide details for audit trail</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-circle"></i> Submit Adjustment</button>
                            <a href="{{ route('adjustments.index') }}" class="btn btn-secondary btn-lg"><i class="bi bi-x-circle"></i> Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Guide -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h6 class="card-title text-primary"><i class="bi bi-info-circle-fill"></i> Adjustment Guide</h6>
                    <hr>
                    <div class="mb-3">
                        <h6 class="text-success"><i class="bi bi-plus-circle-fill"></i> Addition (Increase)</h6>
                        <p class="small">Use when you need to add stock:</p>
                        <ul class="small">
                            <li>Found extra inventory during count</li>
                            <li>Correcting undercount errors</li>
                            <li>Customer returns</li>
                            <li>Manual stock entry</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-danger"><i class="bi bi-dash-circle-fill"></i> Subtraction (Decrease)</h6>
                        <p class="small">Use when you need to reduce stock:</p>
                        <ul class="small">
                            <li>Damaged or expired items</li>
                            <li>Theft or loss</li>
                            <li>Correcting overcount errors</li>
                            <li>Sample or promotional usage</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning small"><i class="bi bi-exclamation-triangle-fill"></i> <strong>Important:</strong> Adjustments are permanent and immediately affect inventory levels.</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateStock() {
            const warehouseId = document.getElementById('warehouseSelect').value;
            const productId = document.getElementById('productSelect').value;
            const stockInfo = document.getElementById('stockInfo');

            if (warehouseId && productId) {
                fetch(`{{ route('adjustments.get-stock') }}?warehouse_id=${warehouseId}&product_id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('currentStock').textContent = data.stock;
                            document.getElementById('productName').textContent = data.product_name;
                            document.getElementById('productCode').textContent = data.product_code;
                            stockInfo.classList.remove('d-none');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        stockInfo.classList.add('d-none');
                    });
            } else {
                stockInfo.classList.add('d-none');
            }
        }
    </script>

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


