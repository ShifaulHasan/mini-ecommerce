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
                                    @forelse($products as $index => $product)
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
            $('#productsTable').DataTable({
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
                order: [[2, 'asc']]
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

</div>

</x-app-layout>
