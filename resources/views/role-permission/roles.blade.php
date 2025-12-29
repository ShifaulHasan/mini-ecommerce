<x-app-layout>
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="h4 fw-bold text-dark">
                    <i class="bi bi-person-badge"></i> Role Permission Management
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

        {{-- Super Admin Role Card --}}
        @php
            $superAdmin = $roles->firstWhere('name', 'super-admin');
        @endphp
        
        @if($superAdmin)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-shield-fill-check"></i> {{ ucfirst($superAdmin->name) }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('roles.permissions.assign', $superAdmin->id) }}">
                        @csrf

                        <div class="row g-2">
                            @forelse($permissions as $permission)
                                <div class="col-md-4 col-lg-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permission->name }}"
                                               id="perm_{{ $superAdmin->id }}_{{ $permission->id }}"
                                               {{ $superAdmin->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_{{ $superAdmin->id }}_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted mb-0">No permissions available.</p>
                                </div>
                            @endforelse
                        </div>

                        <hr class="my-3">

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg"></i> Save Permissions
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Admin Role Card --}}
        @php
            $admin = $roles->firstWhere('name', 'admin');
        @endphp
        
        @if($admin)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-award"></i> {{ ucfirst($admin->name) }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('roles.permissions.assign', $admin->id) }}">
                        @csrf

                        <div class="row g-2">
                            @forelse($permissions as $permission)
                                <div class="col-md-4 col-lg-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permission->name }}"
                                               id="perm_{{ $admin->id }}_{{ $permission->id }}"
                                               {{ $admin->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_{{ $admin->id }}_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted mb-0">No permissions available.</p>
                                </div>
                            @endforelse
                        </div>

                        <hr class="my-3">

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg"></i> Save Permissions
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Staff Role Card --}}
        @php
            $staff = $roles->firstWhere('name', 'staff');
        @endphp
        
        @if($staff)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-person-badge"></i> {{ ucfirst($staff->name) }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('roles.permissions.assign', $staff->id) }}">
                        @csrf

                        <div class="row g-2">
                            @forelse($permissions as $permission)
                                <div class="col-md-4 col-lg-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permission->name }}"
                                               id="perm_{{ $staff->id }}_{{ $permission->id }}"
                                               {{ $staff->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_{{ $staff->id }}_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted mb-0">No permissions available.</p>
                                </div>
                            @endforelse
                        </div>

                        <hr class="my-3">

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg"></i> Save Permissions
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Other Roles (if any) --}}
        @foreach($roles->whereNotIn('name', ['super-admin', 'admin', 'staff']) as $role)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-award"></i> {{ ucfirst($role->name) }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('roles.permissions.assign', $role->id) }}">
                        @csrf

                        <div class="row g-2">
                            @forelse($permissions as $permission)
                                <div class="col-md-4 col-lg-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permission->name }}"
                                               id="perm_{{ $role->id }}_{{ $permission->id }}"
                                               {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_{{ $role->id }}_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted mb-0">No permissions available.</p>
                                </div>
                            @endforelse
                        </div>

                        <hr class="my-3">

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg"></i> Save Permissions
                        </button>
                    </form>
                </div>
            </div>
        @endforeach

        {{-- No Roles Found --}}
        @if($roles->isEmpty())
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> No roles found. Please create roles first.
            </div>
        @endif
    </div>
</x-app-layout>