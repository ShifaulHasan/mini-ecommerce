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

        <!-- ======== CASH FLOW + DONUT CHART ======== -->
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

        <!-- ======== PIE CHART + SALES OVERVIEW ======== -->
        <div class="row mt-4">
            
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header fw-bold">Sales by Category</div>
                    <div class="card-body">
                        <canvas id="pieChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header fw-bold">Monthly Sales Overview</div>
                    <div class="card-body">
                        <canvas id="barChart" height="130"></canvas>
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

    </div>

    <!-- CHART JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // ======= CASH FLOW CHART =======
        const ctx = document.getElementById('cashFlowChart');
        const months = {!! json_encode($months ?? []) !!};
        const paymentReceived = {!! json_encode($paymentReceived ?? []) !!};
        const paymentSent = {!! json_encode($paymentSent ?? []) !!};

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Payment Received',
                        data: paymentReceived,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 2,
                        tension: 0.4
                    },
                    {
                        label: 'Payment Sent',
                        data: paymentSent,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderWidth: 2,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // ======= DONUT CHART =======
        const donut = document.getElementById('donutChart');
        const donutData = {!! json_encode($donutData ?? [0,0,0]) !!};

        new Chart(donut, {
            type: 'doughnut',
            data: {
                labels: ['Purchase', 'Revenue', 'Expense'],
                datasets: [{
                    data: donutData,
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // ======= PIE CHART (NEW) =======
        const pie = document.getElementById('pieChart');
        const categoryLabels = {!! json_encode($categoryLabels ?? ['Electronics', 'Clothing', 'Food', 'Others']) !!};
        const categoryData = {!! json_encode($categoryData ?? [30, 25, 20, 25]) !!};

        new Chart(pie, {
            type: 'pie',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryData,
                    backgroundColor: [
                        'rgb(54, 162, 235)',
                        'rgb(255, 99, 132)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)'
                    ],
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed + '%';
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // ======= BAR CHART (BONUS) =======
        const bar = document.getElementById('barChart');
        const monthlySalesData = {!! json_encode($monthlySales ?? []) !!};

        new Chart(bar, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Monthly Sales',
                    data: monthlySalesData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

</x-app-layout>