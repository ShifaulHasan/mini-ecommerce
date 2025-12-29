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
use App\Http\Controllers\CustomerGroupController;
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
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\PermissionController;

Route::get('/', function () {
    return view('welcome');
});

// ===============================
// Authenticated & Verified Routes
// ===============================
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ===============================
    // Role & Permission Management
    // ===============================
    Route::get('/user-roles', [UserRoleController::class, 'index'])->name('user.roles');
    Route::post('/user-roles/assign', [UserRoleController::class, 'assignRole'])->name('user.roles.assign');
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles/{role}/permissions', [RoleController::class, 'assignPermissions'])->name('roles.permissions.assign');

     Route::middleware(['auth'])->group(function () {

    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');

});

    // Categories & Products
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::get('/products/generate-code', [ProductController::class, 'generateCode'])->name('products.generate-code');

    // Purchases
    Route::resource('purchases', PurchaseController::class);
    Route::get('purchases-product-details/{id}', [PurchaseController::class, 'getProductDetails'])->name('purchases.product-details');
    Route::post('/purchases/payment/store', [PurchasePaymentController::class, 'store'])->name('purchase.payment.store');

    // Adjustments
    Route::resource('adjustments', AdjustmentController::class);
    Route::get('adjustments-get-stock', [AdjustmentController::class, 'getStock'])->name('adjustments.get-stock');

    // Orders
    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

    // Sales
    Route::resource('sales', SaleController::class);
    Route::get('/sales/get-warehouse-products', [SaleController::class, 'getWarehouseProducts'])->name('sales.getWarehouseProducts');

    // POS Routes
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::get('/pos/cart', [POSController::class, 'cart'])->name('pos.cart');
    Route::get('/pos/search-products', [POSController::class, 'searchProducts'])->name('pos.search');
    Route::post('/pos/add-to-cart/{id}', [POSController::class, 'addToCart'])->name('pos.add');
    Route::post('/pos/update-qty', [POSController::class, 'updateQty'])->name('pos.update.qty');
    Route::delete('/pos/remove/{id}', [POSController::class, 'removeItem'])->name('pos.remove');
    Route::post('/pos/clear', [POSController::class, 'clearCart'])->name('pos.clear');
    Route::post('/pos/set-customer', [POSController::class, 'setCustomer'])->name('pos.set.customer');
    Route::post('/pos/store', [POSController::class, 'store'])->name('pos.store');
    Route::post('/complete-sale', [POSController::class, 'store'])->name('pos.complete-sale');

    // Sale Returns
    Route::get('/sale-returns/get-items/{sale}', [SaleReturnController::class, 'getSaleItems'])->name('sale-returns.get-items');
    Route::resource('sale-returns', SaleReturnController::class);

    // Account Management Routes
    Route::resource('accounts', AccountController::class);
    Route::post('accounts/{account}/toggle-default', [AccountController::class, 'toggleDefault'])->name('accounts.toggle-default');
    Route::patch('/accounts/{account}/toggle-default', [AccountController::class, 'toggleDefault'])->name('accounts.toggle-default');
    Route::post('accounts/bulk-delete', [AccountController::class, 'bulkDelete'])->name('accounts.bulk-delete');
    Route::get('accounts/export/csv', [AccountController::class, 'exportCSV'])->name('accounts.export.csv');

    // Money Transfer Routes
    Route::resource('money-transfers', MoneyTransferController::class);

    // Accounting Reports
    Route::get('/balance-sheet', [AccountingReportController::class, 'balanceSheet'])->name('accounting.balance-sheet');
    Route::get('/account-statement', [AccountingReportController::class, 'accountStatement'])->name('accounting.statement');

    // User Management Routes
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
    Route::get('customers/{customer}/payment', [CustomerController::class, 'showPaymentForm'])->name('customers.payment');
    Route::post('customers/{customer}/payment', [CustomerController::class, 'processPayment'])->name('customers.payment.process');
    Route::get('customers/{customer}/ledger', [CustomerController::class, 'ledger'])->name('customers.ledger');

    // Customer Group Routes
    Route::resource('customer-groups', CustomerGroupController::class);

    // Supplier Routes
    Route::get('/supplier-payment/{supplier}', [SupplierController::class, 'addPayment'])->name('supplier.payment.form');
    Route::post('/supplier-payment/{supplier}', [SupplierController::class, 'storePayment'])->name('supplier.payment.store');
    Route::get('/supplier-due-report/{supplier}', [SupplierController::class, 'dueReport'])->name('supplier.due.report');
    Route::resource('suppliers', SupplierController::class);
    Route::get('/suppliers/get-all', [SupplierController::class, 'getAllSuppliers'])->name('suppliers.get-all');

    // HRM Routes
    Route::resource('departments', DepartmentController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('attendances', AttendanceController::class);
    Route::resource('payrolls', PayrollController::class);

    // Report Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/products', [ReportController::class, 'productReport'])->name('products');
        Route::get('/sales', [ReportController::class, 'saleReport'])->name('sales');
        Route::get('/purchases', [ReportController::class, 'purchaseReport'])->name('purchases');
        Route::get('/adjustments', [ReportController::class, 'adjustmentReport'])->name('adjustments');
        Route::get('/payments', [ReportController::class, 'paymentReport'])->name('payments');
        Route::get('/customers', [ReportController::class, 'customerReport'])->name('customers');
        Route::get('/suppliers', [ReportController::class, 'supplierReport'])->name('suppliers');
    });

    // Settings Routes
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