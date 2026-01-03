<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Customer List') }}
        </h2>
    </x-slot>

    <style>
        .container-fluid { padding: 20px; background: #f5f7fa; min-height: 100vh; }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-actions { display: flex; gap: 12px; }
        .btn-add { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: transform 0.2s; }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
        .btn-import { background: white; color: #667eea; border: 2px solid #667eea; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; }
        .btn-import:hover { background: #667eea; color: white; }
        
        .pos-section {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
        .pos-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            flex: 1;
            min-width: 200px;
        }
        .pos-label {
            font-size: 12px;
            font-weight: 600;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .pos-input, .pos-select {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            width: 100%;
            font-size: 14px;
        }
        .pos-input::placeholder { color: rgba(255, 255, 255, 0.6); }
        .pos-input:focus, .pos-select:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.25);
            border-color: #fff;
        }
        .pos-select option {
            background: #1f2937; 
            color: white;
        }
        .btn-pos-pay {
            background: #f59e0b;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-pos-pay:hover {
            background: #d97706;
            transform: translateY(-2px);
        }
        .btn-pos-pay:disabled {
            background: #6b7280;
            cursor: not-allowed;
            transform: none;
        }

        .table-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: white; padding: 15px 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .data-table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border-radius: 10px; overflow: hidden; }
        .data-table thead { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .data-table th { padding: 15px 12px; text-align: left; font-weight: 600; color: white; font-size: 13px; text-transform: uppercase; }
        .data-table td { padding: 14px 12px; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #4b5563; vertical-align: middle; }
        .data-table tbody tr:hover { background: #f8f9ff; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .amount-negative { color: #dc2626; font-weight: 700; }
        .dropdown-menu { display: none; position: absolute; right: 0; top: 100%; background: white; border: 1px solid #e0e0e0; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); min-width: 160px; z-index: 1000; }
        .dropdown-menu.show { display: block; }
        .dropdown-item { display: block; padding: 10px 15px; color: #4b5563; text-decoration: none; font-size: 13px; }
        .dropdown-item:hover { background: #f3f4f6; }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="container-fluid">
                
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <strong>Success!</strong> {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <strong>Error!</strong> {{ session('error') }}
                    </div>
                @endif

                <!-- 🔥 QUICK PAY POS SECTION -->
                <div class="pos-section">
                    <div style="width: 100%; margin-bottom: 10px;">
                        <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-cash-coin"></i> Quick Pay Due (POS)
                        </h3>
                        <p style="margin: 0; font-size: 12px; opacity: 0.8;">Record a payment for a customer's outstanding dues instantly.</p>
                    </div>

                    <form id="posPayForm" method="POST" style="display: flex; gap: 15px; flex-wrap: wrap; flex: 1;" onsubmit="return handlePosPayment(event)">
                        @csrf
                        
                        <div class="pos-group" style="flex: 2;">
                            <label class="pos-label">Select Customer</label>
                            <select name="customer_id" id="posCustomerSelect" class="pos-select" required onchange="updatePosForm()">
                                <option value="">-- Choose Customer --</option>
                                @foreach($customers as $c)
                                    @if($c->total_due > 0)
                                    <option value="{{ $c->id }}" 
                                            data-due="{{ $c->total_due }}" 
                                            data-name="{{ $c->name }}"
                                            data-code="{{ $c->customer_code }}">
                                        {{ $c->name }} ({{ $c->customer_code }}) - Due: ৳{{ number_format($c->total_due, 2) }}
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="pos-group">
                            <label class="pos-label">Payment Amount</label>
                            <input type="number" step="0.01" name="amount" id="posAmount" class="pos-input" placeholder="0.00" required min="0.01">
                        </div>

                        <div class="pos-group" style="max-width: 150px;">
                            <label class="pos-label">Date</label>
                            <input type="date" name="payment_date" class="pos-input" value="{{ date('Y-m-d') }}" required max="{{ date('Y-m-d') }}">
                        </div>

                        <div class="pos-group" style="max-width: 150px;">
                            <label class="pos-label">Method</label>
                            <select name="payment_method" class="pos-select" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="check">Check</option>
                                <option value="card">Card</option>
                                <option value="mobile_payment">Mobile Payment</option>
                            </select>
                        </div>

                        <div class="pos-group" style="flex: 1;">
                            <label class="pos-label">Account (Deposit To)</label>
                            <select name="account_id" class="pos-select" required>
                                <option value="">-- Select Account --</option>
                                @if(isset($accounts))
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">
                                            {{ $acc->name }} (Balance: ৳{{ number_format($acc->current_balance, 2) }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Hidden Notes Field -->
                        <input type="hidden" name="notes" value="Quick payment via POS">

                        <button type="submit" id="posPayBtn" class="btn-pos-pay" disabled>
                            <i class="bi bi-check-circle-fill"></i> PAY NOW
                        </button>
                    </form>
                </div>

                <!-- Header Section -->
                <div class="header-section">
                    <h3 style="margin: 0; color: #1f2937; font-size: 22px; font-weight: 700;">
                        Customers
                    </h3>
                    <div class="btn-actions">
                        <a href="{{ route('customers.create') }}" class="btn-add">
                            <i class="bi bi-plus-circle"></i> Add Customer
                        </a>
                        <!-- <button class="btn-import" onclick="alert('Import feature coming soon!')">
                            <i class="bi bi-upload"></i> Import Customer
                        </button> -->
                    </div>
                </div>

                <!-- Table Controls -->
                <div class="table-controls">
                    <div class="flex items-center gap-2">
                        <span style="color: #6b7280;">Show</span>
                        <select class="border rounded p-1" style="width: 70px;" onchange="window.location.href='?per_page='+this.value">
                            <option {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        </select>
                        <span style="color: #6b7280;">entries</span>
                    </div>
                    <div>
                        <input type="text" id="searchInput" placeholder="Search customers..." 
                               class="border rounded p-2 w-64 focus:outline-none focus:border-purple-500"
                               value="{{ request('search') }}"
                               onkeyup="if(event.key === 'Enter') window.location.href='?search='+this.value">
                    </div>
                </div>

                <!-- Data Table -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" id="selectAll"></th>
                            <th>Customer Details</th>
                            <th>Phone / Email</th>
                            <th>Discount Plan</th>
                            <th>Reward Points</th>
                            <th>Deposited Balance</th>
                            <th>Total Due</th>
                            <th>Status</th>
                            <th width="100" style="text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td><input type="checkbox" class="row-checkbox"></td>
                            <td>
                                <div class="font-bold text-gray-800">{{ $customer->name }}</div>
                                <div class="text-xs text-gray-500">{{ $customer->customer_code }}</div>
                                @if($customer->company_name)
                                    <div class="text-xs text-gray-600 mt-1">{{ $customer->company_name }}</div>
                                @endif
                                @if($customer->is_supplier)
                                    <span class="badge bg-blue-100 text-blue-800 mt-1">Supplier</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-sm">{{ $customer->phone }}</div>
                                <div class="text-xs text-gray-500">{{ $customer->email }}</div>
                            </td>
                            <td>
                                <span class="text-green-600 font-bold">{{ number_format($customer->discount_percentage, 2) }}%</span>
                            </td>
                            <td>
                                <span class="text-amber-600 font-bold">{{ number_format($customer->reward_points) }}</span>
                            </td>
                            <td>
                                <span class="text-green-600">৳{{ number_format($customer->deposited_balance, 2) }}</span>
                            </td>
                            <td>
                                @if($customer->total_due > 0)
                                    <span class="amount-negative">৳{{ number_format($customer->total_due, 2) }}</span>
                                @else
                                    <span class="text-gray-400">৳0.00</span>
                                @endif
                            </td>
                            <td>
                                @if($customer->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div class="relative inline-block">
                                    <button class="bg-gray-100 border p-2 rounded hover:bg-gray-200 text-xs font-bold" onclick="toggleDropdown('dropdown-{{ $customer->id }}')">
                                        Action <i class="bi bi-chevron-down"></i>
                                    </button>
                                    <div id="dropdown-{{ $customer->id }}" class="dropdown-menu">
                                        <a href="{{ route('customers.show', $customer->id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View</a>
                                        <a href="{{ route('customers.edit', $customer->id) }}" class="dropdown-item"><i class="bi bi-pencil"></i> Edit</a>
                                        
                                        @if($customer->total_due > 0)
                                            <a href="{{ route('customers.payment', $customer->id) }}" class="dropdown-item text-blue-600 font-bold">
                                                <i class="bi bi-credit-card"></i> Pay Due
                                            </a>
                                        @endif

                                        <a href="#" class="dropdown-item text-red-600" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this customer?')) document.getElementById('delete-{{ $customer->id }}').submit();">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                        
                                        <form id="delete-{{ $customer->id }}" action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center p-10 text-gray-400">
                                No customers found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-6 flex justify-center">
                    {{ $customers->appends(['search' => request('search'), 'per_page' => request('per_page')])->links() }}
                </div>

            </div>
        </div>
    </div>

    <script>
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        }

        function toggleDropdown(id) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if(menu.id !== id) menu.classList.remove('show');
            });
            document.getElementById(id).classList.toggle('show');
        }

        window.addEventListener('click', function(e) {
            if (!e.target.closest('.relative')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });

        // 🔥 POS Form Logic - Fixed
        function updatePosForm() {
            const select = document.getElementById('posCustomerSelect');
            const amountInput = document.getElementById('posAmount');
            const payBtn = document.getElementById('posPayBtn');
            
            if (select.value) {
                const selectedOption = select.options[select.selectedIndex];
                const maxDue = parseFloat(selectedOption.getAttribute('data-due'));
                
                // Set max amount to customer's due
                amountInput.setAttribute('max', maxDue);
                
                // Enable pay button
                payBtn.disabled = false;
                
                // Optional: Auto-fill with full due amount
                // amountInput.value = maxDue.toFixed(2);
            } else {
                payBtn.disabled = true;
                amountInput.value = '';
                amountInput.removeAttribute('max');
            }
        }

        // 🔥 Handle POS Payment Submission
        function handlePosPayment(event) {
            event.preventDefault();
            
            const form = document.getElementById('posPayForm');
            const customerId = document.getElementById('posCustomerSelect').value;
            const amount = parseFloat(document.getElementById('posAmount').value);
            const selectedOption = document.getElementById('posCustomerSelect').options[document.getElementById('posCustomerSelect').selectedIndex];
            const maxDue = parseFloat(selectedOption.getAttribute('data-due'));
            
            if (!customerId) {
                alert('Please select a customer');
                return false;
            }
            
            if (amount <= 0) {
                alert('Payment amount must be greater than 0');
                return false;
            }
            
            if (amount > maxDue) {
                alert(`Payment amount (৳${amount.toFixed(2)}) cannot exceed total due (৳${maxDue.toFixed(2)})`);
                return false;
            }
            
            // Set the correct action URL
            form.action = "{{ url('customers') }}/" + customerId + "/payment";
            
            // Confirm before submitting
            const customerName = selectedOption.getAttribute('data-name');
            if (confirm(`Confirm payment of ৳${amount.toFixed(2)} for ${customerName}?`)) {
                form.submit();
                return true;
            }
            
            return false;
        }

        // Auto-hide success/error messages after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
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