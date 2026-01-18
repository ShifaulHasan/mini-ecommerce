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

    <style>
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .user-avatar-large {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #667eea;
        }
        .avatar-placeholder-large {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 60px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
    </style>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="user-avatar-large mb-3">
                    @else
                        <div class="avatar-placeholder-large mb-3">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        @if($user->role === 'Admin' || $user->role === 'Superadmin')
                            <span class="badge bg-danger">{{ $user->role }}</span>
                        @elseif($user->role === 'Manager')
                            <span class="badge bg-primary">Manager</span>
                        @elseif($user->role === 'Employee' || $user->role === 'Stuff')
                            <span class="badge bg-info">{{ $user->role }}</span>
                        @elseif($user->role === 'Customer')
                            <span class="badge bg-success">Customer</span>
                        @elseif($user->role === 'Supplier')
                            <span class="badge bg-warning">Supplier</span>
                        @else
                            <span class="badge bg-secondary">{{ $user->role }}</span>
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
                        @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-trash"></i> Delete User
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> User Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="fw-bold text-muted"><i class="bi bi-person"></i> Full Name</label>
                            <p class="h6">{{ $user->name }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold text-muted"><i class="bi bi-envelope"></i> Email Address</label>
                            <p class="h6">{{ $user->email }}</p>
                        </div>

                        @if($user->company_name)
                        <div class="col-md-6">
                            <label class="fw-bold text-muted"><i class="bi bi-building"></i> Company Name</label>
                            <p class="h6">{{ $user->company_name }}</p>
                        </div>
                        @endif

                        @if($user->phone)
                        <div class="col-md-6">
                            <label class="fw-bold text-muted"><i class="bi bi-telephone"></i> Phone Number</label>
                            <p class="h6">{{ $user->phone }}</p>
                        </div>
                        @endif

                        <div class="col-md-6">
                            <label class="fw-bold text-muted"><i class="bi bi-shield-check"></i> Role</label>
                            <p class="h6">{{ $user->role }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold text-muted"><i class="bi bi-toggle-on"></i> Status</label>
                            <p class="h6">
                                @if($user->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-warning">Inactive</span>
                                @endif
                            </p>
                        </div>

                        @if($user->address)
                        <div class="col-md-12">
                            <label class="fw-bold text-muted"><i class="bi bi-geo-alt"></i> Address</label>
                            <p class="h6">{{ $user->address }}</p>
                        </div>
                        @endif

                        <div class="col-md-6">
                            <label class="fw-bold text-muted"><i class="bi bi-envelope-check"></i> Email Verified</label>
                            <p class="h6">
                                @if($user->email_verified_at)
                                    <span class="text-success"><i class="bi bi-check-circle-fill"></i> Verified</span>
                                    <br>
                                    <small class="text-muted">{{ $user->email_verified_at->format('d M Y, h:i A') }}</small>
                                @else
                                    <span class="text-warning"><i class="bi bi-exclamation-circle-fill"></i> Not Verified</span>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold text-muted"><i class="bi bi-calendar-plus"></i> Member Since</label>
                            <p class="h6">{{ $user->created_at->format('d M Y') }}</p>
                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                        </div>

                        <div class="col-md-12">
                            <label class="fw-bold text-muted"><i class="bi bi-clock-history"></i> Last Updated</label>
                            <p class="h6">{{ $user->updated_at->format('d M Y, h:i A') }}</p>
                            <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
            </div>

            @if($user->role === 'Customer' || $user->role === 'Supplier')
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Activity Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <h3 class="text-primary mb-0">0</h3>
                                <small class="text-muted">Total Orders</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <h3 class="text-success mb-0">৳0</h3>
                                <small class="text-muted">Total Amount</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <h3 class="text-warning mb-0">৳0</h3>
                                <small class="text-muted">Due Amount</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
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