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

    <!-- Initialize DataTable with Company Logo -->
    <script>
        $(document).ready(function() {
            // Convert logo image to base64 for PDF
            var logoBase64 = '';
            
            function getBase64Image(imgUrl, callback) {
                var img = new Image();
                img.crossOrigin = 'Anonymous';
                img.onload = function() {
                    var canvas = document.createElement('canvas');
                    canvas.width = this.width;
                    canvas.height = this.height;
                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(this, 0, 0);
                    var dataURL = canvas.toDataURL('image/png');
                    callback(dataURL);
                };
                img.onerror = function() {
                    console.error('Failed to load image');
                    callback(null);
                };
                img.src = imgUrl;
            }

            // Get logo URL and convert to base64
            var logoUrl = "{{ asset('images/icon.png') }}";
            
            getBase64Image(logoUrl, function(base64) {
                if (base64) {
                    logoBase64 = base64;
                }
            });

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
                        title: '',
                        customize: function(doc) {
                            // Add company header with logo
                            var headerContent = [];
                            
                            // Add logo if available
                            if (logoBase64) {
                                headerContent.push({
                                    image: logoBase64,
                                    width: 60,
                                    alignment: 'center',
                                    margin: [0, 0, 0, 10]
                                });
                            }
                            
                            // Add company info
                            headerContent.push({
                                text: 'Inventory Management Software',
                                style: 'header',
                                alignment: 'center',
                                fontSize: 16,
                                bold: true,
                                margin: [0, 0, 0, 5]
                            });
                            
                            headerContent.push({
                                text: 'Smart Billing System With E-Commerce',
                                alignment: 'center',
                                fontSize: 12,
                                margin: [0, 0, 0, 8]
                            });
                            
                            headerContent.push({
                                text: 'Location: Uttara, Dhaka',
                                alignment: 'center',
                                fontSize: 10,
                                margin: [0, 0, 0, 3]
                            });
                            
                            headerContent.push({
                                text: 'Email: inventory@test.com | Phone: 01710037283',
                                alignment: 'center',
                                fontSize: 10,
                                margin: [0, 0, 0, 10]
                            });

                            // Insert header at the beginning
                            doc.content.splice(0, 0, {
                                stack: headerContent,
                                margin: [0, 0, 0, 20]
                            });

                            // Add separator line
                            doc.content.splice(1, 0, {
                                canvas: [{
                                    type: 'line',
                                    x1: 0,
                                    y1: 0,
                                    x2: 515,
                                    y2: 0,
                                    lineWidth: 1.5,
                                    lineColor: '#333'
                                }],
                                margin: [0, 0, 0, 15]
                            });
                        }
                    },
                    { 
                        extend: 'print', 
                        className: 'btn btn-primary btn-sm',
                        title: '',
                        customize: function(win) {
                            // Use base64 logo if available, otherwise use direct URL
                            var printLogo = logoBase64 || logoUrl;
                            
                            $(win.document.body)
                                .css('font-size', '12pt')
                                .prepend(
                                    '<div style="text-align:center; margin-bottom:25px; padding:20px 0;">' +
                                    '<img src="' + printLogo + '" style="width:60px; height:60px; border-radius:50%; margin-bottom:10px; display:block; margin-left:auto; margin-right:auto;" />' +
                                    '<h2 style="margin:0 0 5px 0; font-size:18px; font-weight:bold; color:#333;">Inventory Management Software</h2>' +
                                    '<p style="margin:0 0 8px 0; font-size:13px; color:#666;">Smart Billing System With E-Commerce</p>' +
                                    '<p style="margin:0 0 3px 0; font-size:11px; color:#888;">Location: Uttara, Dhaka</p>' +
                                    '<p style="margin:0 0 10px 0; font-size:11px; color:#888;">Email: inventory@test.com | Phone: 01710037283</p>' +
                                    '<hr style="border:none; border-top:1.5px solid #333; margin:10px 0 20px 0;">' +
                                    '</div>'
                                );
                            
                            $(win.document.body).find('table')
                                .addClass('display')
                                .css('font-size', '11pt');
                            
                            // Add print styles
                            $(win.document.head).append(
                                '<style>' +
                                '@media print {' +
                                '  body { margin: 20px; }' +
                                '  table { width: 100%; border-collapse: collapse; }' +
                                '  th, td { padding: 8px; border: 1px solid #ddd; }' +
                                '  th { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }' +
                                '  img { -webkit-print-color-adjust: exact; print-color-adjust: exact; }' +
                                '}' +
                                '</style>'
                            );
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

    <!-- Footer Note -->
    <div class="row mt-4 mb-3">
        <div class="col-12">
            <p class="text-center text-muted small mb-0">
                Developed by Shifaul Hasan &copy; 2026
            </p>
        </div>
    </div>

</x-app-layout>