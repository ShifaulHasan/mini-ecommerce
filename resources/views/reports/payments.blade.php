<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-credit-card"></i> Payment Report
            </h2>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
    </x-slot>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('reports.payments') }}" class="mb-4">
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
                                    <label class="form-label">Account</label>
                                    <select name="account_id" class="form-select">
                                        <option value="">All Accounts</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Transaction Type</label>
                                    <select name="transaction_type" class="form-select">
                                        <option value="">All Types</option>
                                        <option value="credit" {{ request('transaction_type') == 'credit' ? 'selected' : '' }}>Credit</option>
                                        <option value="debit" {{ request('transaction_type') == 'debit' ? 'selected' : '' }}>Debit</option>
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

                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 opacity-75">Total Credit</h6>
                                        <h3 class="card-title mb-0">৳{{ number_format($totals['total_credit'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 opacity-75">Total Debit</h6>
                                        <h3 class="card-title mb-0">৳{{ number_format($totals['total_debit'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 opacity-75">Net Balance</h6>
                                        <h3 class="card-title mb-0">৳{{ number_format($totals['net_balance'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Report Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
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
                                        <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $payment->transaction_type == 'credit' ? 'success' : 'danger' }}">
                                                <i class="bi bi-{{ $payment->transaction_type == 'credit' ? 'arrow-down-circle' : 'arrow-up-circle' }}"></i>
                                                {{ ucfirst($payment->transaction_type) }}
                                            </span>
                                        </td>
                                        <td class="text-end {{ $payment->transaction_type == 'credit' ? 'text-success' : 'text-danger' }}">
                                            <strong>{{ $payment->transaction_type == 'credit' ? '+' : '-' }}৳{{ number_format($payment->amount, 2) }}</strong>
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
                                <tfoot class="table-secondary">
                                    <tr class="fw-bold">
                                        <td colspan="7" class="text-end">Summary:</td>
                                        <td class="text-end">
                                            <div class="text-success">Credit: ৳{{ number_format($totals['total_credit'], 2) }}</div>
                                            <div class="text-danger">Debit: ৳{{ number_format($totals['total_debit'], 2) }}</div>
                                            <div class="text-info border-top pt-1">Balance: ৳{{ number_format($totals['net_balance'], 2) }}</div>
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

    <style>
        @media print {
            .btn, form, .navbar, .sidebar { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</x-app-layout>