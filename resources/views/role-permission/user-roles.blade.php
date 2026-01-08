<x-app-layout>
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="h4 fw-bold text-dark">
                    <i class="bi bi-people"></i> User Role Management
                </h2>
            </div>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- User List Card --}}
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-person-gear"></i> Assign Roles to Users</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">User</th>
                                <th style="width: 30%;">Email</th>
                                <th style="width: 20%;">Current Role</th>
                                <th style="width: 25%;">Assign Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <i class="bi bi-person-circle text-primary"></i>
                                        <strong>{{ $user->name }}</strong>
                                    </td>
                                    <td class="text-muted">{{ $user->email }}</td>
                                    <td>
                                        @if($user->roles->first())
                                            <span class="badge bg-secondary">
                                                {{ $user->roles->pluck('name')->first() }}
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark">No Role</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('user.roles.assign') }}" class="d-flex gap-2">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">

                                            <select name="role" class="form-select form-select-sm" style="max-width: 200px;">
                                                <option value="" disabled selected>Select Role</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->name }}" 
                                                        {{ $user->roles->pluck('name')->first() == $role->name ? 'selected' : '' }}>
                                                        {{ ucfirst($role->name) }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bi bi-check2"></i> Assign
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No users found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Note -->
    <div class="row mt-4 mb-3">
        <div class="col-12">
            <p class="text-center text-muted small mb-0">
                Developed by Shifaul Hasan &copy; 2026
            </p>
        </div>
    </div>
</x-app-layout>