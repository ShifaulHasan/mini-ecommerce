<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchasePaymentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\MoneyTransferController;
use App\Http\Controllers\AccountingReportController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AdjustmentController;

Route::get('/', function () {
    return view('welcome');
});


// ===============================
// Authenticated & Verified Routes
// ===============================
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Categories & Products
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::get('/products/generate-code', [ProductController::class, 'generateCode'])->name('products.generate-code');

    // Purchases
    Route::resource('purchases', PurchaseController::class);

    // AJAX product details for purchase
    Route::get('purchases-product-details/{id}', 
        [PurchaseController::class, 'getProductDetails']
    )->name('purchases.product-details');

    // Purchase Payments (NEW FIXED ROUTE)
    Route::post('/purchases/payment/store', 
        [PurchasePaymentController::class, 'store']
    )->name('purchase.payment.store');


    // Adjustments
    Route::resource('adjustments', AdjustmentController::class);
    Route::get('adjustments-get-stock', [AdjustmentController::class, 'getStock'])
        ->name('adjustments.get-stock');

    // Orders
    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])
         ->name('orders.updateStatus');

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

    // Sales
    Route::resource('sales', SaleController::class);
    Route::get('/sales/get-warehouse-products', [SaleController::class, 'getWarehouseProducts'])
         ->name('sales.get-warehouse-products');

    // POS
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/pos/add-to-cart', [POSController::class, 'addToCart'])->name('pos.addToCart');
    Route::post('/pos/complete-sale', [POSController::class, 'completeSale'])->name('pos.completeSale');

    // Sale Returns
    Route::get('/sale-returns/get-items/{sale}', [SaleReturnController::class, 'getSaleItems'])
         ->name('sale-returns.get-items');
    Route::resource('sale-returns', SaleReturnController::class);

    // Accounting
    Route::resource('accounts', AccountController::class);
    Route::resource('money-transfers', MoneyTransferController::class);

    Route::get('/balance-sheet', [AccountingReportController::class, 'balanceSheet'])
         ->name('accounting.balance-sheet');
    Route::get('/account-statement', [AccountingReportController::class, 'accountStatement'])
         ->name('accounting.statement');

    // HRM
    Route::resource('departments', DepartmentController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('attendances', AttendanceController::class);
    Route::resource('payrolls', PayrollController::class);

    // Users, Customers, Suppliers
    Route::resource('users', UserManagementController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('suppliers', SupplierController::class);

    // Reports
    Route::get('/reports/products', [ReportController::class, 'productReport'])->name('reports.products');
    Route::get('/reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/payments', [ReportController::class, 'paymentReport'])->name('reports.payments');

    // Settings
    Route::get('/settings/roles', [SettingController::class, 'roles'])->name('settings.roles');
    Route::get('/settings/general', [SettingController::class, 'general'])->name('settings.general');
    Route::get('/settings/mail', [SettingController::class, 'mail'])->name('settings.mail');
    Route::get('/settings/sms', [SettingController::class, 'sms'])->name('settings.sms');
    Route::get('/settings/pos', [SettingController::class, 'pos'])->name('settings.pos');
    Route::get('/settings/ecommerce', [SettingController::class, 'ecommerce'])->name('settings.ecommerce');
    Route::post('/settings/update', [SettingController::class, 'update'])->name('settings.update');
});


// ===============================
// Profile Routes
// ===============================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
