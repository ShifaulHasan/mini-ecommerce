<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Supplier Due Report') }}
        </h2>
    </x-slot>

    <style>
        .container-fluid {
            padding: 20px;
        }
        
        .report-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .supplier-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            padding: 15px;
            background: #f9fafb;
            border-radius: 6px;
        }
        
        .info-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
        }
        
        .due-amount {
            color: #ef4444;
            font-size: 20px;
        }
        
        .filters-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }
        
        .filter-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #8b5cf6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #7c3aed;
        }
        
        .btn-export {
            background: #10b981;
            color: white;
        }
        
        .btn-export:hover {
            background: #059669;
        }
        
        .btn-print {
            background: #3b82f6;
            color: white;
        }
        
        .btn-print:hover {
            background: #2563eb;
        }
        
        .export-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
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
        
        .amount-cell {
            text-align: right;
            font-weight: 500;
        }
        
        .amount-due {
            color: #ef4444;
        }
        
        .amount-paid {
            color: #10b981;
        }
        
        .summary-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .summary-item {
            padding: 15px;
            background: #f9fafb;
            border-radius: 6px;
            text-align: center;
        }
        
        .summary-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        
        .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }
        
        .payments-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #111827;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            .report-header {
                box-shadow: none;
                border: 1px solid #e5e7eb;
            }
        }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="container-fluid">
                <!-- Back Button -->
                <div class="no-print" style="margin-bottom: 20px;">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Suppliers
                    </a>
                </div>

                <!-- Supplier Info Header -->
                <div class="report-header">
                    <h3 class="section-title">Supplier Information</h3>
                    <div class="supplier-info">
                        <div class="info-item">
                            <div class="info-label">Supplier Name</div>
                            <div class="info-value">{{ $supplier->name }}</div>
                        </div>
                        @if($supplier->company)
                        <div class="info-item">
                            <div class="info-label">Company</div>
                            <div class="info-value">{{ $supplier->company }}</div>
                        </div>
                        @endif
                        @if($supplier->phone)
                        <div class="info-item">
                            <div class="info-label">Phone</div>
                            <div class="info-value">{{ $supplier->phone }}</div>
                        </div>
                        @endif
                        @if($supplier->email)
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value">{{ $supplier->email }}</div>
                        </div>
                        @endif
                        <div class="info-item">
                            <div class="info-label">Total Due Amount</div>
                            <div class="info-value due-amount">৳{{ number_format($supplier->total_due, 2) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="filters-section no-print">
                    <form method="GET" action="{{ route('supplier.due.report', $supplier->id) }}">

                        <div class="filter-row">
                            <div class="filter-group">
                                <label class="filter-label">Start Date</label>
                                <input type="date" name="start_date" class="filter-input" 
                                       value="{{ request('start_date', '1995-12-24') }}">
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">End Date</label>
                                <input type="date" name="end_date" class="filter-input" 
                                       value="{{ request('end_date', date('Y-m-d')) }}">
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">Search</label>
                                <input type="text" name="search" class="filter-input" 
                                       placeholder="Search by reference..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div style="margin-top: 28px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Export Buttons -->
                <div class="export-buttons no-print">
                    <button onclick="exportToPDF()" class="btn btn-export">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                    <button onclick="exportToExcel()" class="btn btn-export">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button onclick="window.print()" class="btn btn-print">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>

                <!-- Purchase Records Table -->
                <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 class="section-title">Purchase Records</h3>
                    
                    <div class="table-controls no-print">
                        <div class="records-per-page">
                            <span>Show</span>
                            <select id="recordsPerPage" class="filter-input" style="width: auto;">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>entries</span>
                        </div>
                        <div class="search-box">
                            <input type="text" placeholder="Search in table..." id="tableSearchInput">
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="data-table" id="purchaseTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Supplier Details</th>
                                    <th class="amount-cell">Grand Total</th>
                                    <th class="amount-cell">Returned Amount</th>
                                    <th class="amount-cell">Paid</th>
                                    <th class="amount-cell">Due</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @php
                                    $totalGrandTotal = 0;
                                    $totalReturned = 0;
                                    $totalPaid = 0;
                                    $totalDue = 0;
                                @endphp
                                @forelse($purchases as $purchase)
                                    @php
                                        $grandTotal = $purchase->grand_total ?? 0;
                                        $returned = 0;
                                        $paid = $purchase->paid_amount ?? 0;
                                        $due = $purchase->due_amount ?? 0;
                                        
                                        $totalGrandTotal += $grandTotal;
                                        $totalReturned += $returned;
                                        $totalPaid += $paid;
                                        $totalDue += $due;
                                    @endphp
                                    <tr class="data-row">
                                        <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                                        <td><strong>{{ $purchase->reference_no }}</strong></td>
                                        <td>
                                            <div style="font-weight: 500;">{{ $supplier->name }}</div>
                                            @if($supplier->company)
                                                <div style="font-size: 12px; color: #6b7280;">{{ $supplier->company }}</div>
                                            @endif
                                        </td>
                                        <td class="amount-cell">৳{{ number_format($grandTotal, 2) }}</td>
                                        <td class="amount-cell">৳{{ number_format($returned, 2) }}</td>
                                        <td class="amount-cell amount-paid">৳{{ number_format($paid, 2) }}</td>
                                        <td class="amount-cell amount-due">৳{{ number_format($due, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 20px;">No purchase records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($purchases->count() > 0)
                            <tfoot style="background: #f9fafb; font-weight: 600;">
                                <tr>
                                    <td colspan="3" style="text-align: right; padding: 12px;">
                                        <strong>Total:</strong>
                                    </td>
                                    <td class="amount-cell">৳{{ number_format($totalGrandTotal, 2) }}</td>
                                    <td class="amount-cell">৳{{ number_format($totalReturned, 2) }}</td>
                                    <td class="amount-cell amount-paid">৳{{ number_format($totalPaid, 2) }}</td>
                                    <td class="amount-cell amount-due">৳{{ number_format($totalDue, 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

                    <div class="pagination no-print" id="pagination"></div>
                    <div class="showing-info no-print" id="showingInfo" style="text-align: center; margin-top: 10px; color: #6b7280; font-size: 13px;">
                        Showing <span id="startRecord">0</span> - <span id="endRecord">0</span> of {{ $purchases->count() }} entries
                    </div>
                </div>

                <!-- Payment History -->
                <div class="payments-section">
                    <h3 class="section-title">Payment History</h3>
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Reference No</th>
                                    <th>Account</th>
                                    <th>Payment Method</th>
                                    <th class="amount-cell">Amount</th>
                                    <th>Created By</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                        <td>{{ $payment->reference_no ?? '-' }}</td>
                                        <td>{{ $payment->account->account_name ?? 'N/A' }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</td>
                                        <td class="amount-cell amount-paid">৳{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->creator->name ?? 'N/A' }}</td>
                                        <td>{{ $payment->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 20px;">No payment records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="summary-section">
                    <h3 class="section-title">Summary</h3>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <div class="summary-label">Total Purchases</div>
                            <div class="summary-value">৳{{ number_format($totalGrandTotal, 2) }}</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Total Paid</div>
                            <div class="summary-value" style="color: #10b981;">৳{{ number_format($totalPaid, 2) }}</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Total Due</div>
                            <div class="summary-value" style="color: #ef4444;">৳{{ number_format($totalDue, 2) }}</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Total Payments Made</div>
                            <div class="summary-value" style="color: #3b82f6;">৳{{ number_format($payments->sum('amount'), 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    
    <script>
        let currentPage = 1;
        let recordsPerPage = 25;
        let allRows = [];
        let filteredRows = [];

        document.addEventListener('DOMContentLoaded', function() {
            allRows = Array.from(document.querySelectorAll('.data-row'));
            filteredRows = [...allRows];
            updateTable();
        });

        document.getElementById('recordsPerPage').addEventListener('change', function() {
            recordsPerPage = parseInt(this.value);
            currentPage = 1;
            updateTable();
        });

        document.getElementById('tableSearchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filteredRows = allRows.filter(row => row.textContent.toLowerCase().includes(searchTerm));
            currentPage = 1;
            updateTable();
        });

        function updateTable() {
            const start = (currentPage - 1) * recordsPerPage;
            const end = start + recordsPerPage;
            
            allRows.forEach(row => row.style.display = 'none');
            filteredRows.slice(start, end).forEach(row => row.style.display = '');
            
            updatePagination();
            
            const totalRecords = filteredRows.length;
            const startRecord = totalRecords > 0 ? start + 1 : 0;
            const endRecord = Math.min(end, totalRecords);
            
            document.getElementById('startRecord').textContent = startRecord;
            document.getElementById('endRecord').textContent = endRecord;
        }

        function updatePagination() {
            const totalPages = Math.ceil(filteredRows.length / recordsPerPage);
            const paginationDiv = document.getElementById('pagination');
            paginationDiv.innerHTML = '';
            
            if (totalPages <= 1) return;
            
            const prevBtn = document.createElement('button');
            prevBtn.textContent = 'Previous';
            prevBtn.className = 'btn btn-primary';
            prevBtn.style.cssText = 'padding: 6px 12px; margin: 0 5px;';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; updateTable(); } };
            paginationDiv.appendChild(prevBtn);
            
            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.textContent = i;
                pageBtn.className = 'btn' + (i === currentPage ? ' btn-primary' : '');
                pageBtn.style.cssText = 'padding: 6px 12px; margin: 0 2px; background: ' + (i === currentPage ? '#8b5cf6' : '#f3f4f6') + '; color: ' + (i === currentPage ? 'white' : '#111827');
                pageBtn.onclick = () => { currentPage = i; updateTable(); };
                paginationDiv.appendChild(pageBtn);
            }
            
            const nextBtn = document.createElement('button');
            nextBtn.textContent = 'Next';
            nextBtn.className = 'btn btn-primary';
            nextBtn.style.cssText = 'padding: 6px 12px; margin: 0 5px;';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; updateTable(); } };
            paginationDiv.appendChild(nextBtn);
        }

        function exportToExcel() {
            const table = document.getElementById('purchaseTable');
            const wb = XLSX.utils.table_to_book(table, {sheet: "Supplier Due Report"});
            XLSX.writeFile(wb, 'supplier_due_report_{{ $supplier->name }}.xlsx');
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            doc.setFontSize(18);
            doc.text('Supplier Due Report', 14, 20);
            
            doc.setFontSize(12);
            doc.text('Supplier: {{ $supplier->name }}', 14, 30);
            doc.text('Total Due: ৳{{ number_format($supplier->total_due, 2) }}', 14, 37);
            doc.text('Date: {{ date("d M Y") }}', 14, 44);
            
            const tableData = [];
            const rows = document.querySelectorAll('#purchaseTable tbody tr:not([style*="display: none"])');
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 0) {
                    tableData.push([
                        cells[0].textContent.trim(),
                        cells[1].textContent.trim(),
                        cells[2].textContent.trim(),
                        cells[3].textContent.trim(),
                        cells[4].textContent.trim(),
                        cells[5].textContent.trim(),
                        cells[6].textContent.trim()
                    ]);
                }
            });
            
            doc.autoTable({
                startY: 50,
                head: [['Date', 'Reference', 'Supplier', 'Grand Total', 'Returned', 'Paid', 'Due']],
                body: tableData,
                theme: 'grid',
                headStyles: { fillColor: [139, 92, 246] },
                styles: { fontSize: 9 }
            });
            
            doc.save('supplier_due_report_{{ $supplier->name }}.pdf');
        }
    </script>
</x-app-layout>