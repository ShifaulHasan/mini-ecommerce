<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Customer') }}
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 font-weight-bold">Add New Customer</h5>
                        <small class="text-muted">Fields marked with <span class="text-danger">*</span> are required.</small>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('customers.store') }}" method="POST" id="customerForm">
                            @csrf

                            <!-- Basic Information -->
                            <h6 class="text-primary mb-3 fw-bold">Basic Information</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                           class="form-control @error('name') is-invalid @enderror">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                                           class="form-control @error('phone') is-invalid @enderror">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                                           class="form-control @error('email') is-invalid @enderror">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}"
                                           class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="tax_number" class="form-label">Tax Number</label>
                                    <input type="text" name="tax_number" id="tax_number" value="{{ old('tax_number') }}"
                                           class="form-control">
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Address Details -->
                            <h6 class="text-primary mb-3 fw-bold">Address Details</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea name="address" id="address" rows="2"
                                              class="form-control">{{ old('address') }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" name="city" id="city" value="{{ old('city') }}"
                                           class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" name="state" id="state" value="{{ old('state') }}"
                                           class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}"
                                           class="form-control">
                                </div>

                                <div class="col-md-12">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" name="country" id="country" value="{{ old('country', 'Bangladesh') }}"
                                           class="form-control">
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Options -->
                            <h6 class="text-primary mb-3 fw-bold">Settings</h6>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_supplier" name="is_supplier">
                                    <label class="form-check-label" for="is_supplier">
                                        Both Customer and Supplier
                                    </label>
                                </div>

                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="add_user" name="add_user">
                                    <label class="form-check-label" for="add_user">
                                        Add User
                                    </label>
                                </div>
                            </div>

                            <!-- Password Field (Hidden by default) -->
                            <div id="password_field" class="row hidden mb-4" style="display: none;">
                                <div class="col-md-6">
                                    <label for="user_password" class="form-label">Password</label>
                                    <input type="password" name="user_password" id="user_password"
                                           class="form-control @error('user_password') is-invalid @enderror">
                                    @error('user_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('customers.index') }}" class="btn btn-light border">Cancel</a>
                                <button type="submit" id="submitBtn" class="btn text-white" style="background-color: #6f42c1;">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addUserCheckbox = document.getElementById('add_user');
            const passwordField = document.getElementById('password_field');
            const form = document.getElementById('customerForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnOriginalText = submitBtn.innerHTML;

            // Toggle password field
            if(addUserCheckbox) {
                addUserCheckbox.addEventListener('change', function() {
                    passwordField.style.display = this.checked ? 'block' : 'none';
                });
            }

            // Handle form submission
            if(form) {
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                });
            }
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