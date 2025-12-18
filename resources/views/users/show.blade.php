<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-person"></i> User Details
            </h2>
            <div>
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 150px; height: 150px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 60px; font-weight: bold;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    @endif
                    
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        @if($user->role === 'admin')
                        <span class="badge bg-danger">Admin</span>
                        @elseif($user->role === 'manager')
                        <span class="badge bg-primary">Manager</span>
                        @elseif($user->role === 'cashier')
                        <span class="badge bg-info">Cashier</span>
                        @else
                        <span class="badge bg-secondary">User</span>
                        @endif
                        
                        @if($user->status === 'active')
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-warning">Inactive</span>
                        @endif
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Profile
                        </a>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Delete User
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="fw-bold text-muted">Full Name</label>
                            <p class="h6">{{ $user->name }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold text-muted">Email Address</label>
                            <p class="h6">{{ $user->email }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold text-muted">Company Name</label>
                            <p class="h6">{{ $user->company_name ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold text-muted">Phone Number</label>
                            <p class="h6">{{ $user->phone ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold text-muted">Role</label>
                            <p class="h6">{{ ucfirst($user->role) }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold text-muted">Status</label>
                            <p class="h6">{{ ucfirst($user->status) }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold text-muted">Email Verified</label>
                            <p class="h6">
                                @if($user->email_verified_at)
                                <span class="text-success"><i class="bi bi-check-circle"></i> Verified</span>
                                @else
                                <span class="text-warning"><i class="bi bi-exclamation-circle"></i> Not Verified</span>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold text-muted">Member Since</label>
                            <p class="h6">{{ $user->created_at->format('d M Y') }}</p>
                        </div>

                        <div class="col-md-12">
                            <label class="fw-bold text-muted">Last Updated</label>
                            <p class="h6">{{ $user->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Card (Optional) -->
            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Activity Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <h2 class="text-primary">0</h2>
                                <p class="text-muted mb-0">Sales Made</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <h2 class="text-success">0</h2>
                                <p class="text-muted mb-0">Purchases</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <h2 class="text-info">0</h2>
                                <p class="text-muted mb-0">Total Actions</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>