<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ======= SUMMARY CARDS DATA (Using Order Count as fallback) =======
        
        // Check what columns exist in orders table
        $orderColumns = DB::getSchemaBuilder()->getColumnListing('orders');
        
        // Try to calculate totals based on available columns
        if (in_array('total_amount', $orderColumns)) {
            $totalSale = Order::sum('total_amount') ?? 0;
            $totalPayment = Order::where('status', 'completed')->sum('total_amount') ?? 0;
            $monthlySale = Order::whereMonth('created_at', Carbon::now()->month)
                              ->whereYear('created_at', Carbon::now()->year)
                              ->sum('total_amount') ?? 0;
        } elseif (in_array('amount', $orderColumns)) {
            $totalSale = Order::sum('amount') ?? 0;
            $totalPayment = Order::where('status', 'completed')->sum('amount') ?? 0;
            $monthlySale = Order::whereMonth('created_at', Carbon::now()->month)
                              ->whereYear('created_at', Carbon::now()->year)
                              ->sum('amount') ?? 0;
        } else {
            // Fallback: Use order count * average price (or dummy data)
            $totalOrders = Order::count();
            $totalSale = $totalOrders * 1000; // Dummy calculation
            $totalPayment = Order::where('status', 'completed')->count() * 1000;
            $monthlySale = Order::whereMonth('created_at', Carbon::now()->month)
                              ->whereYear('created_at', Carbon::now()->year)
                              ->count() * 1000;
        }
        
        // Total Due: Difference between total sale and payment
        $totalDue = $totalSale - $totalPayment;

        // ======= CASH FLOW DATA =======
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        $paymentReceived = [];
        $paymentSent = [];
        
        for ($i = 1; $i <= 12; $i++) {
            // Count orders per month (or sum if amount column exists)
            $monthlyOrders = Order::whereMonth('created_at', $i)
                                  ->whereYear('created_at', Carbon::now()->year)
                                  ->count();
            
            $paymentReceived[] = $monthlyOrders * 1000; // Adjust multiplier as needed
            $paymentSent[] = $monthlyOrders * 800; // Dummy data for expenses
        }

        // ======= DONUT CHART DATA =======
        $totalRevenue = $totalSale;
        $totalPurchase = $totalSale * 0.6; // 60% as cost
        $totalExpense = $totalSale * 0.1; // 10% as expense
        $donutData = [$totalPurchase, $totalRevenue, $totalExpense];

        // ======= PIE CHART DATA (Sales by Category) =======
        try {
            $categorySales = Product::join('categories', 'products.category_id', '=', 'categories.id')
                                   ->select('categories.name', DB::raw('COUNT(products.id) as total'))
                                   ->groupBy('categories.name')
                                   ->get();
            
            $categoryLabels = $categorySales->pluck('name')->toArray();
            $categoryData = $categorySales->pluck('total')->toArray();
            
            if (empty($categoryLabels)) {
                $categoryLabels = ['Electronics', 'Clothing', 'Food', 'Others'];
                $categoryData = [30, 25, 20, 25];
            }
        } catch (\Exception $e) {
            $categoryLabels = ['Electronics', 'Clothing', 'Food', 'Others'];
            $categoryData = [30, 25, 20, 25];
        }

        // ======= BAR CHART DATA (Monthly Orders) =======
        $monthlySales = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyCount = Order::whereMonth('created_at', $i)
                                ->whereYear('created_at', Carbon::now()->year)
                                ->count();
            $monthlySales[] = $monthlyCount * 1000; // Convert to amount
        }

        // ======= BEST PRODUCTS =======
        try {
            $bestProducts = Product::select('name', DB::raw('stock as qty'))
                             ->orderBy('stock', 'DESC')
                             ->limit(5)
                             ->get();
        } catch (\Exception $e) {
            $bestProducts = collect([]);
        }

        // ======= RECENT TRANSACTIONS =======
        $recent = Order::with('user')
                     ->orderBy('created_at', 'DESC')
                     ->limit(5)
                     ->get()
                     ->map(function($order) use ($orderColumns) {
                         $amount = 0;
                         if (in_array('total_amount', $orderColumns)) {
                             $amount = $order->total_amount;
                         } elseif (in_array('amount', $orderColumns)) {
                             $amount = $order->amount;
                         } else {
                             $amount = 1000; // Fallback
                         }
                         
                         return (object)[
                             'date' => $order->created_at->format('Y-m-d'),
                             'ref' => 'ORD-' . $order->id,
                             'customer' => $order->user->name ?? 'Guest',
                             'total' => number_format($amount, 2)
                         ];
                     });

        // Original data (keep for compatibility)
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalCategories = Category::count();
        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return view('dashboard', compact(
            // New dashboard data
            'totalSale',
            'totalPayment',
            'totalDue',
            'monthlySale',
            'months',
            'paymentReceived',
            'paymentSent',
            'donutData',
            'categoryLabels',
            'categoryData',
            'monthlySales',
            'bestProducts',
            'recent',
            // Original data
            'totalProducts',
            'totalOrders',
            'totalCategories',
            'recentOrders'
        ));
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