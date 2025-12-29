<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ============ SUMMARY CARDS ============
        
        // Total Sale (from sales table - same as Sale Report)
        $totalSale = DB::selectOne("
            SELECT COALESCE(SUM(grand_total), 0) as total
            FROM sales
            WHERE sale_status = 'completed'
        ")->total;

        // Total Payment (from sales - same as Sale Report)
        $totalPayment = DB::selectOne("
            SELECT COALESCE(SUM(paid_amount), 0) as total
            FROM sales
            WHERE sale_status = 'completed'
        ")->total;

        // Total Due (from sales - same as Sale Report)
        $totalDue = DB::selectOne("
            SELECT COALESCE(SUM(due_amount), 0) as total
            FROM sales
            WHERE sale_status = 'completed'
        ")->total;

        // This Month Sale
        $monthlySale = DB::selectOne("
            SELECT COALESCE(SUM(grand_total), 0) as total
            FROM sales
            WHERE sale_status = 'completed'
            AND MONTH(sale_date) = MONTH(CURRENT_DATE())
            AND YEAR(sale_date) = YEAR(CURRENT_DATE())
        ")->total;

        // ============ CASH FLOW CHART (Last 12 Months) ============
        $months = [];
        $paymentReceived = [];
        $paymentSent = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            // Payment Received (Credit transactions)
            $received = DB::selectOne("
                SELECT COALESCE(SUM(amount), 0) as total
                FROM account_transactions
                WHERE transaction_type = 'credit'
                AND MONTH(transaction_date) = ?
                AND YEAR(transaction_date) = ?
            ", [$date->month, $date->year])->total;
            
            $paymentReceived[] = (float) $received;
            
            // Payment Sent (Debit transactions)
            $sent = DB::selectOne("
                SELECT COALESCE(SUM(amount), 0) as total
                FROM account_transactions
                WHERE transaction_type = 'debit'
                AND MONTH(transaction_date) = ?
                AND YEAR(transaction_date) = ?
            ", [$date->month, $date->year])->total;
            
            $paymentSent[] = (float) $sent;
        }

        // ============ DONUT CHART (This Month) ============
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Purchase (This Month)
        $purchaseThisMonth = DB::selectOne("
            SELECT COALESCE(SUM(grand_total), 0) as total
            FROM purchases
            WHERE MONTH(purchase_date) = ?
            AND YEAR(purchase_date) = ?
        ", [$currentMonth, $currentYear])->total;

        // Revenue (This Month)
        $revenueThisMonth = DB::selectOne("
            SELECT COALESCE(SUM(grand_total), 0) as total
            FROM sales
            WHERE sale_status = 'completed'
            AND MONTH(sale_date) = ?
            AND YEAR(sale_date) = ?
        ", [$currentMonth, $currentYear])->total;

        // Expense (This Month - Debit transactions excluding purchases)
        $expenseThisMonth = DB::selectOne("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM account_transactions
            WHERE transaction_type = 'debit'
            AND reference_type NOT IN ('purchase', 'sale')
            AND MONTH(transaction_date) = ?
            AND YEAR(transaction_date) = ?
        ", [$currentMonth, $currentYear])->total;

        $donutData = [
            (float) $purchaseThisMonth,
            (float) $revenueThisMonth,
            (float) $expenseThisMonth
        ];

        // ============ PIE CHART - Sales by Category ============
        $categoryResults = DB::select("
            SELECT 
                c.name as category_name,
                COALESCE(SUM(si.subtotal), 0) as total_sales
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            LEFT JOIN sale_items si ON si.product_id = p.id
            LEFT JOIN sales s ON s.id = si.sale_id AND s.sale_status = 'completed'
            WHERE MONTH(s.sale_date) = ?
            AND YEAR(s.sale_date) = ?
            GROUP BY c.id, c.name
            ORDER BY total_sales DESC
            LIMIT 5
        ", [$currentMonth, $currentYear]);

        $categoryLabels = [];
        $categoryData = [];
        $totalCategorySales = array_sum(array_column($categoryResults, 'total_sales'));

        foreach ($categoryResults as $cat) {
            $categoryLabels[] = $cat->category_name;
            // Calculate percentage
            $percentage = $totalCategorySales > 0 ? round(($cat->total_sales / $totalCategorySales) * 100, 2) : 0;
            $categoryData[] = $percentage;
        }

        // If no data, show placeholder
        if (empty($categoryLabels)) {
            $categoryLabels = ['No Sales'];
            $categoryData = [100];
        }

        // ============ BAR CHART - Monthly Sales Overview (Last 12 Months) ============
        $monthlySales = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            
            $sales = DB::selectOne("
                SELECT COALESCE(SUM(grand_total), 0) as total
                FROM sales
                WHERE sale_status = 'completed'
                AND MONTH(sale_date) = ?
                AND YEAR(sale_date) = ?
            ", [$date->month, $date->year])->total;
            
            $monthlySales[] = (float) $sales;
        }

        // ============ BEST SELLING PRODUCTS (This Month) ============
        $bestProducts = DB::select("
            SELECT 
                p.name,
                SUM(si.quantity) as qty
            FROM sale_items si
            JOIN products p ON p.id = si.product_id
            JOIN sales s ON s.id = si.sale_id
            WHERE s.sale_status = 'completed'
            AND MONTH(s.sale_date) = ?
            AND YEAR(s.sale_date) = ?
            GROUP BY p.id, p.name
            ORDER BY qty DESC
            LIMIT 10
        ", [$currentMonth, $currentYear]);

        // ============ RECENT TRANSACTIONS ============
        $recent = DB::select("
            SELECT 
                DATE_FORMAT(s.sale_date, '%d-%m-%Y') as date,
                s.reference_number as ref,
                COALESCE(c.name, 'Walk-In Customer') as customer,
                CONCAT('৳', FORMAT(s.grand_total, 2)) as total
            FROM sales s
            LEFT JOIN customers c ON c.id = s.customer_id
            WHERE s.sale_status = 'completed'
            ORDER BY s.sale_date DESC, s.id DESC
            LIMIT 10
        ");

        return view('dashboard', compact(
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
            'recent'
        ));
    }
}