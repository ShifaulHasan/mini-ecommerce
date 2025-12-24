<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Edit Customer</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
                        <li class="breadcrumb-item active">Edit {{ $customer->name }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('customers.show', $customer) }}" class="btn btn-info me-2">
                    <i class="fas fa-eye"></i> View Details
                </a>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Customer Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3"><i class="fas fa-user"></i> Basic Information</h6>

                            <div class="mb-3">
                                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="form-control @error('email') is-invalid @enderror">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control @error('phone') is-invalid @enderror" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $customer->company_name) }}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tax Number</label>
                                <input type="text" name="tax_number" value="{{ old('tax_number', $customer->tax_number) }}" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-primary mb-3"><i class="fas fa-map-marker-alt"></i> Address Information</h6>

                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3">{{ old('address', $customer->address) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" value="{{ old('city', $customer->city) }}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">State</label>
                                <input type="text" name="state" value="{{ old('state', $customer->state) }}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" value="{{ old('country', $customer->country) }}" class="form-control">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_supplier" value="1" {{ old('is_supplier', $customer->is_supplier) ? 'checked' : '' }}>
                                <label class="form-check-label">Supplier</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Customer</button>
                    </div>
                </form>
            </div>
        </div>

        @if($customer->sales()->count() > 0 || $customer->payments->count() > 0)
        <div class="card border-warning mt-4">
            <div class="card-body d-flex">
                <i class="fas fa-exclamation-triangle text-warning fa-2x me-3"></i>
                <div>
                    <h6 class="mb-1">Important Notice</h6>
                    <p class="mb-0 text-muted small">This customer has existing sales or payment records.</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>