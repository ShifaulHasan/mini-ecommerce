<x-app-layout>
     <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Suppliers') }}
        </h2>
    </x-slot>

    <style>
        .container-fluid {
            padding: 20px;
        }
        .header-section {
            margin-bottom: 20px;
        }
        .btn-add {
            background: #8b5cf6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-add:hover {
            background: #7c3aed;
            color: white;
        }
        .btn-import {
            background: #10b981;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }
        .btn-import:hover {
            background: #059669;
        }
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .records-per-page {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .records-per-page select {
            padding: 5px 30px 5px 10px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: white;
            cursor: pointer;
        }
        .search-box input {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            width: 250px;
        }
        
        .table-wrapper {
            overflow-x: auto;
            position: relative;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .data-table thead {
            background: #f9fafb;
        }
        .data-table th {
            padding: 12px;
            text-align: left;
            font-weight: 500;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            font-size: 14px;
        }
        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            color: #4b5563;
        }
        .data-table tbody tr:hover {
            background: #f9fafb;
        }
        
        .action-cell {
            position: relative;
        }
        
        .action-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            color: #374151;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }
        
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 4px);
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            min-width: 200px;
            z-index: 1000;
            padding: 4px 0;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            color: #374151;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.15s;
            white-space: nowrap;
            cursor: pointer;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
        }
        
        .dropdown-item:hover {
            background: #f3f4f6;
        }
        
        .dropdown-item.delete {
            color: #ef4444;
        }
        
        .dropdown-item.delete:hover {
            background: #fee2e2;
        }
        
        .dropdown-item i {
            width: 16px;
            font-size: 14px;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge.active {
            background: #10b981;
            color: white;
        }
        .badge.inactive {
            background: #ef4444;
            color: white;
        }
        .badge-due {
            background: #fef3c7;
            color: #92400e;
            padding: 6px 12px;
            font-weight: 600;
        }
        .badge-due.has-due {
            background: #fee2e2;
            color: #991b1b;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin-top: 20px;
        }
        .page-btn {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 4px;
            cursor: pointer;
        }
        .page-btn.active {
            background: #8b5cf6;
            color: white;
            border-color: #8b5cf6;
        }
        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .showing-info {
            text-align: center;
            margin-top: 10px;
            color: #6b7280;
            font-size: 13px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal.show {
            display: block;
        }
        
        .modal-dialog {
            position: relative;
            margin: 50px auto;
            max-width: 500px;
        }
        
        .modal-content {
            background-color: #fefefe;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            padding: 16px 20px;
            background: #10b981;
            color: white;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .btn-close {
            background: transparent;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .text-danger {
            color: #ef4444;
        }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="container-fluid">
                <!-- Success Message -->
                @if(session('success'))
                    <div style="background: #10b981; color: white; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div style="background: #ef4444; color: white; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Suppliers Section Header -->
                <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 15px;">Suppliers</h3>

                <!-- Header Section -->
                <div class="header-section">
                    <a href="{{ route('suppliers.create') }}" class="btn-add">
                        <i class="fas fa-plus"></i> Add Supplier
                    </a>
                    <!-- <a href="#" class="btn-import">
                        <i class="fas fa-file-import"></i> Import Supplier
                    </a> -->
                </div>

                <!-- Table Controls -->
                <div class="table-controls">
                    <div class="records-per-page">
                        <span>Show</span>
                        <select id="recordsPerPage">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>entries</span>
                    </div>
                    <div class="search-box">
                        <input type="text" placeholder="Search suppliers..." id="searchInput">
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                                <th>Supplier Details</th>
                                <th>Contact Info</th>
                                <th>Location</th>
                                <th>Total Due</th>
                                <th>Status</th>
                                <th style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($suppliers as $supplier)
                            <tr class="data-row">
                                <td><input type="checkbox" class="row-checkbox"></td>
                                <td>
                                    <div style="font-weight: 500;">{{ $supplier->name }}</div>
                                    @if($supplier->company)
                                        <div style="font-size: 12px; color: #6b7280;">{{ $supplier->company }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->phone)
                                        <div><i class="fas fa-phone" style="width: 16px;"></i> {{ $supplier->phone }}</div>
                                    @endif
                                    @if($supplier->email)
                                        <div style="font-size: 12px; color: #6b7280;">
                                            <i class="fas fa-envelope" style="width: 16px;"></i> {{ $supplier->email }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->city || $supplier->country)
                                        <div>
                                            <i class="fas fa-map-marker-alt" style="width: 16px;"></i>
                                            {{ $supplier->city ?? '' }}{{ $supplier->city && $supplier->country ? ', ' : '' }}{{ $supplier->country ?? '' }}
                                        </div>
                                    @else
                                        <span style="color: #9ca3af;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-due {{ $supplier->total_due > 0 ? 'has-due' : '' }}">
                                        ৳{{ number_format($supplier->total_due ?? 0, 2) }}
                                    </span>
                                </td>
                                <td>
                                    @if($supplier->status == 'active')
                                        <span class="badge active">ACTIVE</span>
                                    @else
                                        <span class="badge inactive">INACTIVE</span>
                                    @endif
                                </td>
                                <td class="action-cell">
    <div class="action-dropdown">
        <button type="button" class="action-btn" onclick="toggleDropdown(event, this)">
            Action <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
        </button>
        <div class="dropdown-menu">
            <a href="{{ route('suppliers.edit', $supplier->id) }}" class="dropdown-item">
                <i class="fas fa-edit"></i> <span>Edit</span>
            </a>
            <a href="{{ route('supplier.due.report', $supplier->id) }}" class="dropdown-item">
                <i class="fas fa-file-invoice"></i> <span>Supplier Due Report</span>
            </a>
            <a href="#" onclick="openPaymentModal({{ $supplier->id }}, '{{ $supplier->name }}', {{ $supplier->total_due ?? 0 }}); return false;" class="dropdown-item">
                <i class="fas fa-money-bill"></i> <span>Add Payment / Clear Due</span>
            </a>
            <hr style="margin: 4px 0; border: none; border-top: 1px solid #e5e7eb;">
            <button onclick="deleteSupplier({{ $supplier->id }})" class="dropdown-item delete">
                <i class="fas fa-trash"></i> <span>Delete</span>
            </button>
        </div>
    </div>
    
    <form id="delete-form-{{ $supplier->id }}" 
          action="{{ route('suppliers.destroy', $supplier->id) }}" 
          method="POST" 
          style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px;">No suppliers found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination" id="pagination"></div>

                <div class="showing-info" id="showingInfo">
                    Showing <span id="startRecord">0</span> - <span id="endRecord">0</span> of {{ $suppliers->count() }} entries
                </div>
            </div>
        </div>
    </div>

    <!-- Add Payment Modal -->
    <div class="modal" id="paymentModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Clear Due</h5>
                    <button type="button" class="btn-close" onclick="closePaymentModal()">&times;</button>
                </div>
                <form id="paymentForm" action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <strong id="modalSupplierName"></strong><br>
                                Total Due: <strong id="modalTotalDue"></strong>
                            </div>
                        </div>

                        <p style="margin-bottom: 12px; font-size: 13px; color: #6b7280;">
                            <span class="text-danger">*</span> Required
                        </p>

                        <div class="form-group">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Account <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-control" required id="accountSelect">
                                <option value="">Select Account</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-control">
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="bkash">bKash</option>
                                <option value="nagad">Nagad</option>
                                <option value="rocket">Rocket</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Reference No</label>
                            <input type="text" name="reference_no" class="form-control" placeholder="Enter reference number">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Note</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Enter payment notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closePaymentModal()">Cancel</button>
                        <button type="submit" class="btn-success">Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

   <script>
    // Pagination and filtering variables
    let currentPage = 1;
    let recordsPerPage = 10;
    let allRows = [];
    let filteredRows = [];

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        allRows = Array.from(document.querySelectorAll('.data-row'));
        filteredRows = [...allRows];
        updateTable();
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.action-dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    });

    // Toggle dropdown menu
    function toggleDropdown(event, button) {
        event.stopPropagation();
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu !== button.nextElementSibling) {
                menu.classList.remove('show');
            }
        });
        
        // Toggle current dropdown
        const menu = button.nextElementSibling;
        menu.classList.toggle('show');
    }

    // Records per page change
    document.getElementById('recordsPerPage').addEventListener('change', function() {
        recordsPerPage = parseInt(this.value);
        currentPage = 1;
        updateTable();
    });

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        filteredRows = allRows.filter(row => {
            const text = row.textContent.toLowerCase();
            return text.includes(searchTerm);
        });
        
        currentPage = 1;
        updateTable();
    });

    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const visibleCheckboxes = Array.from(checkboxes).filter(cb => 
            cb.closest('tr').style.display !== 'none'
        );
        
        visibleCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Update table display
    function updateTable() {
        const start = (currentPage - 1) * recordsPerPage;
        const end = start + recordsPerPage;
        
        // Hide all rows first
        allRows.forEach(row => row.style.display = 'none');
        
        // Show filtered rows for current page
        filteredRows.slice(start, end).forEach(row => {
            row.style.display = '';
        });
        
        // Update pagination
        updatePagination();
        
        // Update showing info
        const totalRecords = filteredRows.length;
        const startRecord = totalRecords > 0 ? start + 1 : 0;
        const endRecord = Math.min(end, totalRecords);
        
        document.getElementById('startRecord').textContent = startRecord;
        document.getElementById('endRecord').textContent = endRecord;
        document.getElementById('showingInfo').innerHTML = 
            `Showing <span id="startRecord">${startRecord}</span> - <span id="endRecord">${endRecord}</span> of ${totalRecords} entries`;
    }

    // Update pagination buttons
    function updatePagination() {
        const totalPages = Math.ceil(filteredRows.length / recordsPerPage);
        const paginationDiv = document.getElementById('pagination');
        paginationDiv.innerHTML = '';
        
        if (totalPages <= 1) return;
        
        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Previous';
        prevBtn.className = 'page-btn';
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                updateTable();
            }
        };
        paginationDiv.appendChild(prevBtn);
        
        // Page numbers
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage < maxVisiblePages - 1) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        if (startPage > 1) {
            const firstBtn = document.createElement('button');
            firstBtn.textContent = '1';
            firstBtn.className = 'page-btn';
            firstBtn.onclick = () => goToPage(1);
            paginationDiv.appendChild(firstBtn);
            
            if (startPage > 2) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                dots.style.padding = '6px 12px';
                paginationDiv.appendChild(dots);
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.textContent = i;
            pageBtn.className = 'page-btn' + (i === currentPage ? ' active' : '');
            pageBtn.onclick = () => goToPage(i);
            paginationDiv.appendChild(pageBtn);
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                dots.style.padding = '6px 12px';
                paginationDiv.appendChild(dots);
            }
            
            const lastBtn = document.createElement('button');
            lastBtn.textContent = totalPages;
            lastBtn.className = 'page-btn';
            lastBtn.onclick = () => goToPage(totalPages);
            paginationDiv.appendChild(lastBtn);
        }
        
        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Next';
        nextBtn.className = 'page-btn';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = () => {
            if (currentPage < totalPages) {
                currentPage++;
                updateTable();
            }
        };
        paginationDiv.appendChild(nextBtn);
    }

    function goToPage(page) {
        currentPage = page;
        updateTable();
    }

    // Delete supplier function
    function deleteSupplier(id) {
        if (confirm('Are you sure you want to delete this supplier? This action cannot be undone.')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }

    // Payment Modal Functions
    let currentSupplierId = null;
    function closePaymentModal() {
    document.getElementById('paymentModal').classList.remove('show');
    document.getElementById('paymentForm').reset();
    currentSupplierId = null;
}

    function openPaymentModal(supplierId, supplierName, totalDue) {
    currentSupplierId = supplierId;
    document.getElementById('modalSupplierName').textContent = supplierName;
    document.getElementById('modalTotalDue').textContent = '৳' + parseFloat(totalDue).toFixed(2);
    
    // ✅ FIXED: Use Laravel url() helper
    const form = document.getElementById('paymentForm');
 form.action = `{{ url('supplier-payment') }}/${supplierId}`;

 
    
    // Load accounts
    loadAccounts();
    
    // Show modal
    document.getElementById('paymentModal').classList.add('show');
}

function loadAccounts() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    // ✅ FIXED: Use Laravel url() helper
    fetch(`{{ url('supplier-payment') }}/${currentSupplierId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Response Status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(html => {
                console.error('HTML Response:', html.substring(0, 200));
                throw new Error('Server returned HTML instead of JSON');
            });
        }
        
        return response.json();
    })
    .then(data => {
        console.log('✓ Success:', data);
        
        if (data.success) {
            const select = document.getElementById('accountSelect');
            select.innerHTML = '<option value="">Select Account</option>';
            
            data.accounts.forEach(account => {
                const option = document.createElement('option');
                option.value = account.id;
                option.textContent = `${account.account_name} (Balance: ৳${parseFloat(account.current_balance).toFixed(2)})`;
                select.appendChild(option);
            });
            
            console.log(`✓ Loaded ${data.accounts.length} accounts`);
        } else {
            throw new Error(data.message || 'Failed to load accounts');
        }
    })
    .catch(error => {
        console.error('✗ Error:', error);
        alert('Failed to load accounts: ' + error.message);
    });
}

    // Handle payment form submission
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closePaymentModal();
                
                // Show success message
                const successDiv = document.createElement('div');
                successDiv.style.cssText = 'background: #10b981; color: white; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;';
                successDiv.textContent = data.message;
                document.querySelector('.container-fluid').insertBefore(successDiv, document.querySelector('.container-fluid').firstChild);
                
                // Reload page after 1 second
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert(data.message || 'Failed to process payment');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the payment');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });

    // Close modal when clicking outside
    document.getElementById('paymentModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePaymentModal();
        }
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
</x-app-layout>