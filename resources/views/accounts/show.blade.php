<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                Account Details - {{ $account->name }}
            </h2>
            <div>
                <a href="{{ route('accounts.edit', $account->id) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('accounts.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .account-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-item {
            padding: 15px;
            background: #f9fafb;
            border-radius: 6px;
            border-left: 4px solid #6366f1;
        }
        .info-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 16px;
            color: #1f2937;
            font-weight: 600;
        }
        .balance-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 30px;
        }
        .balance-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        .balance-amount {
            font-size: 36px;
            font-weight: 700;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 700;
        }
        .stat-value.positive {
            color: #10b981;
        }
        .stat-value.negative {
            color: #ef4444;
        }
        .stat-value.neutral {
            color: #6366f1;
        }
        .transactions-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .table-header {
            background: #f9fafb;
            padding: 15px 20px;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #1f2937;
        }
        .table {
            width: 100%;
            margin: 0;
        }
        .table th {
            background: #f9fafb;
            padding: 12px 15px;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            border-bottom: 2px solid #e5e7eb;
        }
        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }
        .table tbody tr:hover {
            background: #f9fafb;
        }
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge.active {
            background: #d1fae5;
            color: #065f46;
        }
        .status-badge.inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        .no-transactions {
            padding: 60px 20px;
            text-align: center;
            color: #9ca3af;
        }
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-control {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }
        .btn-filter {
            background: #6366f1;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }
        .btn-filter:hover {
            background: #4f46e5;
        }
    </style>

    <div class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Account Information Card -->
            <div class="account-card">
                <h3 style="margin-bottom: 20px; font-size: 18px; font-weight: 700;">Account Information</h3>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Account Number</div>
                        <div class="info-value">{{ $account->account_no }}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Account Name</div>
                        <div class="info-value">{{ $account->name }}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Branch</div>
                        <div class="info-value">{{ $account->branch ?? 'N/A' }}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Swift Code</div>
                        <div class="info-value">{{ $account->swift_code ?? 'N/A' }}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="status-badge {{ $account->status }}">
                                {{ ucfirst($account->status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Default Account</div>
                        <div class="info-value">
                            {{ $account->is_default ? 'Yes' : 'No' }}
                        </div>
                    </div>
                </div>

                @if($account->note)
                <div style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 6px; border-left: 4px solid #f59e0b;">
                    <strong style="color: #92400e;">Note:</strong> {{ $account->note }}
                </div>
                @endif
            </div>

            <!-- Balance Card -->
            <div class="balance-card">
                <div class="balance-label">Current Balance</div>
                <div class="balance-amount">৳{{ number_format($account->current_balance, 2) }}</div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Opening Balance</div>
                    <div class="stat-value neutral">৳{{ number_format($account->initial_balance, 2) }}</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-label">Total Money In</div>
                    <div class="stat-value positive">৳{{ number_format($totalMoneyIn, 2) }}</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-label">Total Money Out</div>
                    <div class="stat-value negative">৳{{ number_format($totalMoneyOut, 2) }}</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-label">Net Balance</div>
                    <div class="stat-value {{ $netBalance >= 0 ? 'positive' : 'negative' }}">
                        ৳{{ number_format($netBalance, 2) }}
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <form method="GET" action="{{ route('accounts.show', $account->id) }}">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" 
                                value="{{ request('start_date') }}">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" 
                                value="{{ request('end_date') }}">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Transaction Type</label>
                            <select name="type" class="form-control">
                                <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All</option>
                                <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Money In</option>
                                <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Money Out</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn-filter">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Transactions Table -->
            <div class="transactions-table">
                <div class="table-header">
                    Transaction History
                </div>

                @if($transactions->count() > 0)
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th>Type</th>
                            <th class="text-end">Money In</th>
                            <th class="text-end">Money Out</th>
                            <th class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}</td>
                            <td>{{ $transaction->description }}</td>
                            <td>
                                <span style="font-family: monospace; font-size: 12px; color: #6b7280;">
                                    {{ $transaction->reference_type }}
                                </span>
                            </td>
                            <td>
                                @if($transaction->transaction_type == 'credit')
                                    <span class="badge badge-success">Money In</span>
                                @else
                                    <span class="badge badge-danger">Money Out</span>
                                @endif
                            </td>
                            <td class="text-end" style="color: #10b981; font-weight: 600;">
                                @if($transaction->transaction_type == 'credit')
                                    +৳{{ number_format($transaction->amount, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end" style="color: #ef4444; font-weight: 600;">
                                @if($transaction->transaction_type == 'debit')
                                    -৳{{ number_format($transaction->amount, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end" style="font-weight: 700;">
                                ৳{{ number_format($transaction->balance_after, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div style="padding: 20px;">
                    {{ $transactions->links() }}
                </div>
                @else
                <div class="no-transactions">
                    <i class="bi bi-inbox" style="font-size: 48px; margin-bottom: 10px;"></i>
                    <p>No transactions found for this account.</p>
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>