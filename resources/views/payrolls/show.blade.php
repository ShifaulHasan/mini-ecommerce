<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">Payroll Details</h2>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Payroll Information</h5>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Reference:</div>
                            <div class="col-md-7">{{ $payroll->payroll_reference }}</div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Payment Date:</div>
                            <div class="col-md-7">{{ $payroll->payment_date->format('F d, Y') }}</div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Employee:</div>
                            <div class="col-md-7">{{ $payroll->employee->name ?? 'N/A' }}</div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Staff ID:</div>
                            <div class="col-md-7">{{ $payroll->employee->staff_id ?? 'N/A' }}</div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Account:</div>
                            <div class="col-md-7">{{ $payroll->account->name ?? 'N/A' }}</div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Account Number:</div>
                            <div class="col-md-7">{{ $payroll->account->account_no ?? 'N/A' }}</div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Amount:</div>
                            <div class="col-md-7">
                                <span class="badge bg-success fs-6">{{ number_format($payroll->amount, 2) }}</span>
                            </div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Payment Method:</div>
                            <div class="col-md-7">
                                <span class="badge bg-info">{{ $payroll->payment_method }}</span>
                            </div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Note:</div>
                            <div class="col-md-7">{{ $payroll->note ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Additional Information</h5>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Created By:</div>
                            <div class="col-md-7">{{ $payroll->creator->name ?? 'System' }}</div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Created At:</div>
                            <div class="col-md-7">{{ $payroll->created_at->format('F d, Y h:i A') }}</div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Last Updated:</div>
                            <div class="col-md-7">{{ $payroll->updated_at->format('F d, Y h:i A') }}</div>
                        </div>

                        @if($payroll->transaction)
                        <h5 class="border-bottom pb-2 mb-3 mt-4">Transaction Details</h5>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Transaction Type:</div>
                            <div class="col-md-7">
                                <span class="badge bg-danger">{{ strtoupper($payroll->transaction->transaction_type) }}</span>
                            </div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Balance Before:</div>
                            <div class="col-md-7">{{ number_format($payroll->transaction->balance_before, 2) }}</div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-md-5 fw-bold">Balance After:</div>
                            <div class="col-md-7">{{ number_format($payroll->transaction->balance_after, 2) }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('payrolls.edit', $payroll) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('payrolls.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                    <form action="{{ route('payrolls.destroy', $payroll) }}" 
                          method="POST" 
                          class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this payroll?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>