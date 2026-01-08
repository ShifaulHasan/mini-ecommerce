<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-truck"></i> Supplier Report
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
                        <form method="GET" action="{{ route('reports.suppliers') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-10">
                                    <label class="form-label">Search Supplier</label>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Search by name, company, or phone" 
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
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 opacity-75">Total Purchases</h6>
                                        <h3 class="card-title mb-0">৳{{ number_format($totals['total_purchases'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 opacity-75">Total Paid</h6>
                                        <h3 class="card-title mb-0">৳{{ number_format($totals['total_paid'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 opacity-75">Total Due</h6>
                                        <h3 class="card-title mb-0">৳{{ number_format($totals['total_due'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= Report Table ================= --}}
                        <div class="table-responsive">
                            <table id="supplierTable" class="table table-hover table-bordered align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>SL</th>
                                        <th>Name</th>
                                        <th>Company</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>City</th>
                                        <th class="text-center">Total Orders</th>
                                        <th class="text-end">Total Purchases</th>
                                        <th class="text-end">Total Paid</th>
                                        <th class="text-end">Total Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($suppliers as $index => $supplier)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $supplier->name }}</td>
                                        <td>{{ $supplier->company ?? 'N/A' }}</td>
                                        <td>{{ $supplier->email ?? 'N/A' }}</td>
                                        <td>{{ $supplier->phone ?? 'N/A' }}</td>
                                        <td>{{ $supplier->city ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $supplier->total_orders }}</span>
                                        </td>
                                        <td class="text-end">৳{{ number_format($supplier->total_purchases, 2) }}</td>
                                        <td class="text-end text-success fw-bold">৳{{ number_format($supplier->total_paid, 2) }}</td>
                                        <td class="text-end text-danger fw-bold">৳{{ number_format($supplier->total_due, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                @if(count($suppliers) > 0)
                                <tfoot class="table-secondary">
                                    <tr class="fw-bold">
                                        <td colspan="7" class="text-end">Grand Total:</td>
                                        <td class="text-end">৳{{ number_format($totals['total_purchases'], 2) }}</td>
                                        <td class="text-end text-success">৳{{ number_format($totals['total_paid'], 2) }}</td>
                                        <td class="text-end text-danger">৳{{ number_format($totals['total_due'], 2) }}</td>
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
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    {{-- ================= DataTable Init with Company Logo ================= --}}
    <script>
        $(document).ready(function () {
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

            $('#supplierTable').DataTable({
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
                                text: 'Inventory Management Software And',
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
                pageLength: 25,
                order: [[0, 'asc']]
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