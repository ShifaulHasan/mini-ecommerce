<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-bag"></i> Purchase Report
            </h2>
        </div>
    </x-slot>

    {{-- ================= DataTable CSS ================= --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">

                        {{-- ================= Filter Form ================= --}}
                        <form method="GET" action="{{ route('reports.purchases') }}" class="mb-4">
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
                                    <label class="form-label">Supplier</label>
                                    <select name="supplier_id" class="form-select">
                                        <option value="">All Suppliers</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}"
                                                {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Payment Status</label>
                                    <select name="payment_status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-funnel"></i> Filter
                                    </button>
                                    <a href="{{ route('reports.purchases') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        {{-- ================= Summary Cards ================= --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="opacity-75">Total Purchases</h6>
                                        <h3 class="mb-0">৳{{ number_format($totals['total_purchases'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="opacity-75">Total Paid</h6>
                                        <h3 class="mb-0">৳{{ number_format($totals['total_paid'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h6 class="opacity-75">Total Due</h6>
                                        <h3 class="mb-0">৳{{ number_format($totals['total_due'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= Report Table ================= --}}
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle"
                                   id="purchaseReportTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>SL</th>
                                        <th>Date</th>
                                        <th>Reference No</th>
                                        <th>Supplier</th>
                                        <th>Warehouse</th>
                                        <th class="text-end">Total Amount</th>
                                        <th class="text-end">Paid</th>
                                        <th class="text-end">Due</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Payment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($purchases as $index => $purchase)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ date('d-m-Y', strtotime($purchase->purchase_date)) }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $purchase->reference_no }}
                                                </span>
                                            </td>
                                            <td>{{ $purchase->supplier_name }}</td>
                                            <td>{{ $purchase->warehouse_name ?? 'N/A' }}</td>
                                            <td class="text-end">
                                                ৳{{ number_format($purchase->grand_total, 2) }}
                                            </td>
                                            <td class="text-end text-success">
                                                ৳{{ number_format($purchase->paid_amount, 2) }}
                                            </td>
                                            <td class="text-end text-danger">
                                                ৳{{ number_format($purchase->due_amount, 2) }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $purchase->purchase_status == 'received' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($purchase->purchase_status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $purchase->payment_status == 'paid' ? 'success' : ($purchase->payment_status == 'partial' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($purchase->payment_status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-1"></i>
                                                <p class="mb-0">No purchases found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                @if(count($purchases) > 0)
                                <tfoot class="table-secondary fw-bold">
                                    <tr>
                                        <td colspan="5" class="text-end">Grand Total:</td>
                                        <td class="text-end">৳{{ number_format($totals['total_purchases'], 2) }}</td>
                                        <td class="text-end text-success">৳{{ number_format($totals['total_paid'], 2) }}</td>
                                        <td class="text-end text-danger">৳{{ number_format($totals['total_due'], 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= DataTable JS ================= --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    {{-- ================= Buttons ================= --}}
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    {{-- ================= Init DataTable with Company Pad ================= --}}
    <script>
        $(document).ready(function () {
            $('#purchaseReportTable').DataTable({
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
                                text: 'Inventory Management Software And Smart Billing System with E-Commerce\nLocation: Uttara Sector-10, Dhaka, Bangladesh\nEmail: inventory@test.com | Phone: 01710037283',
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
                                    '<p style="margin:0;">Location: Uttara Sector-10, Dhaka, Bangladesh</p>' +
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
