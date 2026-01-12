<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Mini E-commerce') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: #f5f6fa;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #1e1e2d;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            color: #fff;
            padding-top: 70px;
        }
        .sidebar h4 {
            padding: 0 20px;
            font-size: 16px;
            line-height: 22px;
        }
        .sidebar a {
            display: block;
            padding: 12px 25px;
            color: #bbb;
            text-decoration: none;
            font-size: 15px;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background: #3e3e57;
            color: #fff;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .topbar {
            height: 60px;
            background: #fff;
            position: fixed;
            left: 250px;
            right: 0;
            top: 0;
            border-bottom: 1px solid #ddd;
            padding: 15px 25px;
            z-index: 100;
        }
        
        /* DataTable Button Styling */
        .dt-buttons {
            margin-bottom: 15px;
        }
        .dt-button {
            margin-right: 5px !important;
            padding: 5px 10px !important;
            font-size: 14px !important;
        }
    </style>
</head>

<body>

  
  <!-- Sidebar -->
<div class="sidebar">
    
    <!-- Logo Section and name of the site  -->
<div class="logo-section" style="padding: 24px 20px; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1); background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);">
    <img src="{{ asset('images/icon.png') }}" 
         alt="Logo" 
         class="sidebar-logo"
         style="border-radius: 50%; width: 64px; height: 64px; display: block; margin: 0 auto 16px auto; object-fit: cover; border: 3px solid rgba(255, 255, 255, 0.15); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); transition: transform 0.3s ease;">
    
    <div class="brand-text" style="margin-top: 12px;">
        <div class="brand-name" style="font-size: 17px; font-weight: 700; color: #ffffff; margin: 0 0 6px 0; line-height: 1.4; letter-spacing: -0.01em;">
            Inventory Management Software 
        </div>
        <div class="brand-tagline" style="font-size: 13px; font-weight: 500; color: rgba(255, 255, 255, 0.75); margin: 0; line-height: 1.5; letter-spacing: 0.01em;">
           and Smart Billing System With E-Commerce
        </div>
    </div>
