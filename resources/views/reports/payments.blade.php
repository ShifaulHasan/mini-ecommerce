<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-credit-card"></i> Payment Report
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
                        <form method="GET" action="{{ route('reports.payments') }}" class="mb-4">
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
                                    <label class="form-label">Account</label>
                                    <select name="account_id" class="form-select">
                                        <option value="">All Accounts</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Transaction Type</label>
                                    <select name="transaction_type" class="form-select">
                                        <option value="">All Types</option>
                                        <option value="credit" {{ request('transaction_type') == 'credit' ? 'selected' : '' }}>
                                            Credit
                                        </option>
                                        <option value="debit" {{ request('transaction_type') == 'debit' ? 'selected' : '' }}>
                                            Debit
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-funnel"></i> Filter
                                    </button>
                                    <a href="{{ route('reports.payments') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        {{-- ================= Summary Cards ================= --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="opacity-75">Total Credit</h6>
                                        <h3 class="mb-0">৳{{ number_format($totals['total_credit'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h6 class="opacity-75">Total Debit</h6>
                                        <h3 class="mb-0">৳{{ number_format($totals['total_debit'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="opacity-75">Net Balance</h6>
                                        <h3 class="mb-0">৳{{ number_format($totals['net_balance'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= Report Table ================= --}}
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle"
                                   id="paymentReportTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>SL</th>
                                        <th>Date</th>
                                        <th>Account</th>
                                        <th>Reference Type</th>
                                        <th>Description</th>
                                        <th>Payment Method</th>
                                        <th class="text-center">Type</th>
                                        <th class="text-end">Amount</th>
                                        <th>Created By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $index => $payment)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ date('d-m-Y', strtotime($payment->transaction_date)) }}</td>
                                            <td>{{ $payment->account_name }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($payment->reference_type ?? 'N/A') }}
                                                </span>
                                            </td>
                                            <td>{{ $payment->description }}</td>
                                            <td>
                                                @if($payment->actual_payment_method)
                                                    <span class="badge bg-primary">
                                                        {{ ucfirst(str_replace('_', ' ', $payment->actual_payment_method)) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $payment->transaction_type == 'credit' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($payment->transaction_type) }}
                                                </span>
                                            </td>
                                            <td class="text-end {{ $payment->transaction_type == 'credit' ? 'text-success' : 'text-danger' }}">
                                                <strong>
                                                    {{ $payment->transaction_type == 'credit' ? '+' : '-' }}
                                                    ৳{{ number_format($payment->amount, 2) }}
                                                </strong>
                                            </td>
                                            <td>{{ $payment->created_by_name ?? 'System' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-1"></i>
                                                <p class="mb-0">No payments found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                @if(count($payments) > 0)
                                <tfoot class="table-secondary fw-bold">
                                    <tr>
                                        <td colspan="7" class="text-end">Summary:</td>
                                        <td class="text-end">
                                            <div class="text-success">Credit: ৳{{ number_format($totals['total_credit'], 2) }}</div>
                                            <div class="text-danger">Debit: ৳{{ number_format($totals['total_debit'], 2) }}</div>
                                            <div class="text-info border-top pt-1">
                                                Balance: ৳{{ number_format($totals['net_balance'], 2) }}
                                            </div>
                                        </td>
                                        <td></td>
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

            $('#paymentReportTable').DataTable({
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
                pageLength: 25,
                order: [[1, 'desc']],
                columnDefs: [
                    {
                        targets: 0,
                        render: function (data, type, row, meta) {
                            if (type === 'display' || type === 'filter') {
                                return meta.settings._iDisplayStart + meta.row + 1;
                            }
                            return data;
                        }
                    }
                ]
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