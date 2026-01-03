<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0"><i class="bi bi-cart-check"></i> Sale Report</h2>
        </div>
    </x-slot>

    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-body">

                <!-- Filter Form -->
                <form method="GET" action="{{ route('reports.sales') }}" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Customer</label>
                            <select name="customer_id" class="form-select">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id')==$customer->id?'selected':'' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Payment Status</label>
                            <select name="payment_status" class="form-select">
                                <option value="">All</option>
                                <option value="paid" {{ request('payment_status')=='paid'?'selected':'' }}>Paid</option>
                                <option value="partial" {{ request('payment_status')=='partial'?'selected':'' }}>Partial</option>
                                <option value="pending" {{ request('payment_status')=='pending'?'selected':'' }}>Pending</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
                        <a href="{{ route('reports.sales') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>

                <!-- Sales Table -->
                <div class="table-responsive">
                    <table id="salesTable" class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>SL</th>
                                <th>Date</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Warehouse</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-end">Tax</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Net Total</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Due</th>
                                <th>Status</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $index => $sale)
                                <tr>
                                    <td>{{ $index+1 }}</td>
                                    <td>{{ date('d-m-Y', strtotime($sale->sale_date)) }}</td>
                                    <td>{{ $sale->reference_number }}</td>
                                    <td>{{ $sale->customer_name }}</td>
                                    <td>{{ $sale->warehouse_name ?? 'N/A' }}</td>
                                    <td class="text-end">৳{{ number_format($sale->grand_total,2) }}</td>
                                    <td class="text-end">৳{{ number_format($sale->tax_amount,2) }}</td>
                                    <td class="text-end">৳{{ number_format($sale->discount_amount,2) }}</td>
                                    <td class="text-end fw-bold">৳{{ number_format($sale->grand_total + $sale->tax_amount - $sale->discount_amount,2) }}</td>
                                    <td class="text-end text-success">৳{{ number_format($sale->paid_amount,2) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($sale->due_amount,2) }}</td>
                                    <td><span class="badge bg-{{ $sale->sale_status=='completed'?'success':'warning' }}">{{ ucfirst($sale->sale_status) }}</span></td>
                                    <td><span class="badge bg-{{ $sale->payment_status=='paid'?'success':($sale->payment_status=='partial'?'warning':'danger') }}">{{ ucfirst($sale->payment_status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- DataTable CSS & JS (CDN) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <!-- Initialize DataTable with Company Header -->
    <script>
        $(document).ready(function() {
            $('#salesTable').DataTable({
                pageLength: 25,
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'copy', className: 'btn btn-secondary btn-sm' },
                    { extend: 'excel', className: 'btn btn-success btn-sm' },
                    { extend: 'csv', className: 'btn btn-info btn-sm' },
                    { 
                        extend: 'pdf', 
                        className: 'btn btn-danger btn-sm',
                        title: '', // Remove default title
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
