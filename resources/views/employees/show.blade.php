<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">Employee Details</h2>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        @if($employee->image)
                            <img src="{{ asset('storage/' . $employee->image) }}" 
                                 alt="{{ $employee->name }}" 
                                 class="img-thumbnail rounded-circle mb-3" 
                                 style="width: 200px; height: 200px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 200px; height: 200px;">
                                <span class="text-white display-1">{{ substr($employee->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <h4>{{ $employee->name }}</h4>
                        <p class="text-muted">{{ $employee->designation ?? 'N/A' }}</p>
                        <span class="badge bg-info fs-6">{{ $employee->role }}</span>
                    </div>

                    <div class="col-md-8">
                        <h5 class="border-bottom pb-2 mb-3">Personal Information</h5>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Staff ID:</div>
                            <div class="col-md-8">{{ $employee->staff_id }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Username:</div>
                            <div class="col-md-8">{{ $employee->username }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Email:</div>
                            <div class="col-md-8">{{ $employee->email }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Phone:</div>
                            <div class="col-md-8">{{ $employee->phone ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Address:</div>
                            <div class="col-md-8">{{ $employee->address ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">City:</div>
                            <div class="col-md-8">{{ $employee->city ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Country:</div>
                            <div class="col-md-8">{{ $employee->country ?? 'N/A' }}</div>
                        </div>

                        <h5 class="border-bottom pb-2 mb-3 mt-4">Employment Information</h5>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Salary:</div>
                            <div class="col-md-8">{{ $employee->salary ? '$' . number_format($employee->salary, 2) : 'N/A' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Joining Date:</div>
                            <div class="col-md-8">{{ $employee->joining_date ? date('F d, Y', strtotime($employee->joining_date)) : 'N/A' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Created At:</div>
                            <div class="col-md-8">{{ $employee->created_at->format('F d, Y h:i A') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Last Updated:</div>
                            <div class="col-md-8">{{ $employee->updated_at->format('F d, Y h:i A') }}</div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                    <form action="{{ route('employees.destroy', $employee) }}" 
                          method="POST" 
                          class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this employee?');">
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