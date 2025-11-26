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
   public function ajaxSearchProducts(Request $request)
{
    $query = $request->input('query');

    $products = Product::with('category')
        ->where('name', 'like', "%{$query}%")
        ->orWhereHas('category', function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })
        ->get();

    // Return JSON for AJAX
    return response()->json($products);
}


}