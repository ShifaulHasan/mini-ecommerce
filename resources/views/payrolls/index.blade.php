<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">Payroll Management</h2>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <!-- Success Message -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Header with Add Button -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <label for="recordsPerPage" class="me-2">Records per page:</label>
                        <select id="recordsPerPage" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPayrollModal">
                            <i class="bi bi-plus-circle"></i> Add Payroll
                        </button>
                    </div>
                </div>

                <!-- Search Box -->
                <div class="mb-3">
                    <input type="text" id="searchBox" class="form-control" placeholder="Search payrolls...">
                </div>

                <!-- Payroll Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Employee</th>
                                <th>Account</th>
                                <th>Amount</th>
                                <th>Paid This Month</th>
                                <th>Method</th>
                                <th>Status</th>
                                @can('approve payroll')
                                <th>Action</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payrolls as $payroll)
                                <tr>
                                    <td><input type="checkbox" class="payroll-checkbox"></td>
                                    <td>{{ $payroll->payment_date->format('d-m-Y') }}</td>
                                    <td>{{ $payroll->payroll_reference }}</td>
                                    <td>{{ $payroll->employee->name ?? 'N/A' }}</td>
                                    <td>{{ $payroll->account->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($payroll->amount, 2) }}</td>
                                    <td>
                                        @php
                                            $employeeSalary = $payroll->employee->salary ?? 0;
                                            $isExceeded = $employeeSalary > 0 && $payroll->paid_amount > $employeeSalary;
                                        @endphp
                                        <span class="badge {{ $isExceeded ? 'bg-danger' : 'bg-info' }}" 
                                              title="{{ $isExceeded ? 'Exceeds monthly salary!' : 'Within salary limit' }}">
                                            {{ number_format($payroll->paid_amount, 2) }}
                                        </span>
                                        @if($employeeSalary > 0)
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">
                                                Salary: {{ number_format($employeeSalary, 2) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-info">{{ $payroll->payment_method }}</span></td>
                                    <td>
                                        @if($payroll->is_approve == 0)
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @else               
                                            <span class="badge bg-success">Approved</span> 
                                        @endif     
                                    </td>
                                    @can('approve payroll')
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($payroll->is_approve == 0)
                                                <form action="{{ route('payrolls.approve', $payroll) }}" 
                                                      method="POST" 
                                                      class="d-inline approve-form"
                                                      data-employee-id="{{ $payroll->employee_id }}"
                                                      data-employee-name="{{ $payroll->employee->name ?? 'N/A' }}"
                                                      data-employee-salary="{{ $payroll->employee->salary ?? 0 }}"
                                                      data-amount="{{ $payroll->amount }}"
                                                      data-payment-date="{{ $payroll->payment_date->format('Y-m-d') }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Approve & Pay">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <a href="{{ route('payrolls.show', $payroll) }}" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            @if($payroll->is_approve == 0)
                                                <a href="{{ route('payrolls.edit', $payroll) }}" 
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif
                                            
                                            <form action="{{ route('payrolls.destroy', $payroll) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this payroll?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <p class="text-muted">No payroll records found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end">Total:</td>
                                <td colspan="5">{{ number_format($totalAmount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing {{ $payrolls->firstItem() ?? 0 }} to {{ $payrolls->lastItem() ?? 0 }} 
                        of {{ $payrolls->total() }} entries
                    </div>
                    <div>
                        {{ $payrolls->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Payroll Modal -->
    <div class="modal fade" id="addPayrollModal" tabindex="-1" aria-labelledby="addPayrollModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPayrollModalLabel">Add Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('payrolls.store') }}" method="POST" id="payrollForm">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            <small>The field labels marked with * are required input fields.</small>
                        </p>

                        <!-- Alert for salary limit -->
                        <div id="salaryAlert" class="alert alert-warning d-none" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong>Warning!</strong>
                            <div id="salaryAlertMessage"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Date -->
                                <div class="mb-3">
                                    <label for="payment_date" class="form-label">Date *</label>
                                    <input type="date" 
                                           class="form-control @error('payment_date') is-invalid @enderror" 
                                           id="payment_date" 
                                           name="payment_date" 
                                           value="{{ old('payment_date', date('Y-m-d')) }}" 
                                           required>
                                    @error('payment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Account -->
                                <div class="mb-3">
                                    <label for="account_id" class="form-label">Account *</label>
                                    <select class="form-select @error('account_id') is-invalid @enderror" 
                                            id="account_id" 
                                            name="account_id" 
                                            required>
                                        <option value="">Select Account...</option>
                                        @foreach(\App\Models\Account::active()->get() as $account)
                                            <option value="{{ $account->id }}" 
                                                    {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }} ({{ $account->account_no }}) - Balance: {{ number_format($account->current_balance, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Method -->
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Method *</label>
                                    <select class="form-select @error('payment_method') is-invalid @enderror" 
                                            id="payment_method" 
                                            name="payment_method" 
                                            required>
                                        <option value="">Select Method...</option>
                                        @foreach(\App\Models\Payroll::getPaymentMethods() as $method)
                                            <option value="{{ $method }}" 
                                                    {{ old('payment_method') == $method ? 'selected' : '' }}>
                                                {{ $method }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Employee -->
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">Employee *</label>
                                    <select class="form-select @error('employee_id') is-invalid @enderror" 
                                            id="employee_id" 
                                            name="employee_id" 
                                            required>
                                        <option value="">Select Employee...</option>
                                        @foreach(\App\Models\Employee::orderBy('name')->get() as $employee)
                                            <option value="{{ $employee->id }}" 
                                                    data-salary="{{ $employee->salary ?? 0 }}"
                                                    {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }} ({{ $employee->staff_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">
                                        <strong>Monthly Salary:</strong> <span id="employeeSalary" class="text-primary fw-bold">0.00</span> BDT
                                    </small>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Amount -->
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount *</label>
                                    <input type="number" 
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" 
                                           name="amount" 
                                           step="0.01" 
                                           min="0.01"
                                           value="{{ old('amount') }}" 
                                           required>
                                    <small class="text-muted">
                                        <strong>Already Paid This Month:</strong> <span id="paidThisMonth" class="text-info fw-bold">0.00</span> BDT
                                    </small>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Note -->
                                <div class="mb-3">
                                    <label for="note" class="form-label">Note</label>
                                    <textarea class="form-control @error('note') is-invalid @enderror" 
                                              id="note" 
                                              name="note" 
                                              rows="3">{{ old('note') }}</textarea>
                                    @error('note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-check-circle"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .btn-group .btn {
            border-radius: 0;
        }
        .btn-group .btn:first-child {
            border-top-left-radius: 0.25rem;
            border-bottom-left-radius: 0.25rem;
        }
        .btn-group .btn:last-child {
            border-top-right-radius: 0.25rem;
            border-bottom-right-radius: 0.25rem;
        }
        #salaryAlert {
            border-left: 4px solid #ffc107;
        }
        #salaryAlertMessage {
            margin-top: 8px;
            font-size: 0.95rem;
        }
    </style>

    <script>
        // Laravel routes for JavaScript
        const PAYROLL_ROUTES = {
            getPaidAmount: "{{ route('payrolls.getPaidAmount') }}"
        };
        
        document.addEventListener('DOMContentLoaded', function() {
            const employeeSelect = document.getElementById('employee_id');
            const amountInput = document.getElementById('amount');
            const paymentDateInput = document.getElementById('payment_date');
            const salaryAlert = document.getElementById('salaryAlert');
            const salaryAlertMessage = document.getElementById('salaryAlertMessage');
            const employeeSalarySpan = document.getElementById('employeeSalary');
            const paidThisMonthSpan = document.getElementById('paidThisMonth');
            const payrollForm = document.getElementById('payrollForm');
            const submitBtn = document.getElementById('submitBtn');

            let employeeSalary = 0;
            let paidThisMonth = 0;
            let isLoading = false;

            // ===== APPROVE FORM VALIDATION =====
            const approveForms = document.querySelectorAll('.approve-form');
            approveForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const employeeId = this.dataset.employeeId;
                    const employeeName = this.dataset.employeeName;
                    const employeeSalary = parseFloat(this.dataset.employeeSalary) || 0;
                    const amount = parseFloat(this.dataset.amount) || 0;
                    const paymentDate = this.dataset.paymentDate;
                    
                    // Fetch paid amount for this employee this month
                    const url = `${PAYROLL_ROUTES.getPaidAmount}?employee_id=${employeeId}&payment_date=${paymentDate}`;
                    
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            const paidThisMonth = parseFloat(data.paid_amount) || 0;
                            const totalAfterApproval = paidThisMonth + amount;
                            
                            let confirmMessage = '';
                            
                            // Check if exceeds salary
                            if (employeeSalary > 0 && totalAfterApproval > employeeSalary) {
                                const excess = totalAfterApproval - employeeSalary;
                                confirmMessage = 
                                    `⚠️ WARNING: This payment will EXCEED ${employeeName}'s monthly salary!\n\n` +
                                    `📊 Payment Summary:\n` +
                                    `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n` +
                                    `Employee:           ${employeeName}\n` +
                                    `Monthly Salary:     ${employeeSalary.toFixed(2)} BDT\n` +
                                    `Already Paid:       ${paidThisMonth.toFixed(2)} BDT\n` +
                                    `Current Payment:    ${amount.toFixed(2)} BDT\n` +
                                    `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n` +
                                    `Total This Month:   ${totalAfterApproval.toFixed(2)} BDT\n` +
                                    `❌ EXCESS AMOUNT:   ${excess.toFixed(2)} BDT\n` +
                                    `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n` +
                                    `⚠️ Are you sure you want to approve this payment?\n` +
                                    `This will exceed the salary limit by ${excess.toFixed(2)} BDT!`;
                            } else {
                                confirmMessage = 
                                    `✓ Approve Payroll for ${employeeName}\n\n` +
                                    `Amount: ${amount.toFixed(2)} BDT\n` +
                                    `Already Paid This Month: ${paidThisMonth.toFixed(2)} BDT\n` +
                                    `Total After Approval: ${totalAfterApproval.toFixed(2)} BDT\n` +
                                    `Monthly Salary: ${employeeSalary.toFixed(2)} BDT\n\n` +
                                    `The amount will be deducted from the account.\n` +
                                    `Are you sure you want to approve?`;
                            }
                            
                            if (confirm(confirmMessage)) {
                                form.submit();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            if (confirm('Are you sure you want to approve this payroll?\nThe amount will be deducted from the account.')) {
                                form.submit();
                            }
                        });
                });
            });

            // ===== ADD PAYROLL FORM =====
            // Update employee salary display when employee is selected
            employeeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                employeeSalary = parseFloat(selectedOption.getAttribute('data-salary')) || 0;
                employeeSalarySpan.textContent = employeeSalary.toFixed(2);
                
                // Fetch paid amount for this month
                if (this.value && paymentDateInput.value) {
                    fetchPaidAmount();
                } else {
                    paidThisMonth = 0;
                    paidThisMonthSpan.textContent = '0.00';
                    checkSalaryLimit();
                }
            });

            // Fetch paid amount when date changes
            paymentDateInput.addEventListener('change', function() {
                if (employeeSelect.value) {
                    fetchPaidAmount();
                } else {
                    checkSalaryLimit();
                }
            });

            // Check salary limit on amount input
            amountInput.addEventListener('input', checkSalaryLimit);

            // Fetch paid amount for selected employee and month via AJAX
            function fetchPaidAmount() {
                const employeeId = employeeSelect.value;
                const paymentDate = paymentDateInput.value;
                
                if (!employeeId || !paymentDate) {
                    paidThisMonth = 0;
                    paidThisMonthSpan.textContent = '0.00';
                    checkSalaryLimit();
                    return;
                }

                isLoading = true;
                submitBtn.disabled = true;
                paidThisMonthSpan.textContent = 'Loading...';
                
                console.log('Fetching paid amount for:', { employeeId, paymentDate });
                
                const url = `${PAYROLL_ROUTES.getPaidAmount}?employee_id=${employeeId}&payment_date=${paymentDate}`;
                console.log('Fetching from URL:', url);

                fetch(url)
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Received data:', data);
                        paidThisMonth = parseFloat(data.paid_amount) || 0;
                        paidThisMonthSpan.textContent = paidThisMonth.toFixed(2);
                        isLoading = false;
                        submitBtn.disabled = false;
                        checkSalaryLimit();
                    })
                    .catch(error => {
                        console.error('Error fetching paid amount:', error);
                        paidThisMonthSpan.textContent = 'Error!';
                        paidThisMonth = 0;
                        isLoading = false;
                        submitBtn.disabled = false;
                        
                        // Show error alert
                        alert('Failed to fetch payment data. Please refresh the page and try again.\n\nError: ' + error.message);
                        checkSalaryLimit();
                    });
            }

            // Check if current amount will exceed salary limit
            function checkSalaryLimit() {
                const currentAmount = parseFloat(amountInput.value) || 0;
                const totalAmount = paidThisMonth + currentAmount;
                
                // Show alert if there's already payment OR if it will exceed
                if (employeeSalary > 0 && (paidThisMonth > 0 || totalAmount > employeeSalary)) {
                    const excess = totalAmount > employeeSalary ? totalAmount - employeeSalary : 0;
                    
                    let alertHtml = `<div class="mb-2"><strong>`;
                    
                    if (totalAmount > employeeSalary) {
                        alertHtml += `⚠️ This payment will EXCEED the employee's monthly salary!`;
                    } else if (paidThisMonth > 0) {
                        alertHtml += `ℹ️ Employee has already received payment this month!`;
                    }
                    
                    alertHtml += `</strong></div>
                        <table class="table table-sm table-bordered mb-0" style="font-size: 0.9rem;">
                            <tr>
                                <td><strong>Monthly Salary:</strong></td>
                                <td class="text-end">${employeeSalary.toFixed(2)} BDT</td>
                            </tr>
                            <tr ${paidThisMonth > 0 ? 'class="table-warning"' : ''}>
                                <td><strong>Already Paid This Month:</strong></td>
                                <td class="text-end"><strong>${paidThisMonth.toFixed(2)} BDT</strong></td>
                            </tr>
                            <tr>
                                <td><strong>Current Payment:</strong></td>
                                <td class="text-end">${currentAmount.toFixed(2)} BDT</td>
                            </tr>
                            <tr class="${totalAmount > employeeSalary ? 'table-danger' : 'table-info'}">
                                <td><strong>Total This Month:</strong></td>
                                <td class="text-end"><strong>${totalAmount.toFixed(2)} BDT</strong></td>
                            </tr>`;
                    
                    if (excess > 0) {
                        alertHtml += `
                            <tr class="table-danger">
                                <td><strong>❌ Excess Amount:</strong></td>
                                <td class="text-end"><strong>${excess.toFixed(2)} BDT</strong></td>
                            </tr>`;
                    } else {
                        const remaining = employeeSalary - totalAmount;
                        alertHtml += `
                            <tr class="table-success">
                                <td><strong>✓ Remaining:</strong></td>
                                <td class="text-end"><strong>${remaining.toFixed(2)} BDT</strong></td>
                            </tr>`;
                    }
                    
                    alertHtml += `</table>`;
                    
                    salaryAlertMessage.innerHTML = alertHtml;
                    salaryAlert.classList.remove('d-none');
                    
                    // Change alert color based on severity
                    if (totalAmount > employeeSalary) {
                        salaryAlert.className = 'alert alert-danger';
                    } else if (paidThisMonth > 0) {
                        salaryAlert.className = 'alert alert-warning';
                    }
                } else {
                    salaryAlert.classList.add('d-none');
                }
            }

            // Form submission - Show confirmation if exceeding salary OR already paid
            payrollForm.addEventListener('submit', function(e) {
                const currentAmount = parseFloat(amountInput.value) || 0;
                const totalAmount = paidThisMonth + currentAmount;
                
                // Prevent submission if still loading
                if (isLoading) {
                    e.preventDefault();
                    alert('Please wait, loading employee payment data...');
                    return false;
                }
                
                // Show warning if already paid this month OR exceeding salary
                if (employeeSalary > 0 && (paidThisMonth > 0 || totalAmount > employeeSalary)) {
                    const excess = totalAmount > employeeSalary ? (totalAmount - employeeSalary).toFixed(2) : 0;
                    const remaining = totalAmount <= employeeSalary ? (employeeSalary - totalAmount).toFixed(2) : 0;
                    
                    let confirmMessage = '';
                    
                    if (totalAmount > employeeSalary) {
                        confirmMessage = 
                            `⚠️ WARNING: This payment will EXCEED the employee's monthly salary!\n\n` +
                            `📊 Payment Summary:\n` +
                            `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n` +
                            `Monthly Salary:      ${employeeSalary.toFixed(2)} BDT\n` +
                            `Already Paid:        ${paidThisMonth.toFixed(2)} BDT\n` +
                            `Current Payment:     ${currentAmount.toFixed(2)} BDT\n` +
                            `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n` +
                            `Total This Month:    ${totalAmount.toFixed(2)} BDT\n` +
                            `❌ Excess Amount:    ${excess} BDT\n` +
                            `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n` +
                            `❓ Do you want to proceed with this payment?\n\n` +
                            `⚠️ You MUST provide a reason in the Note field!`;
                    } else if (paidThisMonth > 0) {
                        confirmMessage = 
                            `ℹ️ NOTICE: Employee has already received payment this month!\n\n` +
                            `📊 Payment Summary:\n` +
                            `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n` +
                            `Monthly Salary:      ${employeeSalary.toFixed(2)} BDT\n` +
                            `Already Paid:        ${paidThisMonth.toFixed(2)} BDT\n` +
                            `Current Payment:     ${currentAmount.toFixed(2)} BDT\n` +
                            `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n` +
                            `Total This Month:    ${totalAmount.toFixed(2)} BDT\n` +
                            `✓ Remaining:         ${remaining} BDT\n` +
                            `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n` +
                            `❓ Do you want to add another payment for this employee?\n\n` +
                            `💡 Consider adding a note explaining the reason for multiple payments.`;
                    }
                    
                    if (!confirm(confirmMessage)) {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Check if note field is filled when exceeding salary
                    const noteField = document.getElementById('note');
                    if (totalAmount > employeeSalary && !noteField.value.trim()) {
                        e.preventDefault();
                        alert('⚠️ Please provide a reason in the Note field for exceeding the salary limit!');
                        noteField.focus();
                        return false;
                    }
                }
            });
        });

        @if($errors->any())
        // Reopen modal if validation errors exist
        var modal = new bootstrap.Modal(document.getElementById('addPayrollModal'));
        modal.show();
        @endif
    </script>

    <!-- Footer Note -->
    <div class="row mt-4 mb-3">
        <div class="col-12">
            <p class="text-center text-muted small mb-0">
                Developed by Shifaul Hasan &copy; 2026
            </p>
        </div>
    </div>

</x-app-layout>