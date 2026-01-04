<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark">Employee List</h2>
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
                        <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle"></i> Add Employee
                        </a>
                    </div>
                </div>

                <!-- Search Box -->
                <div class="mb-3">
                    <input type="text" id="searchBox" class="form-control" placeholder="Search employees...">
                </div>

                <!-- Employee Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Role</th>
                                <th>Address</th>
                                <th>Staff ID</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                                <tr>
                                    <td><input type="checkbox" class="employee-checkbox"></td>
                                    <td>
                                        @if($employee->image)
                                            <img src="{{ asset('storage/' . $employee->image) }}" 
                                                 alt="{{ $employee->name }}" 
                                                 class="rounded-circle" 
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <span class="text-white">{{ substr($employee->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->email }}</td>
                                    <td>{{ $employee->phone ?? 'N/A' }}</td>
                                    <td><span class="badge bg-info">{{ $employee->role }}</span></td>
                                    <td>{{ $employee->address ?? 'N/A' }}</td>
                                    <td>{{ $employee->staff_id }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('employees.show', $employee) }}" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('employees.edit', $employee) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('employees.destroy', $employee) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <p class="text-muted">No employees found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} 
                        of {{ $employees->total() }} entries
                    </div>
                    <div>
                        {{ $employees->links() }}
                    </div>
                </div>
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