<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-person-plus"></i> Add New User
            </h2>
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Users
            </a>
        </div>
    </x-slot>

    <style>
        .user-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .form-label {
            font-weight: 600;
            color: #374151;
        }
        .form-control, .form-select {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 15px;
        }
        .supplier-customer-fields {
            background: #f8f9ff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
            display: none;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
        }
    </style>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="user-card">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="row g-4">
                {{-- Basic Info --}}
                <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                {{-- Role --}}
                <div class="col-md-6">
                    <label class="form-label">User Role *</label>
                    <select name="role" id="roleSelect" class="form-select" required>
                        <option value="">Select Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>
                                {{ $role }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-md-6">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                </div>

                {{-- Phone (ALWAYS VISIBLE ✅) --}}
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control"
                           value="{{ old('phone') }}"
                           placeholder="+880 1XXX-XXXXXX">
                </div>

                {{-- Supplier / Customer Extra Fields --}}
                <div class="col-12">
                    <div class="supplier-customer-fields" id="additionalFields">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="company_name"
                                       class="form-control"
                                       value="{{ old('company_name') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-submit">
                        <i class="bi bi-check-circle"></i> Create User
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-light ms-2">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('roleSelect');
            const additionalFields = document.getElementById('additionalFields');

            function toggleFields() {
                if (roleSelect.value === 'Supplier' || roleSelect.value === 'Customer') {
                    additionalFields.style.display = 'block';
                } else {
                    additionalFields.style.display = 'none';
                }
            }

            roleSelect.addEventListener('change', toggleFields);
            toggleFields();
        });
    </script>

        </div> 

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
