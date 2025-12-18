<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-cart-plus"></i> Add Sale
            </h2>
            <div class="d-flex gap-2">
                <a href="{{ route('pos.index') }}" class="btn btn-sm btn-success">
                    <i class="bi bi-calculator"></i> POS
                </a>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .sale-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .card-title-custom {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .search-box {
            position: relative;
            margin-bottom: 20px;
        }
        .search-box input, .search-box select {
            padding-left: 45px;
            height: 50px;
            font-size: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
        }
        .search-box input:focus, .search-box select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        .search-box .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 22px;
            color: #667eea;
            pointer-events: none;
        }
        .order-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .order-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .order-table th {
            font-weight: 600;
            padding: 15px 10px;
            font-size: 13px;
        }
        .order-table td {
            padding: 12px 10px;
            vertical-align: middle;
        }
        .order-table input {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 8px;
            font-size: 13px;
        }
        .calculation-sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            position: sticky;
            top: 20px;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
        }
        .calc-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }
        .calc-row.grand-total {
            font-size: 24px;
            font-weight: bold;
            border-top: 2px solid rgba(255,255,255,0.3);
            padding-top: 15px;
            margin-top: 15px;
        }
        .btn-submit {
            background: white;
            color: #667eea;
            border: none;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .btn-submit:hover {
            background: #f8f9ff;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }
        .delete-btn {
            color: #dc3545;
            cursor: pointer;
            font-size: 18px;
            transition: transform 0.2s;
        }
        .delete-btn:hover {
            transform: scale(1.2);
        }
        .payment-method {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        .payment-method.active {
            border-color: #667eea;
            background: linear-gradient(135deg, #f8f9ff 0%, #e8ecff 100%);
        }
        .payment-method input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #667eea;
        }
        .payment-icon {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>

   @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

    <form id="saleForm" enctype="multipart/form-data" method="POST" action="{{ route('sales.store') }}">
        @csrf

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Sale Information -->
                <div class="sale-card">
                    <h5 class="card-title-custom">
                        <i class="bi bi-info-circle"></i> Sale Information
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="sale_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Reference No</label>
                            <input type="text" name="reference_no" class="form-control" value="AUTO" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status</label>
                            <select name="sale_status" class="form-select">
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Warehouse <span class="text-danger">*</span></label>
                            <select name="warehouse_id" class="form-select" required>
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Customer</label>
                            <select name="customer_id" class="form-select">
                                <option value="">Walk-in Customer</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tax Type</label>
                            <select name="tax_type" id="taxType" class="form-select">
                                <option value="exclusive">Exclusive</option>
                                <option value="inclusive">Inclusive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Attach Document</label>
                            <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.png,.jpeg">
                        </div>
                    </div>
                </div>

                <!-- Product Selection -->
                <div class="sale-card">
                    <h5 class="card-title-custom">
                        <i class="bi bi-box-seam"></i> Product Selection
                    </h5>
                    <div class="search-box">
                        <i class="bi bi-upc-scan search-icon"></i>
                        <select id="productSelect" class="form-control">
                            <option value="">Please type product code and select...</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" 
                                    data-name="{{ $product->name }}" 
                                    data-code="PRD-{{ $product->id }}" 
                                    data-price="{{ $product->price ?? $product->selling_price ?? 0 }}"
                                    data-stock="{{ $product->stock }}">
                                PRD-{{ $product->id }} - {{ $product->name }} (Stock: {{ $product->stock }}) - ${{ $product->price ?? $product->selling_price ?? 0 }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Order Table -->
                <div class="order-table">
                    <table class="table mb-0" id="orderTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th style="width: 80px;">Code</th>
                                <th style="width: 70px;">Qty</th>
                                <th style="width: 90px;">Unit Price</th>
                                <th style="width: 70px;">Discount</th>
                                <th style="width: 60px;">Tax</th>
                                <th style="width: 90px;">Subtotal</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="orderTableBody">
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p class="mt-2">No products added yet</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Additional Costs -->
                <div class="sale-card mt-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Order Tax (%)</label>
                            <input type="number" id="orderTax" class="form-control" step="0.01" value="0" onchange="calculateTotal()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Discount ($)</label>
                            <input type="number" id="orderDiscount" class="form-control" step="0.01" value="0" onchange="calculateTotal()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Shipping ($)</label>
                            <input type="number" id="shippingCost" class="form-control" step="0.01" value="0" onchange="calculateTotal()">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Note</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Add any additional notes..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Calculation Summary -->
            <div class="col-lg-4">
                <div class="calculation-sidebar">
                    <h5 class="mb-4">
                        <i class="bi bi-calculator"></i> Order Summary
                    </h5>
                    
                    <div class="calc-row">
                        <span>Items:</span>
                        <strong id="totalItems">0</strong>
                    </div>
                    <div class="calc-row">
                        <span>Total:</span>
                        <strong id="displaySubtotal">$0.00</strong>
                    </div>
                    <div class="calc-row">
                        <span>Order Tax:</span>
                        <strong id="displayOrderTax">$0.00</strong>
                    </div>
                    <div class="calc-row">
                        <span>Discount:</span>
                        <strong id="displayOrderDiscount">$0.00</strong>
                    </div>
                    <div class="calc-row">
                        <span>Shipping:</span>
                        <strong id="displayShipping">$0.00</strong>
                    </div>
                    <div class="calc-row grand-total">
                        <span>Grand Total:</span>
                        <strong id="displayGrandTotal">$0.00</strong>
                    </div>

                    <button type="button" class="btn btn-submit" onclick="showPaymentModal()">
                        <i class="bi bi-check-circle"></i> Submit Sale
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-credit-card"></i> Payment Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7">
                            <h6 class="mb-3 fw-bold">Select Payment Method</h6>
                            
                            <div class="payment-method" onclick="selectPayment('cash')">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="payment_method" value="cash" id="pay_cash">
                                    <label for="pay_cash" class="ms-3 mb-0 flex-grow-1 cursor-pointer">
                                        <i class="bi bi-cash-stack payment-icon text-success"></i>
                                        <strong>Cash</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="payment-method" onclick="selectPayment('cheque')">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="payment_method" value="cheque" id="pay_cheque">
                                    <label for="pay_cheque" class="ms-3 mb-0 flex-grow-1 cursor-pointer">
                                        <i class="bi bi-file-text payment-icon text-primary"></i>
                                        <strong>Cheque</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="payment-method" onclick="selectPayment('bank_transfer')">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="payment_method" value="bank_transfer" id="pay_bank">
                                    <label for="pay_bank" class="ms-3 mb-0 flex-grow-1 cursor-pointer">
                                        <i class="bi bi-bank payment-icon text-info"></i>
                                        <strong>Bank Transfer</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="payment-method" onclick="selectPayment('bkash')">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="payment_method" value="bkash" id="pay_bkash">
                                    <label for="pay_bkash" class="ms-3 mb-0 flex-grow-1 cursor-pointer">
                                        <i class="bi bi-phone payment-icon text-danger"></i>
                                        <strong>bKash</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="payment-method" onclick="selectPayment('nagad')">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="payment_method" value="nagad" id="pay_nagad">
                                    <label for="pay_nagad" class="ms-3 mb-0 flex-grow-1 cursor-pointer">
                                        <i class="bi bi-phone payment-icon text-warning"></i>
                                        <strong>Nagad</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="payment-method" onclick="selectPayment('rocket')">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="payment_method" value="rocket" id="pay_rocket">
                                    <label for="pay_rocket" class="ms-3 mb-0 flex-grow-1 cursor-pointer">
                                        <i class="bi bi-phone payment-icon" style="color: #8b4789;"></i>
                                        <strong>Rocket</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="bg-light p-3 rounded">
                                <h6 class="fw-bold mb-3">Payment Summary</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Grand Total:</span>
                                    <strong id="paymentTotal">$0.00</strong>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Amount Paying</label>
                                    <input type="number" id="amountPaying" class="form-control" step="0.01" onchange="calculateChange()">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Change Return</label>
                                    <input type="text" id="changeReturn" class="form-control bg-white" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Payment Status</label>
                                    <select id="paymentStatus" class="form-select">
                                        <option value="pending">Pending</option>
                                        <option value="partial">Partial</option>
                                        <option value="paid" selected>Paid</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="completePaymentBtn" onclick="completeSale(event)">
                        <i class="bi bi-check-circle"></i> Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

 <script>
let orderItems = [];
let itemCounter = 0;

document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('productSelect');
    if (productSelect) {
        productSelect.addEventListener('change', function() {
            if (!this.value) return;
            
            const option = this.options[this.selectedIndex];
            const productId = this.value;
            
            // Check if already added
            if (orderItems.find(item => item.productId == productId)) {
                alert('Product already added!');
                this.value = '';
                return;
            }
            
            itemCounter++;
            const item = {
                id: itemCounter,
                productId: productId,
                name: option.dataset.name,
                code: option.dataset.code,
                quantity: 1,
                unitPrice: parseFloat(option.dataset.price),
                discount: 0,
                tax: 0
            };
            
            orderItems.push(item);
            renderOrderTable();
            calculateTotal();
            this.value = '';
        });
    }
});

function renderOrderTable() {
    const tbody = document.getElementById('orderTableBody');
    
    if (orderItems.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1"></i>
                    <p class="mt-2">No products added yet</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = orderItems.map(item => `
        <tr>
            <td><strong>${item.name}</strong></td>
            <td><small class="text-muted">${item.code}</small></td>
            <td>
                <input type="number" class="form-control form-control-sm" min="1" value="${item.quantity}" 
                       onchange="updateQuantity(${item.id}, this.value)">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" step="0.01" value="${item.unitPrice}" 
                       onchange="updatePrice(${item.id}, this.value)">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" step="0.01" value="${item.discount}" 
                       onchange="updateDiscount(${item.id}, this.value)">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" step="0.01" value="${item.tax}" 
                       onchange="updateTax(${item.id}, this.value)">
            </td>
            <td><strong>$${calculateItemSubtotal(item).toFixed(2)}</strong></td>
            <td>
                <i class="bi bi-trash delete-btn" onclick="removeItem(${item.id})"></i>
            </td>
        </tr>
    `).join('');
}

function calculateItemSubtotal(item) {
    const base = item.quantity * item.unitPrice;
    return base - item.discount + item.tax;
}

function updateQuantity(id, value) {
    const item = orderItems.find(i => i.id === id);
    if (item) {
        item.quantity = parseInt(value) || 1;
        renderOrderTable();
        calculateTotal();
    }
}

function updatePrice(id, value) {
    const item = orderItems.find(i => i.id === id);
    if (item) {
        item.unitPrice = parseFloat(value) || 0;
        renderOrderTable();
        calculateTotal();
    }
}

function updateDiscount(id, value) {
    const item = orderItems.find(i => i.id === id);
    if (item) {
        item.discount = parseFloat(value) || 0;
        renderOrderTable();
        calculateTotal();
    }
}

function updateTax(id, value) {
    const item = orderItems.find(i => i.id === id);
    if (item) {
        item.tax = parseFloat(value) || 0;
        renderOrderTable();
        calculateTotal();
    }
}

function removeItem(id) {
    if (confirm('Remove this product?')) {
        orderItems = orderItems.filter(i => i.id !== id);
        renderOrderTable();
        calculateTotal();
    }
}

function calculateTotal() {
    const subtotal = orderItems.reduce((sum, item) => sum + calculateItemSubtotal(item), 0);
    
    const orderTaxInput = document.getElementById('orderTax');
    const orderDiscountInput = document.getElementById('orderDiscount');
    const shippingCostInput = document.getElementById('shippingCost');
    
    const orderTax = (subtotal * (parseFloat(orderTaxInput ? orderTaxInput.value : 0) || 0)) / 100;
    const orderDiscount = parseFloat(orderDiscountInput ? orderDiscountInput.value : 0) || 0;
    const shipping = parseFloat(shippingCostInput ? shippingCostInput.value : 0) || 0;
    const grandTotal = subtotal + orderTax - orderDiscount + shipping;
    
    const totalItemsEl = document.getElementById('totalItems');
    const displaySubtotalEl = document.getElementById('displaySubtotal');
    const displayOrderTaxEl = document.getElementById('displayOrderTax');
    const displayOrderDiscountEl = document.getElementById('displayOrderDiscount');
    const displayShippingEl = document.getElementById('displayShipping');
    const displayGrandTotalEl = document.getElementById('displayGrandTotal');
    
    if (totalItemsEl) totalItemsEl.textContent = orderItems.length;
    if (displaySubtotalEl) displaySubtotalEl.textContent = '$' + subtotal.toFixed(2);
    if (displayOrderTaxEl) displayOrderTaxEl.textContent = '$' + orderTax.toFixed(2);
    if (displayOrderDiscountEl) displayOrderDiscountEl.textContent = '$' + orderDiscount.toFixed(2);
    if (displayShippingEl) displayShippingEl.textContent = '$' + shipping.toFixed(2);
    if (displayGrandTotalEl) displayGrandTotalEl.textContent = '$' + grandTotal.toFixed(2);
}

function showPaymentModal() {
    if (orderItems.length === 0) {
        alert('Please add at least one product!');
        return;
    }
    
    const warehouseSelect = document.querySelector('select[name="warehouse_id"]');
    if (warehouseSelect && !warehouseSelect.value) {
        alert('Please select a warehouse!');
        return;
    }
    
    const grandTotalEl = document.getElementById('displayGrandTotal');
    const grandTotal = grandTotalEl ? grandTotalEl.textContent : '$0.00';
    
    const paymentTotalEl = document.getElementById('paymentTotal');
    const amountPayingInput = document.getElementById('amountPaying');
    
    if (paymentTotalEl) paymentTotalEl.textContent = grandTotal;
    if (amountPayingInput) amountPayingInput.value = grandTotal.replace('$', '');
    
    calculateChange();
    
    const modalEl = document.getElementById('paymentModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}

function selectPayment(method) {
    document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }
    const radioBtn = document.getElementById('pay_' + method);
    if (radioBtn) radioBtn.checked = true;
}

function calculateChange() {
    const paymentTotalEl = document.getElementById('paymentTotal');
    const amountPayingInput = document.getElementById('amountPaying');
    const changeReturnInput = document.getElementById('changeReturn');
    
    if (!paymentTotalEl || !amountPayingInput || !changeReturnInput) return;
    
    const total = parseFloat(paymentTotalEl.textContent.replace('$', '').replace(/,/g, ''));
    const paying = parseFloat(amountPayingInput.value) || 0;
    const change = paying - total;
    changeReturnInput.value = change >= 0 ? '$' + change.toFixed(2) : '$0.00';
}

function completeSale(event) {
    if (event) event.preventDefault();
    
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
    if (!paymentMethod) {
        alert('Please select a payment method!');
        return;
    }
    
    const amountPayingInput = document.getElementById('amountPaying');
    const amountPaying = parseFloat(amountPayingInput ? amountPayingInput.value : 0) || 0;
    
    if (amountPaying < 0) {
        alert('Please enter a valid payment amount!');
        return;
    }
    
    const btn = event ? event.target : document.getElementById('completePaymentBtn');
    if (!btn) return;
    
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
    
    const grandTotalEl = document.getElementById('displayGrandTotal');
    const grandTotalText = grandTotalEl ? grandTotalEl.textContent : '$0.00';
    const grandTotal = parseFloat(grandTotalText.replace('$', '').replace(/,/g, ''));
    
    const dueAmount = grandTotal - amountPaying;
    
    const paymentStatusSelect = document.getElementById('paymentStatus');
    let paymentStatus = paymentStatusSelect ? paymentStatusSelect.value : 'pending';
    
    if (amountPaying >= grandTotal) {
        paymentStatus = 'paid';
    } else if (amountPaying > 0 && amountPaying < grandTotal) {
        paymentStatus = 'partial';
    } else {
        paymentStatus = 'pending';
    }
    
    const form = document.getElementById('saleForm');
    if (!form) {
        alert('Form not found!');
        btn.disabled = false;
        btn.innerHTML = originalText;
        return;
    }
    
    const formData = new FormData(form);
    
    orderItems.forEach((item, index) => {
        formData.append(`products[${index}][product_id]`, item.productId);
        formData.append(`products[${index}][quantity]`, item.quantity);
        formData.append(`products[${index}][unit_price]`, item.unitPrice);
        formData.append(`products[${index}][discount]`, item.discount);
        formData.append(`products[${index}][tax]`, item.tax);
    });
    
    const orderTaxInput = document.getElementById('orderTax');
    const orderDiscountInput = document.getElementById('orderDiscount');
    const shippingCostInput = document.getElementById('shippingCost');
    
    formData.append('tax_percentage', orderTaxInput ? (orderTaxInput.value || '0') : '0');
    formData.append('discount_amount', orderDiscountInput ? (orderDiscountInput.value || '0') : '0');
    formData.append('shipping_cost', shippingCostInput ? (shippingCostInput.value || '0') : '0');
    formData.append('grand_total', grandTotal.toFixed(2));
    
    formData.append('payment_method', paymentMethod.value);
    formData.append('payment_status', paymentStatus);
    formData.append('amount_paid', amountPaying.toFixed(2));
    formData.append('due_amount', dueAmount > 0 ? dueAmount.toFixed(2) : '0');
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    const token = csrfToken ? csrfToken.getAttribute('content') : '';
    
    fetch('{{ route("sales.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Server error occurred');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const modalEl = document.getElementById('paymentModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
            
            alert('✅ Sale completed successfully!');
            window.location.href = '{{ route("sales.index") }}';
        } else {
            throw new Error(data.message || 'Failed to complete sale');
        }
    })
    .catch(error => {
        console.error('Sale Error:', error);
        alert('❌ Error: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
    </script>
</x-app-layout>