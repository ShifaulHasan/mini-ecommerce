<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">Purchase List</h2>
        <div>
            <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm me-2">
                <i class="bi bi-plus-circle"></i> Add Purchase
            </a>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-upload"></i> Import Purchase
            </button>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filters Card -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('purchases.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small">Start Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small">End Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small">Warehouse</label>
                        <select name="warehouse_id" class="form-select form-select-sm">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small">Purchase Status</label>
                        <select name="purchase_status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('purchase_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="received" {{ request('purchase_status') == 'received' ? 'selected' : '' }}>Received</option>
                            <option value="cancelled" {{ request('purchase_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small">Payment Status</label>
                        <select name="payment_status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-funnel"></i> Submit
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card">
        <div class="card-body">
            <!-- Search and Export Bar -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <form action="{{ route('purchases.index') }}" method="GET" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Search by reference or supplier..." 
                               value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
                
                <div class="col-md-6 text-end">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary" onclick="copyTable()">
                            <i class="bi bi-files"></i> Copy
                        </button>
                        <button class="btn btn-outline-secondary" onclick="exportCSV()">
                            <i class="bi bi-filetype-csv"></i> CSV
                        </button>
                        <button class="btn btn-outline-secondary" onclick="exportExcel()">
                            <i class="bi bi-file-earmark-excel"></i> Excel
                        </button>
                        <button class="btn btn-outline-secondary" onclick="exportPDF()">
                            <i class="bi bi-file-pdf"></i> PDF
                        </button>
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-sm" id="purchaseTable">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Supplier</th>
                            <th>Warehouse</th>
                            <th>Purchase Status</th>
                            <th>Grand Total</th>
                            <th>Returned</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ date('M d, Y', strtotime($purchase->purchase_date)) }}</td>
                            <td><strong>{{ $purchase->reference_number }}</strong></td>
                            <td>{{ $purchase->supplier->name }}</td>
                            <td>{{ $purchase->warehouse->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $purchase->purchase_status == 'received' ? 'success' : ($purchase->purchase_status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($purchase->purchase_status) }}
                                </span>
                            </td>
                            <td>${{ number_format($purchase->grand_total, 2) }}</td>
                            <td>${{ number_format($purchase->returned_amount, 2) }}</td>
                            <td>${{ number_format($purchase->paid_amount, 2) }}</td>
                            <td>${{ number_format($purchase->due_amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $purchase->payment_status == 'paid' ? 'success' : ($purchase->payment_status == 'partial' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($purchase->payment_status) }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Action
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('purchases.show', $purchase) }}">
                                            <i class="bi bi-eye"></i> View
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('purchases.edit', $purchase) }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">No purchases found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <form action="{{ route('purchases.index') }}" method="GET" class="d-flex align-items-center gap-2">
                        <label class="small mb-0">Show</label>
                        <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <label class="small mb-0">entries</label>
                    </form>
                </div>
                <div class="col-md-6">
                    {{ $purchases->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Purchases</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="#" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Select Excel or CSV File</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.csv" required>
                        </div>
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle"></i> Download the sample template to see the required format.
                            <a href="#" class="alert-link">Download Sample</a>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyTable() {
            const table = document.getElementById('purchaseTable');
            const range = document.createRange();
            range.selectNode(table);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
            alert('Table copied to clipboard!');
        }

        function exportCSV() {
            let csv = [];
            const rows = document.querySelectorAll('#purchaseTable tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = [], cols = rows[i].querySelectorAll('td, th');
                for (let j = 0; j < cols.length - 1; j++) { // Exclude action column
                    row.push(cols[j].innerText);
                }
                csv.push(row.join(','));
            }
            
            const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
            const downloadLink = document.createElement('a');
            downloadLink.download = 'purchases.csv';
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
        }

        function exportExcel() {
            alert('Excel export functionality - Install Laravel Excel package for full implementation');
        }

        function exportPDF() {
            window.print();
        }
    </script>
</x-app-layout>