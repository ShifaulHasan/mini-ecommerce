<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS - Point of Sale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
        }

        .pos-container {
            height: 100vh;
            display: flex;
        }

        .products-section {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .cart-section {
            width: 400px;
            background: white;
            border-left: 2px solid #e5e7eb;
            display: flex;
            flex-direction: column;
        }

        .search-bar {
            padding: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .search-input {
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            padding: 0.75rem 1rem;
            padding-left: 2.5rem;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .category-tabs {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
        }

        .category-btn {
            padding: 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .category-btn:hover, .category-btn.active {
            background: #6f42c1;
            color: white;
            border-color: #6f42c1;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            padding: 1rem;
        }

        .product-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.15);
            border-color: #6f42c1;
        }

        .product-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            background: #f3f4f6;
        }

        .product-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .product-price {
            font-size: 1.125rem;
            font-weight: 700;
            color: #6f42c1;
        }

        .product-stock {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .cart-header {
            padding: 1rem;
            border-bottom: 2px solid #e5e7eb;
            background: #6f42c1;
            color: white;
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .cart-item {
            background: #f9fafb;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            display: flex;
            gap: 0.75rem;
        }

        .cart-item-image {
            width: 50px;
            height: 50px;
            border-radius: 6px;
            object-fit: cover;
            background: #e5e7eb;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1f2937;
        }

        .cart-item-price {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .qty-btn {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .qty-btn:hover {
            background: #f3f4f6;
        }

        .cart-summary {
            padding: 1rem;
            border-top: 2px solid #e5e7eb;
            background: #f9fafb;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .summary-row.total {
            font-size: 1.25rem;
            font-weight: 700;
            color: #6f42c1;
            padding-top: 0.75rem;
            border-top: 2px solid #e5e7eb;
            margin-top: 0.5rem;
        }

        .checkout-section {
            padding: 1rem;
            border-top: 2px solid #e5e7eb;
        }

        .btn-checkout {
            width: 100%;
            padding: 1rem;
            background: #6f42c1;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-checkout:hover {
            background: #5a32a3;
            transform: translateY(-1px);
        }

        .btn-clear {
            width: 100%;
            padding: 0.75rem;
            background: white;
            color: #ef4444;
            border: 2px solid #ef4444;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 0.5rem;
            cursor: pointer;
        }

        .empty-cart {
            text-align: center;
            padding: 3rem 1rem;
            color: #9ca3af;
        }

        .badge-stock {
            background: #d1fae5;
            color: #065f46;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
        }

        .cart-header a:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="pos-container">

        <!-- Products Section -->
        <div class="products-section">

            <!-- Search Bar -->
            <div class="search-bar">
                <div class="position-relative">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" id="searchInput" class="form-control search-input" 
                           placeholder="Search products by name or code...">
                </div>
            </div>

            <!-- Category Tabs -->
            <div class="category-tabs">
                <button class="category-btn active" onclick="filterCategory('')">All</button>
                @foreach($categories as $category)
                <button class="category-btn" onclick="filterCategory({{ $category->id }})">
                    {{ $category->name }}
                </button>
                @endforeach
            </div>

            <!-- Product Grid -->
            <div class="product-grid" id="productGrid">
                @foreach($products as $product)
                <div class="product-card" data-category="{{ $product->category_id }}"
                     onclick="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }}, {{ $product->stock }}, '{{ $product->image }}')">
                    @if($product->image)
                    <img src="{{ asset('images/products/'.$product->image) }}" class="product-image" alt="{{ $product->name }}">
                    @else
                    <div class="product-image d-flex align-items-center justify-content-center">
                        <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                    </div>
                    @endif
                    <div class="product-name">{{ $product->name }}</div>
                    <div class="product-price">${{ number_format($product->price, 2) }}</div>
                    <div class="product-stock">
                        <span class="badge-stock">Stock: {{ $product->stock }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Cart Section -->
        <div class="cart-section">

            <!-- Cart Header with Back Button Added -->
            <div class="cart-header d-flex align-items-center">
                <a href="{{ route('sales.index') }}" class="text-white me-3" 
                   style="font-size: 1.4rem; text-decoration: none;">
                    <i class="bi bi-arrow-left-circle"></i>
                </a>
                <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>Cart</h5>
            </div>

            <!-- Cart Items -->
            <div class="cart-items" id="cartItems">
                <div class="empty-cart">
                    <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                    <p class="mt-2">Cart is empty</p>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <strong id="subtotal">$0.00</strong>
                </div>
                <div class="summary-row">
                    <span>Tax (0%):</span>
                    <strong id="tax">$0.00</strong>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <strong id="total">$0.00</strong>
                </div>
            </div>

            <!-- Checkout Section -->
            <div class="checkout-section">
                <select id="warehouseSelect" class="form-select mb-2">
                    <option value="">Select Warehouse *</option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>

                <select id="customerSelect" class="form-select mb-2">
                    <option value="">Walk-in Customer</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>

                <select id="paymentMethod" class="form-select mb-2">
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="mobile_payment">Mobile Payment</option>
                </select>

                <input type="number" id="paidAmount" class="form-control mb-2" 
                       placeholder="Paid Amount" step="0.01" min="0">

                <button class="btn-checkout" onclick="checkout()">
                    <i class="bi bi-check-circle me-2"></i>Complete Sale
                </button>
                <button class="btn-clear" onclick="clearCart()">
                    <i class="bi bi-trash me-2"></i>Clear Cart
                </button>
            </div>

        </div>
    </div>

    <!-- JS Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let cart = [];

        function addToCart(id, name, price, stock, image) {
            const existingItem = cart.find(item => item.product_id === id);
            
            if (existingItem) {
                if (existingItem.quantity < stock) {
                    existingItem.quantity++;
                } else {
                    alert('Insufficient stock!');
                    return;
                }
            } else {
                cart.push({
                    product_id: id,
                    name,
                    price,
                    quantity: 1,
                    stock,
                    image
                });
            }
            updateCart();
        }

        function updateCart() {
            const cartItems = document.getElementById('cartItems');

            if (cart.length === 0) {
                cartItems.innerHTML = `
                    <div class="empty-cart">
                        <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                        <p class="mt-2">Cart is empty</p>
                    </div>
                `;
                updateSummary();
                return;
            }

            let html = '';
            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                const imageSrc = item.image ? `/images/products/${item.image}` : '';

                html += `
                    <div class="cart-item">
                        ${item.image 
                        ? `<img src="${imageSrc}" class="cart-item-image">`
                        : `<div class="cart-item-image d-flex align-items-center justify-content-center">
                                <i class="bi bi-image"></i>
                           </div>`}

                        <div class="cart-item-details">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">$${item.price.toFixed(2)} each</div>

                            <div class="quantity-controls">
                                <button class="qty-btn" onclick="decreaseQty(${index})">-</button>
                                <span style="min-width: 30px; text-align: center;">${item.quantity}</span>
                                <button class="qty-btn" onclick="increaseQty(${index})">+</button>

                                <span class="ms-auto fw-bold">$${itemTotal.toFixed(2)}</span>

                                <button class="qty-btn text-danger" onclick="removeItem(${index})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            cartItems.innerHTML = html;
            updateSummary();
        }

        function increaseQty(index) {
            if (cart[index].quantity < cart[index].stock) {
                cart[index].quantity++;
                updateCart();
            } else {
                alert('Insufficient stock!');
            }
        }

        function decreaseQty(index) {
            if (cart[index].quantity > 1) {
                cart[index].quantity--;
                updateCart();
            }
        }

        function removeItem(index) {
            cart.splice(index, 1);
            updateCart();
        }

        function clearCart() {
            if (confirm('Clear all items from cart?')) {
                cart = [];
                updateCart();
            }
        }

        function updateSummary() {
            let subtotal = 0;
            cart.forEach(item => subtotal += item.price * item.quantity);

            const tax = 0;
            const total = subtotal + tax;

            document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
            document.getElementById('total').textContent = `$${total.toFixed(2)}`;
        }

        async function checkout() {
            if (cart.length === 0) {
                alert('Cart is empty!');
                return;
            }

            const warehouseId = document.getElementById('warehouseSelect').value;
            if (!warehouseId) {
                alert('Please select a warehouse!');
                return;
            }

            const paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;
            const total = parseFloat(document.getElementById('total').textContent.replace('$', ''));

            if (paidAmount < total) {
                if (!confirm(`Paid amount ($${paidAmount}) is less than total ($${total}). Continue as partial payment?`)) {
                    return;
                }
            }

            const data = {
                warehouse_id: warehouseId,
                customer_id: document.getElementById('customerSelect').value || null,
                payment_method: document.getElementById('paymentMethod').value,
                paid_amount: paidAmount,
                cart: cart,
                total: total,
                _token: '{{ csrf_token() }}'
            };

            try {
                const response = await fetch('{{ route("pos.completeSale") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert(`Sale completed successfully!\nReference: ${result.reference}`);
                    cart = [];
                    updateCart();
                    document.getElementById('paidAmount').value = '';
                    document.getElementById('warehouseSelect').value = '';
                    document.getElementById('customerSelect').value = '';
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error completing sale!');
                console.error(error);
            }
        }

        document.getElementById('searchInput').addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            const products = document.querySelectorAll('.product-card');
            
            products.forEach(product => {
                const name = product.querySelector('.product-name').textContent.toLowerCase();
                product.style.display = name.includes(search) ? 'block' : 'none';
            });
        });

        function filterCategory(categoryId) {
            const products = document.querySelectorAll('.product-card');
            const buttons = document.querySelectorAll('.category-btn');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            products.forEach(product => {
                if (!categoryId || product.dataset.category == categoryId) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
