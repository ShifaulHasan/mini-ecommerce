<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>POS - Point of Sale</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"/>
<style>
/* ====== POS Styles ====== */
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f5f7fa; overflow-x:hidden; }
.pos-container { display:flex; height:100vh; }
.products-panel { flex:1; display:flex; flex-direction:column; background:#fff; overflow:hidden; }
.pos-header { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:#fff; padding:15px 20px; display:flex; justify-content:space-between; align-items:center; }
.search-bar { padding:15px 20px; background:#f8f9fa; border-bottom:2px solid #e9ecef; }
.search-input { position:relative; }
.search-input input { width:100%; padding:12px 45px; border:2px solid #dee2e6; border-radius:10px; font-size:16px; }
.search-input .search-icon { position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#667eea; font-size:20px; }
.categories-bar { padding:10px 20px; background:white; border-bottom:1px solid #e9ecef; overflow-x:auto; white-space:nowrap; }
.category-btn { display:inline-block; padding:8px 20px; margin-right:10px; border:2px solid #e9ecef; border-radius:20px; background:white; color:#495057; cursor:pointer; transition:all .3s; }
.category-btn:hover, .category-btn.active { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; border-color:#667eea; }
.products-grid { flex:1; overflow-y:auto; padding:20px; display:grid; grid-template-columns: repeat(auto-fill,minmax(180px,1fr)); gap:15px; align-content:start; }
.product-card { background:white; border:2px solid #e9ecef; border-radius:12px; padding:15px; cursor:pointer; transition:all .3s; text-align:center; min-height:200px; display:flex; flex-direction:column; }
.product-card:hover { transform:translateY(-5px); box-shadow:0 10px 25px rgba(102,126,234,0.2); border-color:#667eea; }
.product-image { width:100%; height:100px; object-fit:cover; border-radius:8px; margin-bottom:10px; background:#f8f9fa; display:flex; align-items:center; justify-content:center; font-size:48px; color:#dee2e6; }
.product-name { font-weight:600; margin-bottom:5px; font-size:14px; color:#212529; flex:1; }
.product-stock { font-size:12px; color:#6c757d; margin-bottom:8px; }
.product-price { font-size:18px; font-weight:700; color:#667eea; }
.cart-panel { width:450px; background:white; display:flex; flex-direction:column; border-left:2px solid #e9ecef; }
.cart-header { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; padding:15px 20px; }
.customer-section { padding:15px 20px; background:#f8f9fa; border-bottom:2px solid #e9ecef; }
.customer-section select { width:100%; padding:10px; border:2px solid #dee2e6; border-radius:8px; font-size:14px; }
.cart-items { flex:1; overflow-y:auto; padding:15px; }
.cart-item { background:#f8f9fa; border-radius:10px; padding:12px; margin-bottom:10px; display:flex; align-items:center; gap:10px; }
.cart-item-info { flex:1; }
.cart-item-name { font-weight:600; font-size:14px; margin-bottom:5px; }
.cart-item-price { color:#667eea; font-weight:600; }
.cart-item-qty { display:flex; align-items:center; gap:5px; }
.qty-btn { width:30px; height:30px; border:none; background:#667eea; color:white; border-radius:5px; cursor:pointer; font-weight:600; }
.qty-input { width:50px; text-align:center; border:1px solid #dee2e6; border-radius:5px; padding:5px; }
.remove-btn { background:#dc3545; color:white; border:none; width:30px; height:30px; border-radius:5px; cursor:pointer; display:flex; align-items:center; justify-content:center; }
.cart-summary { padding:20px; background:#f8f9fa; border-top:2px solid #e9ecef; }
.summary-row { display:flex; justify-content:space-between; margin-bottom:12px; font-size:15px; }
.summary-row.total { font-size:24px; font-weight:700; color:#667eea; border-top:2px solid #dee2e6; padding-top:12px; margin-top:12px; }
.tax-discount-section { display:flex; gap:10px; margin-bottom:15px; }
.tax-discount-section input { flex:1; padding:8px; border:1px solid #dee2e6; border-radius:5px; }
.checkout-btn { width:100%; padding:15px; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; border:none; border-radius:10px; font-size:18px; font-weight:600; cursor:pointer; transition:all .3s; }
.checkout-btn:disabled { background:#6c757d; cursor:not-allowed; }
.empty-cart { text-align:center; padding:40px 20px; color:#6c757d; }
.payment-method { border:2px solid #e0e0e0; border-radius:10px; padding:15px; margin-bottom:12px; cursor:pointer; transition:all .3s; }
.payment-method:hover { border-color:#667eea; background:#f8f9ff; }
.payment-method.active { border-color:#667eea; background:linear-gradient(135deg,#f8f9ff 0%,#e8ecff 100%); }
.payment-icon { font-size:24px; margin-right:10px; }
.numpad { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-top:15px; }
.numpad-btn { padding:20px; font-size:24px; font-weight:600; border:2px solid #dee2e6; background:white; border-radius:10px; cursor:pointer; transition:all .2s; }
.numpad-btn:hover { background:#667eea; color:white; border-color:#667eea; }
.numpad-btn.clear { background:#dc3545; color:white; border-color:#dc3545; }
</style>
</head>
<body>
<div class="pos-container">
    <!-- Products Panel -->
    <div class="products-panel">
        <div class="pos-header">
            <h4><i class="bi bi-shop"></i> POS</h4>
            <a href="{{ route('sales.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Exit</a>
        </div>
        <div class="search-bar">
            <div class="search-input">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="searchProduct" placeholder="Search products by name or code..." autocomplete="off">
            </div>
        </div>
        <div class="categories-bar">
            <button class="category-btn active" data-category="">All Products</button>
            @foreach($categories as $category)
            <button class="category-btn" data-category="{{ $category->id }}">{{ $category->name }}</button>
            @endforeach
        </div>
        <div class="products-grid" id="productsGrid">
            @foreach($products as $product)
            <div class="product-card" data-id="{{ $product->id }}" data-category="{{ $product->category_id ?? '' }}">
                <div class="product-image">
                    @if($product->image && file_exists(public_path($product->image)))
                        <img src="{{ asset($product->image) }}" style="max-width:100%; max-height:100px; border-radius:8px;" onerror="this.parentElement.innerHTML='<i class=\'bi bi-box-seam\'></i>'">
                    @else
                        <i class="bi bi-box-seam"></i>
                    @endif
                </div>
                <div class="product-name">{{ $product->name }}</div>
                <div class="product-stock">Stock: {{ $product->stock ?? 0 }}</div>
                <div class="product-price">৳{{ number_format($product->price ?? 0,2) }}</div>
                <button class="btn btn-primary btn-sm mt-2" onclick="addToCart({{ $product->id }})">Add</button>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Cart Panel -->
    <div class="cart-panel">
        <div class="cart-header"><h5><i class="bi bi-cart3"></i> Current Sale</h5></div>
        <div class="customer-section">
            <label class="fw-bold mb-2">Customer</label>
            <select id="customerId" class="form-select">
                <option value="">Walk-in Customer</option>
                @foreach($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="cart-items" id="cartItems">
            <div class="empty-cart">
                <i class="bi bi-cart-x" style="font-size:48px;"></i>
                <p class="mt-2">Cart is empty<br>Add products to start</p>
            </div>
        </div>
        <div class="cart-summary">
            <div class="tax-discount-section">
                <input type="number" id="taxPercentage" placeholder="Tax %" value="0" step="any" onchange="renderCart()">
                <input type="number" id="discountAmount" placeholder="Discount  ৳" value="0" step="any" onchange="renderCart()">
            </div>
            <div class="summary-row"><span>Items:</span><strong id="totalItems">0</strong></div>
            <div class="summary-row"><span>Subtotal:</span><strong id="subtotal"> ৳0.00</strong></div>
            <div class="summary-row"><span>Tax:</span><strong id="taxAmount"> ৳0.00</strong></div>
            <div class="summary-row"><span>Discount:</span><strong id="discountDisplay"> ৳0.00</strong></div>
            <div class="summary-row total"><span>Total:</span><strong id="grandTotal"> ৳0.00</strong></div>
            <button class="checkout-btn" id="checkoutBtn" onclick="openPaymentModal()" disabled>Checkout</button>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white;">
                <h5 class="modal-title"><i class="bi bi-credit-card"></i> Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-7">
                        @if(isset($accounts) && $accounts->count() > 0)
                        <h6 class="mb-3 fw-bold">Select Account</h6>
                        <div class="mb-4">
                            <select id="accountSelect" class="form-select form-select-lg">
                                <option value="">-- Select Account --</option>
                                @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ $account->is_default ? 'selected' : '' }}>
                                    {{ $account->name }} - {{ $account->account_no }} 
                                    (Balance:  ৳ {{ number_format($account->current_balance, 2) }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <h6 class="mb-3 fw-bold">Payment Method</h6>
                        
                        <div class="payment-method active" onclick="selectPayment('cash')">
                            <div class="d-flex align-items-center">
                                <input type="radio" name="payment_method" value="cash" id="pay_cash" checked>
                                <label for="pay_cash" class="ms-3 mb-0 flex-grow-1">
                                    <i class="bi bi-cash-stack payment-icon text-success"></i>
                                    <strong>Cash</strong>
                                </label>
                            </div>
                        </div>

                        <div class="payment-method" onclick="selectPayment('card')">
                            <div class="d-flex align-items-center">
                                <input type="radio" name="payment_method" value="card" id="pay_card">
                                <label for="pay_card" class="ms-3 mb-0 flex-grow-1">
                                    <i class="bi bi-credit-card payment-icon text-primary"></i>
                                    <strong>Card</strong>
                                </label>
                            </div>
                        </div>

                        <div class="payment-method" onclick="selectPayment('bkash')">
                            <div class="d-flex align-items-center">
                                <input type="radio" name="payment_method" value="bkash" id="pay_bkash">
                                <label for="pay_bkash" class="ms-3 mb-0 flex-grow-1">
                                    <i class="bi bi-phone payment-icon text-danger"></i>
                                    <strong>bKash</strong>
                                </label>
                            </div>
                        </div>

                        <div class="payment-method" onclick="selectPayment('nagad')">
                            <div class="d-flex align-items-center">
                                <input type="radio" name="payment_method" value="nagad" id="pay_nagad">
                                <label for="pay_nagad" class="ms-3 mb-0 flex-grow-1">
                                    <i class="bi bi-phone payment-icon text-warning"></i>
                                    <strong>Nagad</strong>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-bold mb-3">Payment Details</h6>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Total Amount:</span>
                                <strong id="modalTotal" style="font-size:20px; color:#667eea;">৳0.00</strong>
                            </div>
                            
                            <label class="form-label fw-bold">Amount Paying</label>
                            <input type="number" id="amountPaying" class="form-control form-control-lg mb-3" step="any" onchange="calculateChange()">
                            
                            <label class="form-label fw-bold">Change</label>
                            <input type="text" id="changeAmount" class="form-control form-control-lg mb-3" readonly style="background:white; font-size:24px; font-weight:700; color:#28a745;">

                         
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-lg" onclick="completeSale()">
                    <i class="bi bi-check-circle"></i> Complete Sale
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
let cart = [];
let warehouseId = {{ $mainWarehouse ? $mainWarehouse->id : 'null' }};

/**
 * 🔥 MISSING FUNCTION: Render cart items and totals
 */
function renderCart() {
    const cartItemsDiv = document.getElementById('cartItems');
    const checkoutBtn = document.getElementById('checkoutBtn');
    
    if (!cart || cart.length === 0) {
        cartItemsDiv.innerHTML = `
            <div class="empty-cart">
                <i class="bi bi-cart-x" style="font-size:48px;"></i>
                <p class="mt-2">Cart is empty<br>Add products to start</p>
            </div>
        `;
        checkoutBtn.disabled = true;
        updateSummary(0, 0, 0, 0);
        return;
    }
    
    checkoutBtn.disabled = false;
    
    let html = '';
    cart.forEach(item => {
        const itemTotal = item.quantity * item.unit_price - (item.discount || 0);
        html += `
            <div class="cart-item">
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.product_name}</div>
                    <div class="cart-item-price">৳${item.unit_price.toFixed(2)} each</div>
                </div>
                <div class="cart-item-qty">
                    <button class="qty-btn" onclick="updateQty(${item.product_id}, ${item.quantity - 1})">-</button>
                    <input type="number" class="qty-input" value="${item.quantity}" min="1" 
                           onchange="updateQty(${item.product_id}, this.value)">
                    <button class="qty-btn" onclick="updateQty(${item.product_id}, ${item.quantity + 1})">+</button>
                </div>
                <div class="fw-bold" style="min-width:70px; text-align:right;">৳${itemTotal.toFixed(2)}</div>
                <button class="remove-btn" onclick="removeItem(${item.product_id})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
    });
    
    cartItemsDiv.innerHTML = html;
    calculateTotals();
}

/**
 * Calculate totals with tax and discount
 */
function calculateTotals() {
    let subtotal = 0;
    
    cart.forEach(item => {
        subtotal += (item.quantity * item.unit_price) - (item.discount || 0);
    });
    
    const taxPercent = parseFloat(document.getElementById('taxPercentage').value) || 0;
    const discountAmount = parseFloat(document.getElementById('discountAmount').value) || 0;
    
    const taxAmount = (subtotal * taxPercent) / 100;
    const grandTotal = subtotal + taxAmount - discountAmount;
    
    updateSummary(cart.length, subtotal, taxAmount, discountAmount, grandTotal);
}

/**
 * Update summary display
 */
function updateSummary(items, subtotal, tax, discount, grandTotal = null) {
    document.getElementById('totalItems').textContent = items;
    document.getElementById('subtotal').textContent = '৳' + subtotal.toFixed(2);
    document.getElementById('taxAmount').textContent = '৳' + tax.toFixed(2);
    document.getElementById('discountDisplay').textContent = '৳' + discount.toFixed(2);
    
    if (grandTotal === null) {
        grandTotal = subtotal + tax - discount;
    }
    
    document.getElementById('grandTotal').textContent = '৳' + grandTotal.toFixed(2);
}

/**
 * Fetch current cart from server
 */
function fetchCart() {
    fetch('{{ route("pos.cart") }}')
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            cart = data.cart || [];
            renderCart();
        }
    })
    .catch(err => {
        console.error('Fetch cart error:', err);
        cart = [];
        renderCart();
    });
}

/**
 * Add product to cart
 */
function addToCart(product_id) {
    fetch(`{{ url("/pos/add-to-cart") }}/${product_id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            cart = data.cart || [];
            renderCart();
        } else {
            alert(data.message || 'Could not add product');
        }
    })
    .catch(err => {
        console.error('Add to cart error:', err);
        alert('Error adding product to cart');
    });
}

/**
 * Update quantity of a cart item
 */
function updateQty(product_id, quantity) {
    quantity = parseInt(quantity);
    if (quantity < 1) return removeItem(product_id);

    fetch('{{ route("pos.update.qty") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ product_id, quantity })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            cart = data.cart || [];
            renderCart();
        } else {
            alert(data.message || 'Could not update quantity');
        }
    })
    .catch(err => console.error('Update qty error:', err));
}

/**
 * Remove item from cart
 */
function removeItem(product_id) {
    if (!confirm('Remove this item from cart?')) return;
    
    fetch(`{{ url("/pos/remove") }}/${product_id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            cart = data.cart || [];
            renderCart();
        }
    })
    .catch(err => console.error('Remove item error:', err));
}

/**
 * Open payment modal
 */
function openPaymentModal() {
    if (!cart || cart.length === 0) {
        alert('Cart is empty!');
        return;
    }

    const grandTotal = document.getElementById('grandTotal').textContent;
    document.getElementById('modalTotal').textContent = grandTotal;
    document.getElementById('amountPaying').value = grandTotal.replace('৳', '');
    calculateChange();
    
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
}

/**
 * Select payment method
 */
function selectPayment(method) {
    document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
    event.currentTarget.classList.add('active');
    document.getElementById('pay_' + method).checked = true;
}

/**
 * Number pad functions
 */
function addDigit(digit) {
    const input = document.getElementById('amountPaying');
    input.value = input.value + digit;
    calculateChange();
}

function clearAmount() {
    document.getElementById('amountPaying').value = '';
    calculateChange();
}

function calculateChange() {
    const total = parseFloat(document.getElementById('modalTotal').textContent.replace('৳', ''));
    const paying = parseFloat(document.getElementById('amountPaying').value) || 0;
    const change = paying - total;
    document.getElementById('changeAmount').value = change >= 0 ? '৳' + change.toFixed(2) : '৳0.00';
}

/**
 * Complete sale / checkout
 */
function completeSale() {
    if (!cart || cart.length === 0) {
        alert('Cart is empty!');
        return;
    }

    const customerIdSelect = document.getElementById('customerId');
    const customerId = customerIdSelect.value ? parseInt(customerIdSelect.value) : null;

    const accountSelect = document.getElementById('accountSelect');
    const accountId = accountSelect ? (accountSelect.value ? parseInt(accountSelect.value) : null) : null;

    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
    if (!paymentMethod) {
        alert('Please select a payment method.');
        return;
    }

    const amountPaying = parseFloat(document.getElementById('amountPaying').value) || 0;
    const grandTotal = parseFloat(document.getElementById('modalTotal').textContent.replace('৳', ''));

    if (amountPaying <= 0) {
        alert('Please enter amount paying.');
        return;
    }

    if (amountPaying < grandTotal) {
        if (!confirm('Amount paid is less than total. Create partial payment?')) {
            return;
        }
    }

    const tax = parseFloat(document.getElementById('taxPercentage').value) || 0;
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;

    const payload = {
        warehouse_id: warehouseId,
        customer_id: customerId,
        account_id: accountId,
        products: cart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            unit_price: item.unit_price,
            discount: item.discount || 0
        })),
        payment_method: paymentMethod.value,
        amount_paid: amountPaying,
        tax_percentage: tax,
        discount_amount: discount
    };

    const btn = event.target;
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';

    fetch('{{ route("pos.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
        
        if (data.success) {
            const changeAmount = data.change || (amountPaying - grandTotal);
            alert(`✅ Sale completed!\n\nReference: ${data.reference_no || 'N/A'}\nChange: ৳${changeAmount.toFixed(2)}`);
            
            cart = [];
            renderCart();
            document.getElementById('taxPercentage').value = 0;
            document.getElementById('discountAmount').value = 0;
            customerIdSelect.value = '';
            
            const modalElement = document.getElementById('paymentModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        } else if (data.errors) {
            let msg = 'Validation errors:\n';
            for (const key in data.errors) {
                msg += `${data.errors[key].join(', ')}\n`;
            }
            alert(msg);
        } else {
            alert('❌ Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
        console.error('Complete sale error:', err);
        alert('❌ Server error occurred');
    });
}

/**
 * Search products
 */
document.getElementById('searchProduct').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        const productName = card.querySelector('.product-name').textContent.toLowerCase();
        if (productName.includes(searchTerm)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
});

/**
 * Category filter
 */
document.querySelectorAll('.category-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const categoryId = this.dataset.category;
        const productCards = document.querySelectorAll('.product-card');
        
        productCards.forEach(card => {
            if (!categoryId || card.dataset.category === categoryId) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// Initial fetch on page load
document.addEventListener('DOMContentLoaded', function() {
    fetchCart();
});
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
</body>
</html>