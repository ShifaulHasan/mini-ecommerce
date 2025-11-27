<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">Dashboard</h2>
    </x-slot>

    <div class="container-fluid">

        <!-- ======== SUMMARY CARDS ======== -->
        <div class="row">

            <div class="col-md-3">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body text-center">
                        <h6 class="fw-bold text-muted">Total Sale</h6>
                        <h3 class="fw-bold text-primary">{{ number_format($totalSale ?? 0,2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body text-center">
                        <h6 class="fw-bold text-muted">Total Payment</h6>
                        <h3 class="fw-bold text-success">{{ number_format($totalPayment ?? 0,2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body text-center">
                        <h6 class="fw-bold text-muted">Total Due</h6>
                        <h3 class="fw-bold text-danger">{{ number_format($totalDue ?? 0,2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body text-center">
                        <h6 class="fw-bold text-muted">This Month Sale</h6>
                        <h3 class="fw-bold text-info">{{ number_format($monthlySale ?? 0,2) }}</h3>
                    </div>
                </div>
            </div>

        </div>

        <!-- ======== CASH FLOW + PIE CHART ======== -->
        <div class="row mt-4">

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header fw-bold">Cash Flow</div>
                    <div class="card-body">
                        <canvas id="cashFlowChart" height="130"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header fw-bold">Report ({{ now()->format('F Y') }})</div>
                    <div class="card-body">
                        <canvas id="donutChart" height="200"></canvas>
                    </div>
                </div>
            </div>

        </div>

        <!-- ======== BEST SELL PRODUCT ======== -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header fw-bold">Best Sale Product (This Month)</div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bestProducts ?? [] as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->qty }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header fw-bold">Recent Transactions</div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Ref</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent ?? [] as $row)
                                    <tr>
                                        <td>{{ $row->date }}</td>
                                        <td>{{ $row->ref }}</td>
                                        <td>{{ $row->customer }}</td>
                                        <td>{{ $row->total }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- container end -->

    <!-- CHART JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // ======= CASH FLOW CHART =======
        const ctx = document.getElementById('cashFlowChart');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($months ?? []),
                datasets: [
                    {
                        label: 'Payment Received',
                        data: @json($paymentReceived ?? []),
                        borderWidth: 2
                    },
                    {
                        label: 'Payment Sent',
                        data: @json($paymentSent ?? []),
                        borderWidth: 2
                    }
                ]
            }
        });

        // ======= DONUT CHART =======
        const donut = document.getElementById('donutChart');
        new Chart(donut, {
            type: 'doughnut',
            data: {
                labels: ['Purchase', 'Revenue', 'Expense'],
                datasets: [{
                    data: @json($donutData ?? [0,0,0])
                }]
            }
        });
    </script>

</x-app-layout>
