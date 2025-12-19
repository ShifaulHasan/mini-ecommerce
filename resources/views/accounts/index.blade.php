<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Accounts') }}
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
                background: #17a2b8;
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
                background: #138496;
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
                appearance: auto;
                -webkit-appearance: menulist;
                -moz-appearance: menulist;
                cursor: pointer;
            }
            .search-box input {
                padding: 6px 12px;
                border: 1px solid #d1d5db;
                border-radius: 4px;
                width: 250px;
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
            /* Toggle Switch Styles */
            .toggle-switch {
                position: relative;
                display: inline-block;
                width: 60px;
                height: 28px;
            }
            
            .toggle-switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }
            
            .toggle-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ef4444;
                transition: 0.3s;
                border-radius: 28px;
            }
            
            .toggle-slider:before {
                position: absolute;
                content: "";
                height: 20px;
                width: 20px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: 0.3s;
                border-radius: 50%;
            }
            
            .toggle-switch input:checked + .toggle-slider {
                background-color: #10b981;
            }
            
            .toggle-switch input:checked + .toggle-slider:before {
                transform: translateX(32px);
            }
            
            .toggle-slider:after {
                content: 'Off';
                position: absolute;
                color: white;
                font-size: 11px;
                font-weight: 600;
                left: 8px;
                top: 50%;
                transform: translateY(-50%);
                transition: opacity 0.3s;
            }
            
            .toggle-switch input:checked + .toggle-slider:after {
                content: 'On';
                left: auto;
                right: 8px;
            }
            
            .toggle-slider:hover {
                opacity: 0.9;
            }
            .action-dropdown {
                position: relative;
                display: inline-block;
            }
            .action-btn {
                padding: 4px 12px;
                border: 1px solid #d1d5db;
                background: white;
                border-radius: 4px;
                cursor: pointer;
                font-size: 13px;
                text-decoration: none;
                color: #374151;
                display: inline-block;
            }
            .action-btn:hover {
                background: #f9fafb;
            }
            .dropdown-menu {
                display: none;
                position: absolute;
                right: 0;
                top: 100%;
                background: white;
                border: 1px solid #d1d5db;
                border-radius: 4px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                min-width: 120px;
                z-index: 1000;
                margin-top: 4px;
            }
            .dropdown-menu.show {
                display: block;
            }
            .dropdown-item {
                display: block;
                padding: 8px 16px;
                color: #374151;
                text-decoration: none;
                font-size: 13px;
                transition: background 0.2s;
            }
            .dropdown-item:hover {
                background: #f3f4f6;
            }
            .dropdown-item.delete {
                color: #ef4444;
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
            .total-row {
                font-weight: 600;
                background: #f9fafb;
            }
            .showing-info {
                text-align: center;
                margin-top: 10px;
                color: #6b7280;
                font-size: 13px;
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

                <!-- Header Section -->
                <div class="header-section">
                    <a href="{{ route('accounts.create') }}" class="btn-add">
                        + Add Account
                    </a>
                </div>

                <!-- Table Controls -->
                <div class="table-controls">
                    <div class="records-per-page">
                        <select id="recordsPerPage">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>records per page</span>
                    </div>
                    <div class="search-box">
                        <input type="text" placeholder="Search" id="searchInput">
                    </div>
                </div>

                <!-- Data Table -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                            <th>Account No</th>
                            <th>Name</th>
                            <th>Branch</th>
                            <th>Swift Code</th>
                            <th>Initial Balance</th>
                            <th>Default</th>
                            <th>Note</th>
                            <th style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($accounts as $account)
                        <tr class="data-row">
                            <td><input type="checkbox" class="row-checkbox"></td>
                            <td>{{ $account->account_no }}</td>
                            <td>{{ $account->name }}</td>
                            <td>{{ $account->branch ?? '-' }}</td>
                            <td>{{ $account->swift_code ?? '-' }}</td>
                            <td>{{ number_format($account->initial_balance, 2) }}</td>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" 
                                           {{ $account->is_default ? 'checked' : '' }}
                                           onchange="toggleDefault({{ $account->id }}, this.checked)">
                                    <span class="toggle-slider"></span>
                                </label>
                                
                                <!-- Toggle Form (hidden) -->
                                <form id="toggle-form-{{ $account->id }}" 
                                      action="{{ route('accounts.toggle-default', $account->id) }}" 
                                      method="POST" 
                                      style="display: none;">
                                    @csrf
                                    @method('PATCH')
                                </form>
                            </td>
                            <td>{{ $account->note ?? '' }}</td>
                            <td>
                                <div class="action-dropdown">
                                    <button type="button" class="action-btn" onclick="toggleDropdown(this)">
                                        action ▼
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="{{ route('accounts.edit', $account->id) }}" class="dropdown-item">Edit</a>
                                        <a href="#" onclick="deleteAccount({{ $account->id }})" class="dropdown-item delete">Delete</a>
                                    </div>
                                </div>
                                
                                <!-- Delete Form (hidden) -->
                                <form id="delete-form-{{ $account->id }}" 
                                      action="{{ route('accounts.destroy', $account->id) }}" 
                                      method="POST" 
                                      style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 20px;">No accounts found</td>
                        </tr>
                        @endforelse
                        
                        @if($accounts->count() > 0)
                        <tr class="total-row">
                            <td colspan="5"><strong>Total</strong></td>
                            <td><strong>{{ number_format($totalBalance, 2) }}</strong></td>
                            <td colspan="3"></td>
                        </tr>
                        @endif
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination" id="pagination"></div>

                <!-- Showing Info -->
                <div class="showing-info" id="showingInfo">
                    Showing <span id="startRecord">1</span> - <span id="endRecord">{{ min(10, $accounts->count()) }}</span> ({{ $accounts->count() }})
                </div>
            </div>
        </div>
    </div>

    <script>
            let currentPage = 1;
            let recordsPerPage = 10;
            let allRows = [];
            let filteredRows = [];

            // Initialize
            document.addEventListener('DOMContentLoaded', function() {
                allRows = Array.from(document.querySelectorAll('.data-row'));
                filteredRows = [...allRows];
                updatePagination();
                showPage(1);
            });

            // Select all checkboxes
            document.getElementById('selectAll').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.row-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });

            // Records per page change
            document.getElementById('recordsPerPage').addEventListener('change', function() {
                recordsPerPage = parseInt(this.value);
                currentPage = 1;
                updatePagination();
                showPage(1);
            });

            // Search functionality
            document.getElementById('searchInput').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                
                filteredRows = allRows.filter(row => {
                    const text = row.textContent.toLowerCase();
                    return text.includes(searchTerm);
                });

                currentPage = 1;
                updatePagination();
                showPage(1);
            });

            // Show specific page
            function showPage(page) {
                currentPage = page;
                
                // Hide all rows first
                allRows.forEach(row => row.style.display = 'none');
                
                // Calculate start and end indices
                const start = (page - 1) * recordsPerPage;
                const end = start + recordsPerPage;
                
                // Show only filtered rows for current page
                filteredRows.slice(start, end).forEach(row => row.style.display = '');
                
                // Update pagination buttons
                updatePaginationButtons();
                
                // Update showing info
                updateShowingInfo(start, end);
            }

            // Update pagination
            function updatePagination() {
                const totalPages = Math.ceil(filteredRows.length / recordsPerPage);
                const paginationDiv = document.getElementById('pagination');
                paginationDiv.innerHTML = '';

                // Previous button
                const prevBtn = document.createElement('button');
                prevBtn.className = 'page-btn';
                prevBtn.innerHTML = '‹';
                prevBtn.disabled = currentPage === 1;
                prevBtn.onclick = () => {
                    if (currentPage > 1) showPage(currentPage - 1);
                };
                paginationDiv.appendChild(prevBtn);

                // Page buttons
                for (let i = 1; i <= totalPages; i++) {
                    if (
                        i === 1 || 
                        i === totalPages || 
                        (i >= currentPage - 1 && i <= currentPage + 1)
                    ) {
                        const pageBtn = document.createElement('button');
                        pageBtn.className = 'page-btn' + (i === currentPage ? ' active' : '');
                        pageBtn.textContent = i;
                        pageBtn.onclick = () => showPage(i);
                        paginationDiv.appendChild(pageBtn);
                    } else if (
                        i === currentPage - 2 || 
                        i === currentPage + 2
                    ) {
                        const dots = document.createElement('span');
                        dots.textContent = '...';
                        dots.style.padding = '6px';
                        paginationDiv.appendChild(dots);
                    }
                }

                // Next button
                const nextBtn = document.createElement('button');
                nextBtn.className = 'page-btn';
                nextBtn.innerHTML = '›';
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.onclick = () => {
                    if (currentPage < totalPages) showPage(currentPage + 1);
                };
                paginationDiv.appendChild(nextBtn);
            }

            // Update pagination button states
            function updatePaginationButtons() {
                const buttons = document.querySelectorAll('.pagination .page-btn');
                buttons.forEach(btn => {
                    btn.classList.remove('active');
                    if (parseInt(btn.textContent) === currentPage) {
                        btn.classList.add('active');
                    }
                });
            }

            // Update showing info
            function updateShowingInfo(start, end) {
                const total = filteredRows.length;
                const actualEnd = Math.min(end, total);
                const actualStart = total > 0 ? start + 1 : 0;
                
                document.getElementById('startRecord').textContent = actualStart;
                document.getElementById('endRecord').textContent = actualEnd;
                document.getElementById('showingInfo').innerHTML = 
                    `Showing ${actualStart} - ${actualEnd} (${total})`;
            }

            // Toggle dropdown
            function toggleDropdown(button) {
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

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.action-dropdown')) {
                    document.querySelectorAll('.dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });

            // Delete account
            function deleteAccount(id) {
                if (confirm('Are you sure you want to delete this account?')) {
                    document.getElementById('delete-form-' + id).submit();
                }
                return false;
            }

            // Toggle default
            function toggleDefault(id, checked) {
                if (confirm('Are you sure you want to change the default account?')) {
                    document.getElementById('toggle-form-' + id).submit();
                } else {
                    // Revert checkbox if cancelled
                    event.target.checked = !checked;
                }
            }
    </script>
</x-app-layout>