<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Account Statement') }}
        </h2>
    </x-slot>

    <style>
        .statement-container {
            background: #f5f5f5;
            min-height: 100vh;
            padding: 20px;
        }
        .statement-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .filter-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .filter-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 15px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        .required {
            color: #ef4444;
            margin-left: 2px;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            font-size: 14px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            transition: border-color 0.15s, box-shadow 0.15s;
            background: white;
        }
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .date-range-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .date-separator {
            color: #6b7280;
            font-size: 14px;
        }
        .btn-submit {
            padding: 9px 24px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            background: #8b5cf6;
            color: white;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-submit:hover {
            background: #7c3aed;
        }
        .btn-reset {
            padding: 9px 24px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            background: white;
            color: #374151;
            font-weight: 500;
            transition: background 0.2s;
            margin-left: 10px;
        }
        .btn-reset:hover {
            background: #f3f4f6;
        }
        .statement-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        .statement-table thead {
            background: #1e40af;
        }
        .statement-table th {
            padding: 14px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: white;
            border: 1px solid #1e3a8a;
        }
        .statement-table td {
            padding: 12px;
            font-size: 14px;
            color: #374151;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        .statement-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        .statement-table tbody tr:hover {
            background: #f0f9ff;
        }
        .money-in {
            color: #059669;
            font-weight: 600;
            font-size: 15px;
        }
        .money-out {
            color: #dc2626;
            font-weight: 600;
            font-size: 15px;
        }
        .balance-amount {
            font-weight: 700;
            color: #1e40af;
            font-size: 15px;
        }
        .transaction-desc {
            color: #374151;
            line-height: 1.5;
        }
        .transaction-ref {
            font-size: 12px;
            color: #6b7280;
            font-style: italic;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-size: 14px;
        }
        .summary-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 20px;
            padding: 20px;
            background: #f0f9ff;
            border-radius: 6px;
            border: 2px solid #3b82f6;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .summary-value {
            font-size: 24px;
            font-weight: 700;
        }
        .summary-value.inflow {
            color: #059669;
        }
        .summary-value.outflow {
            color: #dc2626;
        }
        .summary-value.balance {
            color: #1e40af;
        }
        .export-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .btn-export {
            padding: 8px 16px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            background: white;
            color: #374151;
            transition: all 0.2s;
        }
        .btn-export:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }
        .btn-export.pdf {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }
        .btn-export.pdf:hover {
            background: #dc2626;
        }
        .btn-export.excel {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        .btn-export.excel:hover {
            background: #059669;
        }
        .btn-export.print {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        .btn-export.print:hover {
            background: #2563eb;
        }
        .account-header {
            margin-bottom: 25px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            color: white;
        }
        .account-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .account-details {
            font-size: 14px;
            opacity: 0.95;
        }
        .opening-closing-row {
            background: #fef3c7 !important;
            font-weight: 700;
            font-size: 15px;
        }
        .opening-closing-row td {
            border: 2px solid #f59e0b !important;
        }
        .col-center {
            text-align: center;
        }
        .col-right {
            text-align: right;
        }
        .flow-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .flow-badge.inflow {
            background: #d1fae5;
            color: #065f46;
        }
        .flow-badge.outflow {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="statement-container">
                
                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="filter-title">Filter Account Statement</div>
                    <p style="font-size: 13px; color: #6b7280; font-style: italic; margin-bottom: 20px;">
                        The field labels marked with * are required input fields.
                    </p>

                    <form action="{{ route('accounting.statement') }}" method="GET" id="statementForm">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                            
                            <!-- Account Dropdown -->
                            <div class="form-group">
                                <label class="form-label">
                                    Account <span class="required">*</span>
                                </label>
                                <select name="account_id" class="form-control" required>
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" 
                                            {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} [{{ $account->account_no }}]
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Type Dropdown -->
                            <div class="form-group">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-control">
                                    <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Transactions</option>
                                    <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Money In (Credit)</option>
                                    <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Money Out (Debit)</option>
                                </select>
                            </div>

                            <!-- Quick Range -->
                            <div class="form-group">
                                <label class="form-label">Quick Range</label>
                                <select name="quick_range" class="form-control" id="quickRange">
                                    <option value="">Custom</option>
                                    <option value="today" {{ request('quick_range') == 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="yesterday" {{ request('quick_range') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                    <option value="last_7_days" {{ request('quick_range') == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                                    <option value="last_30_days" {{ request('quick_range') == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                                    <option value="last_90_days" {{ request('quick_range') == 'last_90_days' ? 'selected' : '' }}>Last 90 Days</option>
                                    <option value="this_month" {{ request('quick_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                                    <option value="last_month" {{ request('quick_range') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                                    <option value="this_year" {{ request('quick_range') == 'this_year' ? 'selected' : '' }}>This Year</option>
                                    <option value="last_year" {{ request('quick_range') == 'last_year' ? 'selected' : '' }}>Last Year</option>
                                    <option value="all_time" {{ request('quick_range') == 'all_time' ? 'selected' : '' }}>All Time</option>
                                </select>
                            </div>

                            <!-- Date Range -->
                            <div class="form-group">
                                <label class="form-label">Choose Your Date</label>
                                <div class="date-range-wrapper">
                                    <input type="date" 
                                           name="start_date" 
                                           class="form-control" 
                                           id="startDate"
                                           value="{{ request('start_date') }}"
                                           placeholder="Start Date">
                                    <span class="date-separator">to</span>
                                    <input type="date" 
                                           name="end_date" 
                                           class="form-control" 
                                           id="endDate"
                                           value="{{ request('end_date') }}"
                                           placeholder="End Date">
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 20px; display: flex; justify-content: flex-start;">
                            <button type="submit" class="btn-submit">Generate Statement</button>
                            <button type="button" class="btn-reset" onclick="resetForm()">Reset</button>
                        </div>
                    </form>
                </div>

                @if(isset($transactions))
                <!-- Results Section -->
                <div class="statement-card">
                    
                    <!-- Export Buttons -->
                    <div class="export-buttons">
                        <!-- <button class="btn-export pdf" onclick="exportPDF()">📄 Export PDF</button> -->
                        <button class="btn-export excel" onclick="exportExcel()">📊 Export Excel</button>
                        <button class="btn-export print" onclick="window.print()">🖨️ Print</button>
                    </div>

                    <!-- Account Info Header -->
                    @if(isset($selectedAccount))
                    <div class="account-header">
                        <div class="account-name">{{ $selectedAccount->name }}</div>
                        <div class="account-details">
                            <strong>Account No:</strong> {{ $selectedAccount->account_no }}
                            @if($selectedAccount->branch)
                                | <strong>Branch:</strong> {{ $selectedAccount->branch }}
                            @endif
                            @if($selectedAccount->swift_code)
                                | <strong>SWIFT:</strong> {{ $selectedAccount->swift_code }}
                            @endif
                        </div>
                        @if(request('start_date') && request('end_date'))
                        <div class="account-details" style="margin-top: 8px;">
                            <strong>Statement Period:</strong> {{ date('d M Y', strtotime(request('start_date'))) }} to {{ date('d M Y', strtotime(request('end_date'))) }}
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Transactions Table -->
                    <table class="statement-table">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Date</th>
                                <th style="width: 40%;">Transaction Description</th>
                                <th style="width: 120px;" class="col-center">Transaction Type</th>
                                <th style="width: 130px;" class="col-right">Money In<br>(Credit)</th>
                                <th style="width: 130px;" class="col-right">Money Out<br>(Debit)</th>
                                <th style="width: 150px;" class="col-right">Running Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $runningBalance = $openingBalance ?? 0;
                            @endphp

                            <!-- Opening Balance Row -->
                            @if(isset($openingBalance))
                            <tr class="opening-closing-row">
                                <td colspan="3"><strong>OPENING BALANCE</strong></td>
                                <td class="col-right">-</td>
                                <td class="col-right">-</td>
                                <td class="col-right balance-amount">{{ number_format($openingBalance, 2) }}</td>
                            </tr>
                            @endif

                            <!-- Transaction Rows -->
                            <!-- Transaction Rows Section - Replace this in your blade file -->
@forelse($transactions as $transaction)
    @php
        // 🔥 FIXED: Use transaction_type instead of type
        if ($transaction->transaction_type == 'credit') {
            $runningBalance += $transaction->amount;
            $moneyIn = $transaction->amount;
            $moneyOut = 0;
            $flowType = 'inflow';
            $flowLabel = 'Money In';
        } else {
            $runningBalance -= $transaction->amount;
            $moneyIn = 0;
            $moneyOut = $transaction->amount;
            $flowType = 'outflow';
            $flowLabel = 'Money Out';
        }
    @endphp
    <tr>
        <!-- 🔥 FIXED: Use transaction_date instead of date -->
        <td><strong>{{ date('d M Y', strtotime($transaction->transaction_date)) }}</strong></td>
        <td>
            <div class="transaction-desc">
                <strong>{{ $transaction->description }}</strong>
            </div>
            <!-- 🔥 FIXED: Show reference_type and reference_id -->
            @if($transaction->reference_type && $transaction->reference_id)
                <div class="transaction-ref">
                    Ref: {{ strtoupper($transaction->reference_type) }}-{{ $transaction->reference_id }}
                </div>
            @endif
        </td>
        <td class="col-center">
            <span class="flow-badge {{ $flowType }}">{{ $flowLabel }}</span>
        </td>
        <td class="col-right">
            @if($moneyIn > 0)
                <span class="money-in">+{{ number_format($moneyIn, 2) }}</span>
            @else
                <span style="color: #9ca3af;">-</span>
            @endif
        </td>
        <td class="col-right">
            @if($moneyOut > 0)
                <span class="money-out">-{{ number_format($moneyOut, 2) }}</span>
            @else
                <span style="color: #9ca3af;">-</span>
            @endif
        </td>
        <td class="col-right balance-amount">
            {{ number_format($runningBalance, 2) }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="no-data">
            No transactions found for the selected criteria.<br>
            Please adjust your filters and try again.
        </td>
    </tr>
@endforelse

                            <!-- Closing Balance Row -->
                            @if($transactions->count() > 0)
                            <tr class="opening-closing-row">
                                <td colspan="3"><strong>CLOSING BALANCE</strong></td>
                                <td class="col-right money-in"><strong>{{ number_format($totalCredit ?? 0, 2) }}</strong></td>
                                <td class="col-right money-out"><strong>{{ number_format($totalDebit ?? 0, 2) }}</strong></td>
                                <td class="col-right balance-amount">{{ number_format($runningBalance, 2) }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>

                    <!-- Summary Section -->
                    @if($transactions->count() > 0)
                    <div class="summary-section">
                        <div class="summary-item">
                            <div class="summary-label">Total Money In (Credit)</div>
                            <div class="summary-value inflow">+{{ number_format($totalCredit ?? 0, 2) }}</div>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">Total Inflow to Account</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Total Money Out (Debit)</div>
                            <div class="summary-value outflow">-{{ number_format($totalDebit ?? 0, 2) }}</div>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">Total Outflow from Account</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Net Balance</div>
                            <div class="summary-value balance">{{ number_format($runningBalance, 2) }}</div>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">Available Balance</div>
                        </div>
                    </div>

                    <!-- Transaction Summary -->
                    <div style="margin-top: 20px; padding: 15px; background: #f9fafb; border-left: 4px solid #3b82f6; border-radius: 4px;">
                        <p style="margin: 0; font-size: 14px; color: #374151;">
                            <strong>Summary:</strong> This statement shows <strong>{{ $transactions->count() }}</strong> transaction(s) 
                            with <strong class="money-in">{{ number_format($totalCredit ?? 0, 2) }}</strong> received into the account 
                            and <strong class="money-out">{{ number_format($totalDebit ?? 0, 2) }}</strong> paid out from the account.
                            The current balance is <strong class="balance-amount">{{ number_format($runningBalance, 2) }}</strong>.
                        </p>
                    </div>
                    @endif
                </div>
                @endif

            </div>
        </div>
    </div>

    <script>
        // Quick range change handler
        document.getElementById('quickRange').addEventListener('change', function() {
            const value = this.value;
            const today = new Date();
            let startDate, endDate;

            if (!value) {
                return;
            }

            endDate = today.toISOString().split('T')[0];

            switch(value) {
                case 'today':
                    startDate = endDate;
                    break;
                case 'yesterday':
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    startDate = endDate = yesterday.toISOString().split('T')[0];
                    break;
                case 'last_7_days':
                    const last7 = new Date(today);
                    last7.setDate(last7.getDate() - 7);
                    startDate = last7.toISOString().split('T')[0];
                    break;
                case 'last_30_days':
                    const last30 = new Date(today);
                    last30.setDate(last30.getDate() - 30);
                    startDate = last30.toISOString().split('T')[0];
                    break;
                case 'last_90_days':
                    const last90 = new Date(today);
                    last90.setDate(last90.getDate() - 90);
                    startDate = last90.toISOString().split('T')[0];
                    break;
                case 'this_month':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                    break;
                case 'last_month':
                    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    startDate = lastMonth.toISOString().split('T')[0];
                    endDate = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().split('T')[0];
                    break;
                case 'this_year':
                    startDate = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
                    break;
                case 'last_year':
                    startDate = new Date(today.getFullYear() - 1, 0, 1).toISOString().split('T')[0];
                    endDate = new Date(today.getFullYear() - 1, 11, 31).toISOString().split('T')[0];
                    break;
                case 'all_time':
                    startDate = '';
                    endDate = '';
                    break;
            }

            document.getElementById('startDate').value = startDate;
            document.getElementById('endDate').value = endDate;
        });

        function resetForm() {
            document.getElementById('statementForm').reset();
            window.location.href = '{{ route('accounting.statement') }}';
        }

        // function exportPDF() {
        //     const form = document.getElementById('statementForm');
        //     const formData = new FormData(form);
        //     const params = new URLSearchParams(formData);
        //     window.location.href = '{{ route('accounting.statement') }}?' + params.toString() + '&export=pdf';
        // }

        function exportExcel() {
            const form = document.getElementById('statementForm');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            window.location.href = '{{ route('accounting.statement') }}?' + params.toString() + '&export=excel';
        }

        // Print styles
        const style = document.createElement('style');
        style.textContent = `
            @media print {
                body * {
                    visibility: hidden;
                }
                .statement-card, .statement-card * {
                    visibility: visible;
                }
                .statement-card {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                }
                .export-buttons {
                    display: none !important;
                }
                .filter-section {
                    display: none !important;
                }
                .statement-table {
                    page-break-inside: auto;
                }
                .statement-table tr {
                    page-break-inside: avoid;
                    page-break-after: auto;
                }
            }
        `;
        document.head.appendChild(style);
    </script>


    </div> 

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