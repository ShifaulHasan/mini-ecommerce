<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="mb-0">Customer Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('customers.index') }}">Customers</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ $customer->name }}
                    </li>
                </ol>
            </nav>
        </div>
    </x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-end mb-4">
            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> Edit Customer
            </a>
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="row">
            <!-- Customer Info -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user"></i> Customer Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-muted"><strong>Customer Code:</strong></td>
                                <td>{{ $customer->customer_code }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Name:</strong></td>
                                <td>{{ $customer->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Email:</strong></td>
                                <td>{{ $customer->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Phone:</strong></td>
                                <td>{{ $customer->phone }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Company:</strong></td>
                                <td>{{ $customer->company_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Status:</strong></td>
                                <td>
                                    @if($customer->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt"></i> Address
                        </h5>
                    </div>
                    <div class="card-body">
                        {{ $customer->address ?? 'No address provided' }}
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="col-md-8">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-sm border-left-primary">
                            <div class="card-body">
                                <div class="text-uppercase text-primary small">
                                    Total Sales
                                </div>
                                <div class="h5">
                                    ৳{{ number_format($totalSales, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm border-left-success">
                            <div class="card-body">
                                <div class="text-uppercase text-success small">
                                    Sales Count
                                </div>
                                <div class="h5">
                                    {{ $salesCount }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm border-left-warning">
                            <div class="card-body">
                                <div class="text-uppercase text-warning small">
                                    Total Due
                                </div>
                                <div class="h5">
                                    ৳{{ number_format($customer->total_due, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm border-left-info">
                            <div class="card-body">
                                <div class="text-uppercase text-info small">
                                    Deposited Balance
                                </div>
                                <div class="h5">
                                    ৳{{ number_format($customer->deposited_balance ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .border-left-primary { border-left: 4px solid #4e73df; }
        .border-left-success { border-left: 4px solid #1cc88a; }
        .border-left-warning { border-left: 4px solid #f6c23e; }
        .border-left-info { border-left: 4px solid #36b9cc; }
    </style>
</x-app-layout>
