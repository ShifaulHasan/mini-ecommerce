<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Admin Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            overflow-x: hidden;
        }
        .sidebar {
            height: 100vh;
            width: 230px;
            position: fixed;
            left: 0;
            top: 0;
            background: #1f2937;
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            color: #cbd5e1;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            font-size: 16px;
        }
        .sidebar a:hover {
            background: #4b5563;
            color: white;
        }
        .content {
            margin-left: 230px;
            padding: 20px;
            background: #f3f4f6;
            min-height: 100vh;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="text-center mb-4">Admin Panel</h4>

        <a href="{{ route('dashboard') }}">Dashboard</a>
        <a href="{{ route('categories.index') }}">Categories</a>
        <a href="{{ route('products.index') }}">Products</a>
        <a href="{{ route('orders.index') }}">Orders</a>
        <a href="{{ route('profile.edit') }}">Profile</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        @yield('content')
    </div>

</body>
</html>
