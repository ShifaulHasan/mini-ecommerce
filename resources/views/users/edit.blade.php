<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-pencil-square"></i> Edit User
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
        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #667eea;
        }
        .avatar-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            font-weight: bold;
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
        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- Avatar Upload --}}
                <div class="col-12 text-center mb-3">
                    @if($user->avatar)
                        <img id="avatarPreview" class="avatar-preview" src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}">
                        <div class="avatar-placeholder" id="avatarPlaceholder" style="display: none;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @else
                        <img id="avatarPreview" class="avatar-preview" style="display: none;">
                        <div class="avatar-placeholder" id="avatarPlaceholder">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    
                    <div class="mt-3">
                        <label for="avatarInput" class="btn btn-outline-primary">
                            <i class="bi bi-camera"></i> Change Avatar
                        </label>
                        <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/*">
                        <button type="button" class="btn btn-outline-danger ms-2" id="removeAvatar">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    <input type="hidden" name="remove_avatar" id="removeAvatarInput" value="0">
                </div>

                {{-- Basic Info --}}
                <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password (Leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control" minlength="8">
                    <small class="text-muted">Minimum 8 characters</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>

                {{-- Role --}}
                <div class="col-md-6">
                    <label class="form-label">User Role *</label>
                    <select name="role" id="roleSelect" class="form-select" required>
                        <option value="">Select Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>
                                {{ $role }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-md-6">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                </div>

                {{-- Phone --}}
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control"
                           value="{{ old('phone', $user->phone) }}"
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
                                       value="{{ old('company_name', $user->company_name) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3">{{ old('address', $user->address) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-submit">
                        <i class="bi bi-check-circle"></i> Update User
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
            const avatarInput = document.getElementById('avatarInput');
            const avatarPreview = document.getElementById('avatarPreview');
            const avatarPlaceholder = document.getElementById('avatarPlaceholder');
            const removeAvatar = document.getElementById('removeAvatar');
            const removeAvatarInput = document.getElementById('removeAvatarInput');

            // Role toggle
            function toggleFields() {
                if (roleSelect.value === 'Supplier' || roleSelect.value === 'Customer') {
                    additionalFields.style.display = 'block';
                } else {
                    additionalFields.style.display = 'none';
                }
            }

            roleSelect.addEventListener('change', toggleFields);
            toggleFields();

            // Avatar preview
            avatarInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                        avatarPreview.style.display = 'block';
                        avatarPlaceholder.style.display = 'none';
                        removeAvatarInput.value = '0';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Remove avatar
            removeAvatar.addEventListener('click', function() {
                avatarInput.value = '';
                avatarPreview.style.display = 'none';
                avatarPlaceholder.style.display = 'flex';
                removeAvatarInput.value = '1';
            });
        });
    </script>

    <!-- Footer Note -->
    <div class="row mt-4 mb-3">
        <div class="col-12">
            <p class="text-center text-muted small mb-0">
                Developed by Shifaul Hasan &copy; 2026
            </p>
        </div>
    </div>

</x-app-layout>