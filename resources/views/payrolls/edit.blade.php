<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">Edit Payroll</h2>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-4">
                    <small>The field labels marked with * are required input fields.</small>
                </p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('payrolls.update', $payroll) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Date -->
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Date *</label>
                                <input type="date" 
                                       class="form-control @error('payment_date') is-invalid @enderror" 
                                       id="payment_date" 
                                       name="payment_date" 
                                       value="{{ old('payment_date', $payroll->payment_date->format('Y-m-d')) }}" 
                                       required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Employee -->
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">Employee *</label>
                                <select class="form-select @error('employee_id') is-invalid @enderror" 
                                        id="employee_id" 
                                        name="employee_id" 
                                        required>
                                    <option value="">Select Employee...</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" 
                                                {{ old('employee_id', $payroll->employee_id) == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }} ({{ $employee->staff_id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
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
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" 
                                                {{ old('account_id', $payroll->account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ $account->account_no }}) - Balance: {{ number_format($account->current_balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Amount -->
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount *</label>
                                <input type="number" 
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" 
                                       name="amount" 
                                       step="0.01" 
                                       min="0.01"
                                       value="{{ old('amount', $payroll->amount) }}" 
                                       required>
                                @error('amount')
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
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method }}" 
                                                {{ old('payment_method', $payroll->payment_method) == $method ? 'selected' : '' }}>
                                            {{ $method }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Note -->
                            <div class="mb-3">
                                <label for="note" class="form-label">Note</label>
                                <textarea class="form-control @error('note') is-invalid @enderror" 
                                          id="note" 
                                          name="note" 
                                          rows="3">{{ old('note', $payroll->note) }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update
                        </button>
                        <a href="{{ route('payrolls.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>