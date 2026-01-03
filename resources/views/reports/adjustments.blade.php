<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-arrow-left-right"></i> Stock Adjustment Report
            </h2>
        </div>
    </x-slot>

    {{-- ================== DataTable CSS ================== --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">

                        {{-- ================== Filter Form ================== --}}
                        <form method="GET" action="{{ route('reports.adjustments') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" class="form-control"
                                           value="{{ request('start_date') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control"
                                           value="{{ request('end_date') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Product</label>
                                    <select name="product_id" class="form-select">
                                        <option value="">All Products</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Adjustment Type</label>
                                    <select name="adjustment_type" class="form-select">
                                        <option value="">All Types</option>
                                        <option value="addition"
                                            {{ request('adjustment_type') == 'addition' ? 'selected' : '' }}>
                                            Addition
                                        </option>
                                        <option value="subtraction"
                                            {{ request('adjustment_type') == 'subtraction' ? 'selected' : '' }}>
                                            Subtraction
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-funnel"></i> Filter
                                    </button>
                                    <a href="{{ route('reports.adjustments') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        {{-- ================== Report Table ================== --}}
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle"
                                   id="adjustmentTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>SL</th>
                                        <th>Date</th>
                                        <th>Product Code</th>
                                        <th>Product Name</th>
                                        <th>Warehouse</th>
                                        <th class="text-center">Type</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-center">Current Stock</th>
                                        <th class="text-center">New Stock</th>
                                        <th>Reason</th>
                                        <th>Created By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($adjustments as $index => $adjustment)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ date('d-m-Y H:i', strtotime($adjustment->adjustment_date)) }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $adjustment->product_code }}
                                                </span>
                                            </td>
                                            <td>{{ $adjustment->product_name }}</td>
                                            <td>{{ $adjustment->warehouse_name }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $adjustment->adjustment_type == 'addition' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($adjustment->adjustment_type) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $adjustment->adjustment_type == 'addition' ? 'success' : 'danger' }}">
                                                    {{ $adjustment->adjustment_type == 'addition' ? '+' : '-' }}
                                                    {{ $adjustment->quantity }}
                                                </span>
                                            </td>
                                            <td class="text-center">{{ $adjustment->current_stock }}</td>
                                            <td class="text-center">
                                                <strong>{{ $adjustment->new_stock }}</strong>
                                            </td>
                                            <td>{{ $adjustment->reason ?? 'N/A' }}</td>
                                            <td>{{ $adjustment->created_by_name ?? 'System' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-1"></i>
                                                <p class="mb-0">No adjustments found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================== DataTable JS ================== --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    {{-- ================== DataTable Init with Company Pad ================== --}}
    <script>
        $(document).ready(function () {
            $('#adjustmentTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'copy', className: 'btn btn-secondary btn-sm' },
                    { extend: 'csv', className: 'btn btn-info btn-sm' },
                    { extend: 'excel', className: 'btn btn-success btn-sm' },
                    { 
                        extend: 'pdf', 
                        className: 'btn btn-danger btn-sm',
                        title: '',
                        customize: function(doc) {
                            doc.content.splice(0, 0, [{
                                text: 'Inventory Management Software And Smart Billing System with E-Commerce\nLocation: Uttara, Dhaka\nEmail: inventory@test.com | Phone: 01710037283',
                                alignment: 'center',
                                margin: [0, 0, 0, 20],
                                fontSize: 12
                            }]);
                        }
                    },
                    { 
                        extend: 'print', 
                        className: 'btn btn-primary btn-sm',
                        title: '',
                        customize: function(win) {
                            $(win.document.body)
                                .css('font-size', '12pt')
                                .prepend(
                                    '<div style="text-align:center; margin-bottom:20px;">' +
                                    '<h3 style="margin-bottom:0;">Inventory Management Software And Smart Billing System with E-Commerce</h3>' +
                                    '<p style="margin:0;">Location: Uttara, Dhaka</p>' +
                                    '<p style="margin:0;">Email: inventory@test.com | Phone: 01710037283</p>' +
                                    '<hr style="border:1px solid #000; margin-top:10px; margin-bottom:10px;">' +
                                    '</div>'
                                );
                            $(win.document.body).find('table').addClass('display').css('font-size', '12pt');
                        }
                    }
                ],
                pageLength: 25,
                order: [[1, 'desc']]
            });
        });
    </script>

    <style>
        @media print {
            .btn, form, .navbar, .sidebar { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</x-app-layout>
