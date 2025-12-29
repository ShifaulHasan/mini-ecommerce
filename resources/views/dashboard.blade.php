<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark">
                <i class="bi bi-speedometer2"></i> Dashboard
            </h2>
            <div class="text-muted">{{ now()->format('l, F d, Y') }}</div>
        </div>
    </x-slot>

    <div class="container-fluid">

        <!-- ======== SUMMARY CARDS ======== -->
        <div class="row">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 mb-3 bg-primary bg-gradient text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold opacity-75 mb-1">Total Sale</h6>
                                <h3 class="fw-bold mb-0">৳{{ number_format($totalSale, 2) }}</h3>
                            </div>
                            <div>
                                <i class="bi bi-cart-check fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 mb-3 bg-success bg-gradient text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold opacity-75 mb-1">Total Payment</h6>
                                <h3 class="fw-bold mb-0">৳{{ number_format($totalPayment, 2) }}</h3>
                            </div>
                            <div>
                                <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 mb-3 bg-danger bg-gradient text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold opacity-75 mb-1">Total Due</h6>
                                <h3 class="fw-bold mb-0">৳{{ number_format($totalDue, 2) }}</h3>
                            </div>
                            <div>
                                <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 mb-3 bg-info bg-gradient text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold opacity-75 mb-1">This Month Sale</h6>
                                <h3 class="fw-bold mb-0">৳{{ number_format($monthlySale, 2) }}</h3>
                            </div>
                            <div>
                                <i class="bi bi-calendar fs-1 opacity-50"></i>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======== CASH FLOW + DONUT CHART ======== -->
        <div class="row mt-3">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-graph-up"></i> Cash Flow (Last 12 Months)</span>
                        <span class="badge bg-primary">Payment Tracking</span>
                    </div>
                    <div class="card-body">
                        <canvas id="cashFlowChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-pie-chart"></i> Report</span>
                        <span class="badge bg-info">{{ now()->format('F Y') }}</span>
                    </div>
                    <div class="card-body">
                        <canvas id="donutChart" height="200"></canvas>
                        <div class="mt-3 text-center">
                            <small class="text-muted">Monthly Financial Overview</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======== PIE CHART + SALES OVERVIEW ======== -->
        <div class="row mt-3">
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-diagram-3"></i> Sales by Category</span>
                        <span class="badge bg-success">This Month</span>
                    </div>
                    <div class="card-body">
                        <canvas id="pieChart" height="200"></canvas>
                        <div class="mt-3 text-center">
                            <small class="text-muted">Top 5 Categories Performance</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-bar-chart"></i> Monthly Sales Overview</span>
                        <span class="badge bg-primary">Last 12 Months</span>
                    </div>
                    <div class="card-body">
                        <canvas id="barChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======== BEST SELLING PRODUCTS + RECENT TRANSACTIONS ======== -->
        <div class="row mt-3 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-trophy"></i> Best Selling Products</span>
                        <span class="badge bg-warning text-dark">This Month</span>
                    </div>
                    <div class="card-body">
                        @if(count($bestProducts) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="60">Rank</th>
                                            <th>Product Name</th>
                                            <th class="text-end">Quantity Sold</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bestProducts as $index => $item)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-{{ $index < 3 ? 'success' : 'secondary' }}">
                                                        #{{ $index + 1 }}
                                                    </span>
                                                </td>
                                                <td>{{ $item->name }}</td>
                                                <td class="text-end">
                                                    <strong class="text-primary">{{ number_format($item->qty) }}</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mb-0">No sales this month</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-clock-history"></i> Recent Transactions</span>
                        <a href="{{ route('reports.sales') }}" class="badge bg-primary text-decoration-none">
                            View All <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        @if(count($recent) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice</th>
                                            <th>Customer</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recent as $row)
                                            <tr>
                                                <td><small>{{ $row->date }}</small></td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $row->ref }}</span>
                                                </td>
                                                <td>{{ Str::limit($row->customer, 20) }}</td>
                                                <td class="text-end">
                                                    <strong class="text-success">{{ $row->total }}</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mb-0">No recent transactions</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- CHART JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Chart.js Global Configuration
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#6c757d';

        // ======= CASH FLOW CHART =======
        const ctx = document.getElementById('cashFlowChart');
        const months = {!! json_encode($months) !!};
        const paymentReceived = {!! json_encode($paymentReceived) !!};
        const paymentSent = {!! json_encode($paymentSent) !!};

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Payment Received',
                        data: paymentReceived,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Payment Sent',
                        data: paymentSent,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ৳' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '৳' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // ======= DONUT CHART =======
        const donut = document.getElementById('donutChart');
        const donutData = {!! json_encode($donutData) !!};

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
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ৳' + context.parsed.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // ======= PIE CHART =======
        const pie = document.getElementById('pieChart');
        const categoryLabels = {!! json_encode($categoryLabels) !!};
        const categoryData = {!! json_encode($categoryData) !!};

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
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)'
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 10
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                }
            }
        });

        // ======= BAR CHART =======
        const bar = document.getElementById('barChart');
        const monthlySalesData = {!! json_encode($monthlySales) !!};

        new Chart(bar, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Monthly Sales',
                    data: monthlySalesData,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 0,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return 'Sales: ৳' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '৳' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>

</x-app-layout>