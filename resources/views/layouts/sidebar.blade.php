<!-- Sidebar -->
<div class="sidebar bg-dark" style="width: 250px; min-height: 100vh; position: fixed; left: 0; top: 0; overflow-y: auto;">
    <div class="text-white p-3">
         Inventory Management Software And <br>
    <span style="color:#c7c9d1;">Smart Billing System with E-Commerce</span>
        
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="sidebar-link d-block text-white text-decoration-none p-2 mb-1 rounded">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <!-- Inventory -->
        <div class="sidebar-dropdown mb-1">
            <a class="sidebar-link d-flex justify-content-between align-items-center text-white text-decoration-none p-2 rounded" data-bs-toggle="collapse" href="#inventoryMenu">
                <span><i class="bi bi-box-seam"></i> Inventory</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse" id="inventoryMenu">
                <a href="{{ route('categories.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Category List</a>
                <a href="{{ route('products.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Product List</a>
                <a href="{{ route('products.create') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Add Product</a>
            </div>
        </div>

        <!-- Purchase -->
        <div class="sidebar-dropdown mb-1">
            <a class="sidebar-link d-flex justify-content-between align-items-center text-white text-decoration-none p-2 rounded" data-bs-toggle="collapse" href="#purchaseMenu">
                <span><i class="bi bi-cart-plus"></i> Purchase</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse" id="purchaseMenu">
                <a href="{{ route('purchases.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Purchase List</a>
                <a href="{{ route('purchases.create') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Add Purchase</a>
            </div>
        </div>

        <!-- Sale -->
        <div class="sidebar-dropdown mb-1">
            <a class="sidebar-link d-flex justify-content-between align-items-center text-white text-decoration-none p-2 rounded" data-bs-toggle="collapse" href="#saleMenu">
                <span><i class="bi bi-cart-check"></i> Sale</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse" id="saleMenu">
                <a href="{{ route('sales.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Sale List</a>
                <a href="{{ route('sales.create') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Add Sale</a>
                <a href="{{ route('pos.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">POS</a>
            </div>
        </div>

        <!-- Return -->
        <div class="sidebar-dropdown mb-1">
            <a class="sidebar-link d-flex justify-content-between align-items-center text-white text-decoration-none p-2 rounded" data-bs-toggle="collapse" href="#returnMenu">
                <span><i class="bi bi-arrow-return-left"></i> Return</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse" id="returnMenu">
                <a href="{{ route('sale-returns.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Sale Return</a>
            </div>
        </div>

        <!-- Accounting -->
        <div class="sidebar-dropdown mb-1">
            <a class="sidebar-link d-flex justify-content-between align-items-center text-white text-decoration-none p-2 rounded" data-bs-toggle="collapse" href="#accountingMenu">
                <span><i class="bi bi-cash-stack"></i> Accounting</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse" id="accountingMenu">
                <a href="{{ route('accounts.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Account List</a>
                <a href="{{ route('accounts.create') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Add Account</a>
                <a href="{{ route('money-transfers.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Money Transfer</a>
                <a href="{{ route('accounting.balance-sheet') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Balance Sheet</a>
                <a href="{{ route('accounting.statement') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Account Statement</a>
            </div>
        </div>

        <!-- HRM -->
        <div class="sidebar-dropdown mb-1">
            <a class="sidebar-link d-flex justify-content-between align-items-center text-white text-decoration-none p-2 rounded" data-bs-toggle="collapse" href="#hrmMenu">
                <span><i class="bi bi-people"></i> HRM</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse" id="hrmMenu">
                <a href="{{ route('departments.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Department</a>
                <a href="{{ route('employees.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Employee</a>
                <a href="{{ route('attendances.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Attendance</a>
                <a href="{{ route('payrolls.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Payroll</a>
            </div>
        </div>

        <!-- People -->
        <div class="sidebar-dropdown mb-1">
            <a class="sidebar-link d-flex justify-content-between align-items-center text-white text-decoration-none p-2 rounded" data-bs-toggle="collapse" href="#peopleMenu">
                <span><i class="bi bi-person-badge"></i> People</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse" id="peopleMenu">
                <a href="{{ route('users.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">User List</a>
                <a href="{{ route('users.create') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Add User</a>
                <a href="{{ route('customers.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Customer List</a>
                <a href="{{ route('customers.create') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Add Customer</a>
                <a href="{{ route('suppliers.index') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Supplier List</a>
                <a href="{{ route('suppliers.create') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Add Supplier</a>
            </div>
        </div>

        <!-- Reports -->
        <div class="sidebar-dropdown mb-1">
            <a class="sidebar-link d-flex justify-content-between align-items-center text-white text-decoration-none p-2 rounded" data-bs-toggle="collapse" href="#reportsMenu">
                <span><i class="bi bi-graph-up"></i> Reports</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse" id="reportsMenu">
                <a href="{{ route('reports.products') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Product Report</a>
                <a href="{{ route('reports.sales') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Sale Report</a>
                <a href="{{ route('reports.payments') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Payment Report</a>
            </div>
        </div>

        <!-- Settings -->
        <div class="sidebar-dropdown mb-1">
            <a class="sidebar-link d-flex justify-content-between align-items-center text-white text-decoration-none p-2 rounded" data-bs-toggle="collapse" href="#settingsMenu">
                <span><i class="bi bi-gear"></i> Settings</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse" id="settingsMenu">
                <a href="{{ route('settings.roles') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Role Permission</a>
                <a href="{{ route('settings.general') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">General Setting</a>
                <a href="{{ route('settings.mail') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">Mail Setting</a>
                <a href="{{ route('settings.sms') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">SMS Setting</a>
                <a href="{{ route('settings.pos') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">POS Setting</a>
                <a href="{{ route('settings.ecommerce') }}" class="sidebar-sublink d-block text-white-50 text-decoration-none ps-4 p-2">E-commerce Setting</a>
            </div>
        </div>
    </div>
</div>

<style>
.sidebar-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar-sublink:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

.sidebar {
    z-index: 1000;
}
</style>