</div>

        <!-- Dashboard -->
          @can('see dashboard')
        <a class="nav-link text-white" href="{{ route('dashboard') }}" class="{{ request()->is('dashboard*') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        @endcan

        <!-- Categories -->
        @can('manage categories')
        <a class="nav-link text-white" href="{{ route('categories.index') }}" class="{{ request()->is('categories*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> Categories
        </a>
        @endcan

        <!-- Product Dropdown -->
        @can('manage products')
        <a class="nav-link text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#productMenu">
            <span><i class="bi bi-box-seam"></i> Product</span>
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="productMenu">
            <a class="nav-link text-white-50 ms-4" href="{{ route('products.index') }}">Product List</a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('adjustments.index') }}">Adjustment List</a>
            @can('create adjustments')
            <a class="nav-link text-white-50 ms-4" href="{{ route('adjustments.create') }}">Add Adjustment</a>
            @endcan
        </div>
        @endcan

        <!-- Purchase -->
        @can('manage purchases')
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#purchaseMenu">
            <i class="bi bi-cart-plus"></i> Purchase
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="purchaseMenu">
            <a class="nav-link text-white-50 ms-4" href="{{ route('purchases.index') }}">Purchase List</a>
            @can('create purchases')
            <a class="nav-link text-white-50 ms-4" href="{{ route('purchases.create') }}">Add Purchase</a>
            @endcan
        </div>
        @endcan

        <!-- Sale -->
        @can('manage sales')
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#saleMenu">
            <i class="bi bi-cart-check"></i> Sale
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="saleMenu">
            <a class="nav-link text-white-50 ms-4" href="{{ route('sales.index') }}">Sale List</a>
            @can('create sales')
            <a class="nav-link text-white-50 ms-4" href="{{ route('sales.create') }}">Add Sale</a>
            @endcan
            @can('create pos')
            <a class="nav-link text-white-50 ms-4" href="{{ route('pos.index') }}">
                <i class="bi bi-shop"></i> POS
            </a>
            @endcan
        </div>
        @endcan

        <!-- Accounting -->
        @can('manage accounting')
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#accountingMenu">
            <i class="bi bi-calculator"></i> Accounting
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="accountingMenu">
            <a class="nav-link text-white-50 ms-4" href="{{ route('accounts.index') }}">Account List</a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('accounts.create') }}">Add Account</a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('accounting.statement') }}">Account Statement</a>
        </div>
        @endcan

        <!-- People -->
        @can('manage people')
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#peopleMenu">
            <i class="bi bi-person-lines-fill"></i> People
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="peopleMenu">
            @can ('manage users')
            <a class="nav-link text-white-50 ms-4" href="{{ route('users.index') }}">User List</a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('users.create') }}">Add User</a>
            @endcan
            @can('manage suppliers and customers')
            <a class="nav-link text-white-50 ms-4" href="{{ route('customers.index') }}">Customer List</a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('customers.create') }}">Add Customer</a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('suppliers.index') }}">Supplier List</a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('suppliers.create') }}">Add Supplier</a>
            @endcan
        </div>
        @endcan

        <!-- HRM -->
        @can('manage hrm')
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#hrmMenu">
            <i class="bi bi-people"></i> HRM
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="hrmMenu">
            @if (Route::has('employees.index'))
                <a class="nav-link text-white-50 ms-4" href="{{ route('employees.index') }}">Employee</a>
            @endif
            @if (Route::has('payrolls.index'))
                <a class="nav-link text-white-50 ms-4" href="{{ route('payrolls.index') }}">Payroll</a>
            @endif
        </div>
        @endcan

        <!-- Reports -->
        @can('view reports')
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#reportsMenu">
            <i class="bi bi-bar-chart"></i> Reports
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="reportsMenu">
            <a class="nav-link text-white-50 ms-4" href="{{ route('reports.products') }}">
                <i class="bi bi-box"></i> Product Report
            </a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('reports.sales') }}">
                <i class="bi bi-cart-check"></i> Sale Report
            </a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('reports.purchases') }}">
                <i class="bi bi-bag"></i> Purchase Report
            </a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('reports.adjustments') }}">
                <i class="bi bi-arrow-left-right"></i> Adjustment Report
            </a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('reports.payments') }}">
                <i class="bi bi-credit-card"></i> Payment Report
            </a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('reports.customers') }}">
                <i class="bi bi-people"></i> Customer Report
            </a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('reports.suppliers') }}">
                <i class="bi bi-truck"></i> Supplier Report
            </a>
        </div>
        @endcan

        <!-- Settings -->
        @can('manage settings')
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#settingsMenu">
            <i class="bi bi-gear"></i> Settings
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="settingsMenu">
            <a class="nav-link text-white-50 ms-4" href="{{ route('user.roles') }}">
                User Role Management
            </a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('roles.index') }}">
                 Role Permission
            </a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('permissions.index') }}">
                Permission Management
            </a>
            <a class="nav-link text-white-50 ms-4" href="{{ route('settings.general') }}">
                 General Setting
            </a>
        </div>
        @endcan

    </div>
    <!-- END Sidebar -->

    <!-- Top Bar -->
    <div class="topbar">
        <div class="d-flex justify-content-end align-items-center">
            <span class="me-3">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-sm btn-danger">Logout</button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div style="margin-top: 60px;">
            @isset($header)
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    {{ $header }}
                </div>
            @endisset

            {{ $slot }}
        </div>
    </div>

    <!-- jQuery (Required for DataTables) - IMPORTANT: Load First -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables Core JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- DataTables Buttons Extension -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    
    <!-- JSZip for Excel Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    
    <!-- PDFMake for PDF Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    
    <!-- Buttons HTML5 Export -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

</body>
</html>