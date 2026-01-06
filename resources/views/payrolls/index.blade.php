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
                                                <!-- Approve Button -->
                                               
                                                <form action="{{ route('payrolls.approve', $payroll) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to approve this payroll? The amount will be deducted from the account.');">
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
                                    <td colspan="9" class="text-center py-4">
                                        <p class="text-muted">No payroll records found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end">Total:</td>
                                <td colspan="4">{{ number_format($totalAmount, 2) }}</td>
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
                <form action="{{ route('payrolls.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            <small>The field labels marked with * are required input fields.</small>
                        </p>

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
                                                    {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }} ({{ $employee->staff_id }})
                                            </option>
                                        @endforeach
                                    </select>
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
                        <button type="submit" class="btn btn-primary">
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
    </style>

    @if($errors->any())
    <script>
        // Reopen modal if validation errors exist
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('addPayrollModal'));
            modal.show();
        });
    </script>
    @endif

    <!-- Footer Note -->
    <div class="row mt-4 mb-3">
        <div class="col-12">
            <p class="text-center text-muted small mb-0">
                Developed by Shifaul Hasan &copy; 2026
            </p>
        </div>
    </div>

</x-app-layout>