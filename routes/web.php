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
use App\Http\Controllers\UserController;


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
 // Sales Routes
    Route::resource('sales', SaleController::class);
    Route::get('/sales/get-warehouse-products', [SaleController::class, 'getWarehouseProducts'])->name('sales.getWarehouseProducts');
    // POS Routes

// POS Routes
Route::middleware(['auth'])->group(function () {

    // POS main screen
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');

    // Get current cart (AJAX)
    Route::get('/pos/cart', [POSController::class, 'cart'])->name('pos.cart');

    // Product search (AJAX)
    Route::get('/pos/search-products', [POSController::class, 'searchProducts'])->name('pos.search');

    // Cart actions
    Route::post('/pos/add-to-cart/{id}', [POSController::class, 'addToCart'])->name('pos.add');
    Route::post('/pos/update-qty', [POSController::class, 'updateQty'])->name('pos.update.qty');
    Route::delete('/pos/remove/{id}', [POSController::class, 'removeItem'])->name('pos.remove');
    Route::post('/pos/clear', [POSController::class, 'clearCart'])->name('pos.clear');

    // Set customer for current sale
    Route::post('/pos/set-customer', [POSController::class, 'setCustomer'])->name('pos.set.customer');

    // Complete checkout / store sale
    Route::post('/pos/store', [POSController::class, 'store'])->name('pos.store');
    Route::post('/complete-sale', [POSController::class, 'store'])->name('pos.complete-sale');
});



    // Sale Returns
    Route::get('/sale-returns/get-items/{sale}', [SaleReturnController::class, 'getSaleItems'])
         ->name('sale-returns.get-items');
    Route::resource('sale-returns', SaleReturnController::class);

    

    // Account Management Routes
Route::resource('accounts', AccountController::class);

// Additional custom routes for accounts 
Route::post('accounts/{account}/toggle-default', [AccountController::class, 'toggleDefault'])->name('accounts.toggle-default');
Route::post('accounts/bulk-delete', [AccountController::class, 'bulkDelete'])->name('accounts.bulk-delete');
Route::get('accounts/export/csv', [AccountController::class, 'exportCSV'])->name('accounts.export.csv');
//account routes
Route::patch('/accounts/{account}/toggle-default', [AccountController::class, 'toggleDefault'])
    ->name('accounts.toggle-default');

// Money Transfer Routes
Route::resource('money-transfers', MoneyTransferController::class);

// Accounting Reports
Route::get('/balance-sheet', [AccountingReportController::class, 'balanceSheet'])
     ->name('accounting.balance-sheet');
Route::get('/account-statement', [AccountingReportController::class, 'accountStatement'])
     ->name('accounting.statement');



   // User Management Routes
Route::middleware(['auth'])->group(function () {
    // User Management Routes - PUT THESE FIRST
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::post('/users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    
     // Customer Routes
    Route::resource('customers', CustomerController::class);
    
    // Biller Routes
    Route::resource('billers', BillerController::class);
    
    // Supplier Routes
    Route::resource('suppliers', SupplierController::class);
    
    // other routes
});


    // HRM
    Route::resource('departments', DepartmentController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('attendances', AttendanceController::class);
    Route::resource('payrolls', PayrollController::class);

    // // Users, Customers, Suppliers
    // Route::resource('users', UserManagementController::class);
    // Route::resource('customers', CustomerController::class);
    // Route::resource('suppliers', SupplierController::class);

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