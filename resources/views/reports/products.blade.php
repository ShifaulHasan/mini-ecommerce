<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-box"></i> Product Report
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
                        <form method="GET" action="{{ route('reports.products') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Brand</label>
                                    <select name="brand_id" class="form-select">
                                        <option value="">All Brands</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}"
                                                {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-fill">
                                            <i class="bi bi-funnel"></i> Filter
                                        </button>
                                        <a href="{{ route('reports.products') }}" class="btn btn-secondary">
                                            <i class="bi bi-arrow-clockwise"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        {{-- ================== Report Table ================== --}}
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle"
                                   id="productsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>SL</th>
                                        <th>Product Code</th>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Brand</th>
                                        <th>Cost Price</th>
                                        <th>Selling Price</th>
                                        <th>Stock</th>
                                        <th>Profit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalStock = 0;
                                    @endphp
                                    @forelse($products as $index => $product)
                                        @php
                                            $totalStock += $product->current_stock;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $product->product_code }}</td>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->category_name ?? 'N/A' }}</td>
                                            <td>{{ $product->brand_name ?? 'N/A' }}</td>
                                            <td>৳{{ number_format($product->cost_price, 2) }}</td>
                                            <td>৳{{ number_format($product->selling_price, 2) }}</td>
                                            <td>{{ $product->current_stock }}</td>
                                            <td>৳{{ number_format($product->profit_margin, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-1"></i>
                                                <p class="mb-0">No products found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(count($products) > 0)
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="7" class="text-end">Total Stock:</th>
                                        <th>{{ number_format($totalStock) }}</th>
                                        <th></th>
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

    {{-- ================== DataTable JS ================== --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    {{-- ================== DataTable Init with Company Logo ================== --}}
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

            $('#productsTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    { 
                        extend: 'copy', 
                        className: 'btn btn-secondary btn-sm',
                        exportOptions: {
                            footer: false
                        }
                    },
                    { 
                        extend: 'csv', 
                        className: 'btn btn-info btn-sm',
                        exportOptions: {
                            footer: false
                        }
                    },
                    { 
                        extend: 'excel', 
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            footer: false
                        }
                    },
                    { 
                        extend: 'pdf', 
                        className: 'btn btn-danger btn-sm',
                        title: '',
                        exportOptions: {
                            footer: false
                        },
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
                                text: 'and Smart Billing System ',
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

                            // Add total stock footer manually
                            var totalStock = '{{ number_format($totalStock) }}';
                            doc.content.push({
                                table: {
                                    widths: ['*', 'auto'],
                                    body: [
                                        [
                                            {text: 'Total Stock:', alignment: 'right', bold: true, fillColor: '#e9ecef'},
                                            {text: totalStock, bold: true, fillColor: '#e9ecef'}
                                        ]
                                    ]
                                },
                                margin: [0, 10, 0, 0]
                            });
                        }
                    },
                    { 
                        extend: 'print', 
                        className: 'btn btn-primary btn-sm',
                        title: '',
                        exportOptions: {
                            footer: false
                        },
                        customize: function(win) {
                            // Use base64 logo if available, otherwise use direct URL
                            var printLogo = logoBase64 || logoUrl;
                            
                            $(win.document.body)
                                .css('font-size', '12pt')
                                .prepend(
                                    '<div style="text-align:center; margin-bottom:25px; padding:20px 0;">' +
                                    '<img src="' + printLogo + '" style="width:60px; height:60px; border-radius:50%; margin-bottom:10px; display:block; margin-left:auto; margin-right:auto;" />' +
                                    '<h2 style="margin:0 0 5px 0; font-size:18px; font-weight:bold; color:#333;">Inventory Management Software</h2>' +
                                    '<p style="margin:0 0 8px 0; font-size:13px; color:#666;">and Smart Billing System </p>' +
                                    '<p style="margin:0 0 3px 0; font-size:11px; color:#888;">Location: Uttara, Dhaka</p>' +
                                    '<p style="margin:0 0 10px 0; font-size:11px; color:#888;">Email: inventory@test.com | Phone: 01710037283</p>' +
                                    '<hr style="border:none; border-top:1.5px solid #333; margin:10px 0 20px 0;">' +
                                    '</div>'
                                );
                            
                            $(win.document.body).find('table')
                                .addClass('display')
                                .css('font-size', '11pt');
                            
                            // Add total stock after table
                            var totalStock = '{{ number_format($totalStock) }}';
                            $(win.document.body).find('table').after(
                                '<table style="width:100%; margin-top:15px; border-collapse:collapse;">' +
                                '<tr style="background-color:#e9ecef; font-weight:bold;">' +
                                '<td style="text-align:right; padding:8px; border:1px solid #ddd;">Total Stock:</td>' +
                                '<td style="padding:8px; border:1px solid #ddd; width:100px;">' + totalStock + '</td>' +
                                '</tr>' +
                                '</table>'
                            );
                            
                            // Add print styles
                            $(win.document.head).append(
                                '<style>' +
                                '@media print {' +
                                '  body { margin: 20px; }' +
                                '  table { width: 100%; border-collapse: collapse; }' +
                                '  th, td { padding: 8px; border: 1px solid #ddd; }' +
                                '  th { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }' +
                                '  tfoot tr { background-color: #e9ecef !important; -webkit-print-color-adjust: exact; }' +
                                '  img { -webkit-print-color-adjust: exact; print-color-adjust: exact; }' +
                                '}' +
                                '</style>'
                            );
                        }
                    }
                ],
                pageLength: 25,
                order: [[2, 'asc']]
            });
        });
    </script>

    <style>
        @media print {
            .btn, form, .navbar, .sidebar { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
        
        tfoot tr {
            background-color: #e9ecef !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
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