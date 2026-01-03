<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-people"></i> Customer Report
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
                        <form method="GET" action="{{ route('reports.customers') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-10">
                                    <label class="form-label">Search Customer</label>
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Search by name, code, or phone"
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                </div>
                            </div>
                        </form>

                        {{-- ================= Summary Cards ================= --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 opacity-75">Total Sales</h6>
                                        <h3 class="mb-0">৳{{ number_format($totals['total_sales'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 opacity-75">Total Paid</h6>
                                        <h3 class="mb-0">৳{{ number_format($totals['total_paid'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 opacity-75">Total Due</h6>
                                        <h3 class="mb-0">৳{{ number_format($totals['total_due'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= Report Table ================= --}}
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle"
                                   id="customerReportTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>SL</th>
                                        <th>Customer Code</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>City</th>
                                        <th class="text-center">Total Orders</th>
                                        <th class="text-end">Total Sales</th>
                                        <th class="text-end">Total Paid</th>
                                        <th class="text-end">Total Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customers as $index => $customer)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $customer->customer_code }}
                                                </span>
                                            </td>
                                            <td>{{ $customer->name }}</td>
                                            <td>{{ $customer->email ?? 'N/A' }}</td>
                                            <td>{{ $customer->phone ?? 'N/A' }}</td>
                                            <td>{{ $customer->city ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-info">
                                                    {{ $customer->total_orders }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                ৳{{ number_format($customer->total_sales, 2) }}
                                            </td>
                                            <td class="text-end text-success fw-bold">
                                                ৳{{ number_format($customer->total_paid, 2) }}
                                            </td>
                                            <td class="text-end text-danger fw-bold">
                                                ৳{{ number_format($customer->total_due, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-1"></i>
                                                <p class="mb-0">No customers found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                @if(count($customers) > 0)
                                    <tfoot class="table-secondary fw-bold">
                                        <tr>
                                            <td colspan="7" class="text-end">Grand Total:</td>
                                            <td class="text-end">
                                                ৳{{ number_format($totals['total_sales'], 2) }}
                                            </td>
                                            <td class="text-end text-success">
                                                ৳{{ number_format($totals['total_paid'], 2) }}
                                            </td>
                                            <td class="text-end text-danger">
                                                ৳{{ number_format($totals['total_due'], 2) }}
                                            </td>
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

    {{-- ================= DataTable Buttons ================= --}}
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    {{-- ================= DataTable Init with Company Pad ================= --}}
    <script>
        $(document).ready(function () {
            $('#customerReportTable').DataTable({
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
                order: [[1, 'asc']]
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
