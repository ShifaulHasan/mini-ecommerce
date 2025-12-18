<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">Edit Product</h2>
            <a href="{{ route('products.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row g-3">
                    <!-- Product Name -->
                    <div class="col-md-6">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $product->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Product Code -->
                    <div class="col-md-6">
                        <label class="form-label">Product Code <span class="text-danger">*</span></label>
                        <input type="text" name="product_code" 
                               class="form-control @error('product_code') is-invalid @enderror" 
                               value="{{ old('product_code', $product->product_code) }}" required readonly>
                        @error('product_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="col-md-4">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Brand -->
                    <div class="col-md-4">
                        <label class="form-label">Brand</label>
                        <select name="brand_id" id="brandSelect" class="form-select" onchange="toggleBrandInput()">
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" 
                                    {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                            @endforeach
                            <option value="new">+ Add New Brand</option>
                        </select>
                        <input type="text" name="brand_new" id="brandInput" class="form-control mt-2" 
                               placeholder="Enter new brand name" style="display: none;">
                    </div>

                    <!-- Unit -->
                    <div class="col-md-4">
                        <label class="form-label">Unit</label>
                        <select name="unit_id" id="unitSelect" class="form-select" onchange="toggleUnitInput()">
                            <option value="">Select Unit</option>
                            @foreach($units as $unit)
                            <option value="{{ $unit->id }}" 
                                    {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                            @endforeach
                            <option value="new">+ Add New Unit</option>
                        </select>
                        <input type="text" name="unit_new" id="unitInput" class="form-control mt-2" 
                               placeholder="Enter new unit name" style="display: none;">
                    </div>

                    <!-- Sale Price -->
                    <div class="col-md-6">
                        <label class="form-label">Sale Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳ </span>
                            <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" 
                                   step="0.01" value="{{ old('price', $product->price) }}" required>
                        </div>
                        @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Cost Price -->
                    <div class="col-md-6">
                        <label class="form-label">Cost (Buy Price) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳ </span>
                            <input type="number" name="cost_price" class="form-control @error('cost_price') is-invalid @enderror" 
                                   step="0.01" value="{{ old('cost_price', $product->cost_price) }}" required>
                        </div>
                        @error('cost_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Stock (Read-only with note) -->
                    <div class="col-md-12">
                        <label class="form-label">Current Stock</label>
                        <input type="number" name="stock" class="form-control" 
                               value="{{ $product->stock }}" readonly>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Use Stock Adjustments to modify stock levels
                        </small>
                    </div>

                    <!-- Description -->
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <!-- Current Image -->
                    @if($product->image)
                    <div class="col-md-12">
                        <label class="form-label">Current Image</label>
                        <div>
                            <img src="{{ asset('images/products/' . $product->image) }}" 
                                 alt="{{ $product->name }}" 
                                 class="img-thumbnail" 
                                 style="max-width: 200px;">
                        </div>
                    </div>
                    @endif

                    <!-- New Image -->
                    <div class="col-md-12">
                        <label class="form-label">Change Image</label>
                        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Update Product
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleBrandInput() {
            const select = document.getElementById('brandSelect');
            const input = document.getElementById('brandInput');
            input.style.display = select.value === 'new' ? 'block' : 'none';
            if (select.value !== 'new') input.value = '';
        }

        function toggleUnitInput() {
            const select = document.getElementById('unitSelect');
            const input = document.getElementById('unitInput');
            input.style.display = select.value === 'new' ? 'block' : 'none';
            if (select.value !== 'new') input.value = '';
        }
    </script>
</x-app-layout>