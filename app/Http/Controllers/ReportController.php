<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // Product Report
    public function productReport(Request $request)
    {
        $query = "
            SELECT 
                p.id,
                p.name,
                p.product_code,
                c.name as category_name,
                b.name as brand_name,
                p.cost_price,
                p.price as selling_price,
                p.stock as current_stock,
                (p.price - p.cost_price) as profit_margin
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN brands b ON b.id = p.brand_id
            WHERE p.status = 'active'
        ";
        
        $params = [];
        if ($request->filled('category_id')) {
            $query .= " AND p.category_id = ?";
            $params[] = $request->category_id;
        }
        if ($request->filled('brand_id')) {
            $query .= " AND p.brand_id = ?";
            $params[] = $request->brand_id;
        }
        
        $query .= " ORDER BY p.name ASC";
        $products = DB::select($query, $params);
        
        $categories = DB::select("SELECT id, name FROM categories ORDER BY name");
        $brands = DB::select("SELECT id, name FROM brands ORDER BY name");
        
        return view('reports.products', compact('products', 'categories', 'brands'));
    }

    // Sale Report
    public function saleReport(Request $request)
    {
        $query = "
            SELECT 
                s.id,
                s.reference_number,
                s.sale_date,
                COALESCE(c.name, 'Walk-In Customer') as customer_name,
                s.grand_total,
                s.tax_amount,
                s.discount_amount,
                s.paid_amount,
                s.due_amount,
                s.sale_status,
                s.payment_status,
                w.name as warehouse_name
            FROM sales s
            LEFT JOIN customers c ON c.id = s.customer_id
            LEFT JOIN warehouses w ON w.id = s.warehouse_id
            WHERE 1=1
        ";
        
        $params = [];
        if ($request->filled('start_date')) {
            $query .= " AND s.sale_date >= ?";
            $params[] = $request->start_date;
        }
        if ($request->filled('end_date')) {
            $query .= " AND s.sale_date <= ?";
            $params[] = $request->end_date;
        }
        if ($request->filled('customer_id')) {
            $query .= " AND s.customer_id = ?";
            $params[] = $request->customer_id;
        }
        if ($request->filled('payment_status')) {
            $query .= " AND s.payment_status = ?";
            $params[] = $request->payment_status;
        }

        $query .= " ORDER BY s.sale_date DESC, s.id DESC";
        $sales = DB::select($query, $params);

        // Calculate totals
        $totals = [
            'total_sales' => array_sum(array_column($sales, 'grand_total')),
            'total_tax' => array_sum(array_column($sales, 'tax_amount')),
            'total_discount' => array_sum(array_column($sales, 'discount_amount')),
            'total_paid' => array_sum(array_column($sales, 'paid_amount')),
            'total_due' => array_sum(array_column($sales, 'due_amount'))
        ];

        $customers = DB::select("SELECT id, name FROM customers WHERE status = 'active' ORDER BY name");
        
        return view('reports.sales', compact('sales', 'totals', 'customers'));
    }

    // Purchase Report
    public function purchaseReport(Request $request)
    {
        $query = "
            SELECT 
                p.id,
                p.reference_no,
                p.purchase_date,
                s.name as supplier_name,
                p.grand_total,
                p.paid_amount,
                p.due_amount,
                p.purchase_status,
                p.payment_status,
                w.name as warehouse_name
            FROM purchases p
            LEFT JOIN suppliers s ON s.id = p.supplier_id
            LEFT JOIN warehouses w ON w.id = p.warehouse_id
            WHERE 1=1
        ";
        
        $params = [];
        if ($request->filled('start_date')) {
            $query .= " AND p.purchase_date >= ?";
            $params[] = $request->start_date;
        }
        if ($request->filled('end_date')) {
            $query .= " AND p.purchase_date <= ?";
            $params[] = $request->end_date;
        }
        if ($request->filled('supplier_id')) {
            $query .= " AND p.supplier_id = ?";
            $params[] = $request->supplier_id;
        }
        if ($request->filled('payment_status')) {
            $query .= " AND p.payment_status = ?";
            $params[] = $request->payment_status;
        }

        $query .= " ORDER BY p.purchase_date DESC, p.id DESC";
        $purchases = DB::select($query, $params);

        $totals = [
            'total_purchases' => array_sum(array_column($purchases, 'grand_total')),
            'total_paid' => array_sum(array_column($purchases, 'paid_amount')),
            'total_due' => array_sum(array_column($purchases, 'due_amount'))
        ];

        $suppliers = DB::select("SELECT id, name FROM suppliers WHERE status = 'active' ORDER BY name");
        
        return view('reports.purchases', compact('purchases', 'totals', 'suppliers'));
    }

    // Adjustment Report
    public function adjustmentReport(Request $request)
    {
        $query = "
            SELECT 
                a.id,
                a.created_at as adjustment_date,
                p.name as product_name,
                p.product_code,
                w.name as warehouse_name,
                a.adjustment_type,
                a.quantity,
                a.current_stock,
                a.new_stock,
                a.reason,
                u.name as created_by_name
            FROM adjustments a
            JOIN products p ON p.id = a.product_id
            JOIN warehouses w ON w.id = a.warehouse_id
            LEFT JOIN users u ON u.id = a.created_by
            WHERE 1=1
        ";
        
        $params = [];
        if ($request->filled('start_date')) {
            $query .= " AND DATE(a.created_at) >= ?";
            $params[] = $request->start_date;
        }
        if ($request->filled('end_date')) {
            $query .= " AND DATE(a.created_at) <= ?";
            $params[] = $request->end_date;
        }
        if ($request->filled('product_id')) {
            $query .= " AND a.product_id = ?";
            $params[] = $request->product_id;
        }
        if ($request->filled('adjustment_type')) {
            $query .= " AND a.adjustment_type = ?";
            $params[] = $request->adjustment_type;
        }

        $query .= " ORDER BY a.created_at DESC";
        $adjustments = DB::select($query, $params);

        $products = DB::select("SELECT id, name FROM products WHERE status = 'active' ORDER BY name");
        
        return view('reports.adjustments', compact('adjustments', 'products'));
    }

    // Payment Report - FIXED VERSION
    public function paymentReport(Request $request)
    {
        $query = "
            SELECT 
                at.id,
                at.transaction_date,
                at.reference_type,
                at.reference_id,
                at.transaction_type,
                at.amount,
                at.payment_method,
                at.description,
                a.name as account_name,
                u.name as created_by_name,
                CASE 
                    WHEN at.reference_type = 'sale' THEN s.payment_method
                    WHEN at.reference_type = 'purchase' THEN p.payment_method
                    ELSE at.payment_method
                END as actual_payment_method
            FROM account_transactions at
            JOIN accounts a ON a.id = at.account_id
            LEFT JOIN users u ON u.id = at.created_by
            LEFT JOIN sales s ON s.id = at.reference_id AND at.reference_type = 'sale'
            LEFT JOIN purchases p ON p.id = at.reference_id AND at.reference_type = 'purchase'
            WHERE 1=1
        ";
        
        $params = [];
        if ($request->filled('start_date')) {
            $query .= " AND at.transaction_date >= ?";
            $params[] = $request->start_date;
        }
        if ($request->filled('end_date')) {
            $query .= " AND at.transaction_date <= ?";
            $params[] = $request->end_date;
        }
        if ($request->filled('account_id')) {
            $query .= " AND at.account_id = ?";
            $params[] = $request->account_id;
        }
        if ($request->filled('transaction_type')) {
            $query .= " AND at.transaction_type = ?";
            $params[] = $request->transaction_type;
        }

        $query .= " ORDER BY at.transaction_date DESC, at.id DESC";
        $payments = DB::select($query, $params);

        $totalCredit = 0;
        $totalDebit = 0;
        foreach ($payments as $payment) {
            if ($payment->transaction_type === 'credit') {
                $totalCredit += $payment->amount;
            } else {
                $totalDebit += $payment->amount;
            }
        }

        $totals = [
            'total_credit' => $totalCredit,
            'total_debit' => $totalDebit,
            'net_balance' => $totalCredit - $totalDebit
        ];

        $accounts = DB::select("SELECT id, name FROM accounts WHERE status = 'active' ORDER BY name");
        
        return view('reports.payments', compact('payments', 'totals', 'accounts'));
    }

    // Customer Report
    public function customerReport(Request $request)
    {
        $query = "
            SELECT 
                c.id,
                c.customer_code,
                c.name,
                c.email,
                c.phone,
                c.city,
                COALESCE(SUM(s.grand_total), 0) as total_sales,
                COALESCE(SUM(s.paid_amount), 0) as total_paid,
                COALESCE(SUM(s.due_amount), 0) as total_due,
                COUNT(s.id) as total_orders
            FROM customers c
            LEFT JOIN sales s ON s.customer_id = c.id
            WHERE c.status = 'active'
        ";
        
        $params = [];
        if ($request->filled('search')) {
            $query .= " AND (c.name LIKE ? OR c.customer_code LIKE ? OR c.phone LIKE ?)";
            $search = '%' . $request->search . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $query .= " GROUP BY c.id, c.customer_code, c.name, c.email, c.phone, c.city";
        $query .= " ORDER BY total_sales DESC";
        $customers = DB::select($query, $params);

        $totals = [
            'total_sales' => array_sum(array_column($customers, 'total_sales')),
            'total_paid' => array_sum(array_column($customers, 'total_paid')),
            'total_due' => array_sum(array_column($customers, 'total_due'))
        ];

        return view('reports.customers', compact('customers', 'totals'));
    }

    // Supplier Report
    public function supplierReport(Request $request)
    {
        $query = "
            SELECT 
                s.id,
                s.name,
                s.email,
                s.phone,
                s.city,
                s.company,
                COALESCE(SUM(p.grand_total), 0) as total_purchases,
                COALESCE(SUM(p.paid_amount), 0) as total_paid,
                COALESCE(SUM(p.due_amount), 0) as total_due,
                COUNT(p.id) as total_orders
            FROM suppliers s
            LEFT JOIN purchases p ON p.supplier_id = s.id
            WHERE s.status = 'active'
        ";
        
        $params = [];
        if ($request->filled('search')) {
            $query .= " AND (s.name LIKE ? OR s.company LIKE ? OR s.phone LIKE ?)";
            $search = '%' . $request->search . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $query .= " GROUP BY s.id, s.name, s.email, s.phone, s.city, s.company";
        $query .= " ORDER BY total_purchases DESC";
        $suppliers = DB::select($query, $params);

        $totals = [
            'total_purchases' => array_sum(array_column($suppliers, 'total_purchases')),
            'total_paid' => array_sum(array_column($suppliers, 'total_paid')),
            'total_due' => array_sum(array_column($suppliers, 'total_due'))
        ];

        return view('reports.suppliers', compact('suppliers', 'totals'));
    }

    // Profit Report
    public function profitReport(Request $request)
    {
        // Build date filter for sales
        $dateConditions = [];
        $params = [];
        
        if ($request->filled('start_date')) {
            $dateConditions[] = "s.sale_date >= ?";
            $params[] = $request->start_date;
        }
        
        if ($request->filled('end_date')) {
            $dateConditions[] = "s.sale_date <= ?";
            $params[] = $request->end_date;
        }
        
        $dateFilter = !empty($dateConditions) ? 'AND ' . implode(' AND ', $dateConditions) : '';

        $query = "
            SELECT 
                p.id as product_id,
                p.product_code,
                p.name as product_name,
                p.stock as current_stock,
                
                -- Purchase Data
                COALESCE(purchase_data.purchase_qty, 0) as purchase_qty,
                COALESCE(purchase_data.purchase_amount, 0) as purchase_amount,
                COALESCE(purchase_data.avg_cost, 0) as avg_cost,
                
                -- Sales Data
                COALESCE(sale_data.sold_qty, 0) as sold_qty,
                COALESCE(sale_data.sales_amount, 0) as sales_amount,
                
                -- Correct Profit Calculation (Sales - Cost of Goods Sold)
                COALESCE(sale_data.sales_amount, 0) - (COALESCE(sale_data.sold_qty, 0) * COALESCE(purchase_data.avg_cost, 0)) as profit
                
            FROM products p
            
            -- Purchase Subquery with Average Cost
            LEFT JOIN (
                SELECT 
                    pi.product_id,
                    SUM(pi.quantity) as purchase_qty,
                    SUM(pi.quantity * pi.cost_price) as purchase_amount,
                    SUM(pi.quantity * pi.cost_price) / NULLIF(SUM(pi.quantity), 0) as avg_cost
                FROM purchase_items pi
                JOIN purchases pur ON pur.id = pi.purchase_id
                WHERE pur.purchase_status IN ('completed', 'received')
                GROUP BY pi.product_id
            ) as purchase_data ON purchase_data.product_id = p.id
            
            -- Sales Subquery with Date Filter
            LEFT JOIN (
                SELECT 
                    si.product_id,
                    SUM(si.quantity) as sold_qty,
                    SUM(si.subtotal) as sales_amount
                FROM sale_items si
                JOIN sales s ON s.id = si.sale_id
                WHERE s.sale_status = 'completed'
                $dateFilter
                GROUP BY si.product_id
            ) as sale_data ON sale_data.product_id = p.id
            
            WHERE p.status = 'active'
        ";

        // Product Filter
        if ($request->filled('product_id')) {
            $query .= " AND p.id = ?";
            $params[] = $request->product_id;
        }

        $query .= " 
            HAVING purchase_qty > 0 OR sold_qty > 0
            ORDER BY profit DESC
        ";

        $profits = DB::select($query, $params);

        // Calculate Totals
        $totals = [
            'purchase_amount' => array_sum(array_column($profits, 'purchase_amount')),
            'sales_amount' => array_sum(array_column($profits, 'sales_amount')),
            'profit' => array_sum(array_column($profits, 'profit')),
        ];

        $products = DB::select("SELECT id, name FROM products WHERE status = 'active' ORDER BY name");

        return view('reports.profit', compact('profits', 'totals', 'products'));
    }
}