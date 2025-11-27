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
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="text-center fw-semibold px-3 mb-4" 
    style="line-height:1.4; font-size:16px; color:#e4e6eb;">
    Inventory Management Software And <br>
    <span style="color:#c7c9d1;">Smart Billing System with E-Commerce</span>
</h4>


        <a href="{{ route('dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="{{ route('categories.index') }}" class="{{ request()->is('categories*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> Categories
        </a>

        <a href="{{ route('products.index') }}" class="{{ request()->is('products*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Products
        </a>

        <a href="{{ route('orders.index') }}" class="{{ request()->is('orders*') ? 'active' : '' }}">
            <i class="bi bi-cart-check"></i> Orders
        </a>

        <!-- ----------------------
            Purchase
        ------------------------ -->
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#purchaseMenu">
            <i class="bi bi-cart-plus"></i> Purchase
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="purchaseMenu">
            <a class="nav-link text-white-50 ms-4" href="#">Purchase List</a>
            <a class="nav-link text-white-50 ms-4" href="#">Add Purchase</a>
        </div>

        <!-- ----------------------
            Sale
        ------------------------ -->
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#saleMenu">
            <i class="bi bi-cart-check"></i> Sale
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="saleMenu">
            <a class="nav-link text-white-50 ms-4" href="#">Sale List</a>
            <a class="nav-link text-white-50 ms-4" href="#">Add Sale</a>
            <a class="nav-link text-white-50 ms-4" href="#">POS</a>
        </div>

        <!-- ----------------------
            Return
        ------------------------ -->
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#returnMenu">
            <i class="bi bi-arrow-return-left"></i> Return
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="returnMenu">
            <a class="nav-link text-white-50 ms-4" href="#">Sale Return</a>
        </div>

        <!-- ----------------------
            Accounting
        ------------------------ -->
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#accountingMenu">
            <i class="bi bi-calculator"></i> Accounting
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="accountingMenu">
            <a class="nav-link text-white-50 ms-4" href="#">Account List</a>
            <a class="nav-link text-white-50 ms-4" href="#">Add Account</a>
            <a class="nav-link text-white-50 ms-4" href="#">Money Transfer</a>
            <a class="nav-link text-white-50 ms-4" href="#">Balance Sheet</a>
            <a class="nav-link text-white-50 ms-4" href="#">Account Statement</a>
        </div>

        <!-- ----------------------
            HRM
        ------------------------ -->
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#hrmMenu">
            <i class="bi bi-people"></i> HRM
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="hrmMenu">
            <a class="nav-link text-white-50 ms-4" href="#">Department</a>
            <a class="nav-link text-white-50 ms-4" href="#">Employee</a>
            <a class="nav-link text-white-50 ms-4" href="#">Attendance</a>
            <a class="nav-link text-white-50 ms-4" href="#">Payroll</a>
        </div>

        <!-- ----------------------
            People
        ------------------------ -->
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#peopleMenu">
            <i class="bi bi-person-lines-fill"></i> People
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="peopleMenu">
            <a class="nav-link text-white-50 ms-4" href="#">User List</a>
            <a class="nav-link text-white-50 ms-4" href="#">Add User</a>
            <a class="nav-link text-white-50 ms-4" href="#">Customer List</a>
            <a class="nav-link text-white-50 ms-4" href="#">Add Customer</a>
            <a class="nav-link text-white-50 ms-4" href="#">Supplier List</a>
            <a class="nav-link text-white-50 ms-4" href="#">Add Supplier</a>
        </div>

        <!-- ----------------------
            Reports
        ------------------------ -->
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#reportsMenu">
            <i class="bi bi-bar-chart"></i> Reports
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="reportsMenu">
            <a class="nav-link text-white-50 ms-4" href="#">Product Report</a>
            <a class="nav-link text-white-50 ms-4" href="#">Sale Report</a>
            <a class="nav-link text-white-50 ms-4" href="#">Payment Report</a>
        </div>

        <!-- ----------------------
            Settings
        ------------------------ -->
        <a class="nav-link text-white" data-bs-toggle="collapse" href="#settingsMenu">
            <i class="bi bi-gear"></i> Settings
            <i class="bi bi-chevron-down float-end"></i>
        </a>
        <div class="collapse" id="settingsMenu">
            <a class="nav-link text-white-50 ms-4" href="#">Role Permission</a>
            <a class="nav-link text-white-50 ms-4" href="#">General Setting</a>
            <a class="nav-link text-white-50 ms-4" href="#">Mail Setting</a>
            <a class="nav-link text-white-50 ms-4" href="#">SMS Setting</a>
            <a class="nav-link text-white-50 ms-4" href="#">POS Setting</a>
            <a class="nav-link text-white-50 ms-4" href="#">E-commerce Setting</a>
        </div>

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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
