<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalCategories = Category::count();
        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return view('dashboard', compact('totalProducts', 'totalOrders', 'totalCategories', 'recentOrders'));
    }
}