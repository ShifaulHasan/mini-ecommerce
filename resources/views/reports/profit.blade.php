<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-graph-up"></i> Profit Report
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
                        <form method="GET" action="{{ route('reports.profit') }}" class="mb-4">
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
                                <div class="col-md-4">
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
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-fill">
                                            <i class="bi bi-funnel"></i> Filter
                                        </button>
                                        <a href="{{ route('reports.profit') }}" class="btn btn-secondary">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        {{-- ================== Summary Cards ================== --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="card shadow-sm border-primary">
                                    <div class="card-body text-primary">
                                        <strong>Total Purchase Amount</strong>
                                        <h4>৳ {{ number_format($totals['purchase_amount'], 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card shadow-sm border-info">
                                    <div class="card-body text-info">
                                        <strong>Total Sales Amount</strong>
                                        <h4>৳ {{ number_format($totals['sales_amount'], 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card shadow-sm border-success">
                                    <div class="card-body text-success">
                                        <strong>Total Profit</strong>
                                        <h4>৳ {{ number_format($totals['profit'], 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================== Report Table ================== --}}
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle" id="profitTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>SL</th>
                                        <th>Product Code</th>
                                        <th>Product Name</th>
                                        <th>Purchase Qty</th>
                                        <th>Purchase Amount</th>
                                        <th>Sold Qty</th>
                                        <th>Sales Amount</th>
                                        <th>Current Stock</th>
                                        <th>Profit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($profits as $index => $row)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $row->product_code }}</td>
                                            <td>{{ $row->product_name }}</td>
                                            <td class="text-center">{{ number_format($row->purchase_qty) }}</td>
                                            <td class="text-end">৳{{ number_format($row->purchase_amount, 2) }}</td>
                                            <td class="text-center">{{ number_format($row->sold_qty) }}</td>
                                            <td class="text-end">৳{{ number_format($row->sales_amount, 2) }}</td>
                                            <td class="text-center fw-bold text-primary">{{ number_format($row->current_stock) }}</td>
                                            <td class="text-end {{ $row->profit >= 0 ? 'text-success fw-bold' : 'text-danger fw-bold' }}">
                                                ৳{{ number_format($row->profit, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-1"></i>
                                                <p class="mb-0">No profit data found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">৳{{ number_format($totals['purchase_amount'], 2) }}</th>
                                        <th class="text-center">—</th>
                                        <th class="text-end">৳{{ number_format($totals['sales_amount'], 2) }}</th>
                                        <th class="text-center">—</th>
                                        <th class="text-end text-success fw-bold">৳{{ number_format($totals['profit'], 2) }}</th>
                                    </tr>
                                </tfoot>
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

    <script>
        $(document).ready(function () {
            var logoBase64 = '';
            var logoUrl = "{{ asset('images/icon.png') }}";

            function getBase64Image(imgUrl, callback) {
                var img = new Image();
                img.crossOrigin = 'Anonymous';
                img.onload = function() {
                    var canvas = document.createElement('canvas');
                    canvas.width = this.width;
                    canvas.height = this.height;
                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(this, 0, 0);
                    callback(canvas.toDataURL('image/png'));
                };
                img.onerror = function() { callback(null); };
                img.src = imgUrl;
            }

            getBase64Image(logoUrl, function(base64) {
                if (base64) logoBase64 = base64;
            });

            $('#profitTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'copy', className: 'btn btn-secondary btn-sm' },
                    { extend: 'csv', className: 'btn btn-info btn-sm' },
                    { 
                        extend: 'excel', 
                        className: 'btn btn-success btn-sm',
                        footer: true
                    },
                    { 
                        extend: 'pdf', 
                        className: 'btn btn-danger btn-sm',
                        title: '',
                        footer: true,
                        orientation: 'landscape',
                        pageSize: 'A4',
                        customize: function(doc) {
                            // Header Content
                            var headerContent = [];
                            if (logoBase64) {
                                headerContent.push({ 
                                    image: logoBase64, 
                                    width: 60, 
                                    alignment: 'center', 
                                    margin:[0,0,0,10] 
                                });
                            }
                            headerContent.push({ 
                                text: 'Inventory Management Software', 
                                style:'header', 
                                alignment:'center', 
                                fontSize:16, 
                                bold:true, 
                                margin:[0,0,0,5] 
                            });
                            headerContent.push({ 
                                text: 'Smart Billing System With E-Commerce', 
                                alignment:'center', 
                                fontSize:12, 
                                margin:[0,0,0,8] 
                            });
                            headerContent.push({ 
                                text: 'Location: Uttara, Dhaka', 
                                alignment:'center', 
                                fontSize:10, 
                                margin:[0,0,0,3] 
                            });
                            headerContent.push({ 
                                text: 'Email: inventory@test.com | Phone: 01710037283', 
                                alignment:'center', 
                                fontSize:10, 
                                margin:[0,0,0,10] 
                            });

                            // Insert header
                            doc.content.splice(0,0,{ 
                                stack:headerContent, 
                                margin:[0,0,0,20] 
                            });
                            
                            // Add separator line
                            doc.content.splice(1,0,{ 
                                canvas:[{
                                    type:'line', 
                                    x1:0, 
                                    y1:0, 
                                    x2:750, 
                                    y2:0, 
                                    lineWidth:1.5, 
                                    lineColor:'#333'
                                }], 
                                margin:[0,0,0,15] 
                            });
                            
                            // Style the table
                            var objLayout = {};
                            objLayout['hLineWidth'] = function(i) { return 0.5; };
                            objLayout['vLineWidth'] = function(i) { return 0.5; };
                            objLayout['hLineColor'] = function(i) { return '#aaa'; };
                            objLayout['vLineColor'] = function(i) { return '#aaa'; };
                            objLayout['fillColor'] = function (rowIndex, node, columnIndex) {
                                if (rowIndex === 0) return '#333333'; // Header
                                if (rowIndex === node.table.body.length - 1) return '#e8e8e8'; // Footer
                                return null;
                            };
                            doc.content[2].layout = objLayout;
                            
                            // Make header text white
                            var tableBody = doc.content[2].table.body;
                            for (var i = 0; i < tableBody[0].length; i++) {
                                tableBody[0][i].color = 'white';
                                tableBody[0][i].bold = true;
                            }
                            
                            // Style footer row (last row)
                            var lastRowIndex = tableBody.length - 1;
                            for (var i = 0; i < tableBody[lastRowIndex].length; i++) {
                                tableBody[lastRowIndex][i].bold = true;
                                tableBody[lastRowIndex][i].fontSize = 10;
                            }
                            
                            // Adjust column widths
                            doc.content[2].table.widths = ['5%', '10%', '20%', '8%', '12%', '8%', '12%', '10%', '12%'];
                        }
                    },
                    { 
                        extend: 'print',
                        className: 'btn btn-primary btn-sm',
                        title: '',
                        footer: true,
                        customize: function(win) {
                            var printLogo = logoBase64 || logoUrl;
                            
                            $(win.document.body)
                                .css('font-size','11pt')
                                .prepend(
                                    '<div style="text-align:center; margin-bottom:25px; padding:20px 0;">' +
                                    '<img src="'+printLogo+'" style="width:60px;height:60px;border-radius:50%; margin-bottom:10px; display:block; margin-left:auto; margin-right:auto;" />' +
                                    '<h2 style="margin:0 0 5px 0; font-size:18px; font-weight:bold; color:#333;">Inventory Management Software</h2>' +
                                    '<p style="margin:0 0 8px 0; font-size:13px; color:#666;">Smart Billing System With E-Commerce</p>' +
                                    '<p style="margin:0 0 3px 0; font-size:11px; color:#888;">Location: Uttara, Dhaka</p>' +
                                    '<p style="margin:0 0 10px 0; font-size:11px; color:#888;">Email: inventory@test.com | Phone: 01710037283</p>' +
                                    '<hr style="border:none; border-top:1.5px solid #333; margin:10px 0 20px 0;">'+
                                    '</div>'
                                );
                            
                            $(win.document.body).find('table')
                                .addClass('display')
                                .css('font-size', '10pt');
                            
                            // Style the footer row
                            $(win.document.body).find('tfoot tr')
                                .css('background-color', '#f0f0f0')
                                .css('font-weight', 'bold')
                                .css('border-top', '2px solid #333');
                            
                            // Add print styles
                            $(win.document.head).append(
                                '<style>' +
                                '@media print {' +
                                '  body { margin: 15px; }' +
                                '  table { width: 100%; border-collapse: collapse; }' +
                                '  th, td { padding: 6px; border: 1px solid #ddd; }' +
                                '  thead th { background-color: #333 !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }' +
                                '  tfoot th { background-color: #e8e8e8 !important; font-weight: bold !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }' +
                                '  img { -webkit-print-color-adjust: exact; print-color-adjust: exact; }' +
                                '}' +
                                '</style>'
                            );
                        }
                    }
                ],
                pageLength: 25,
                order: [[8,'desc']],
                footerCallback: function (row, data, start, end, display) {
                    // This ensures footer stays visible
                }
            });
        });
    </script>

    <style>
        @media print {
            .btn, form, .navbar, .sidebar { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
        
        tfoot th {
            font-weight: 600;
            background-color: #f8f9fa;
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