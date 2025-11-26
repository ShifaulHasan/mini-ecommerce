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
            color: #fff;
            padding-top: 70px;
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
        <h4 class="text-center text-white">E-commerce</h4>

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
    </div>

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
            
            <!-- Page Header -->
            @isset($header)
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    {{ $header }}
                </div>
            @endisset

            <!-- Page Content -->
            {{ $slot }}
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
