<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StockRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;

// Authentication (accounts are created by an admin, not self-service)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public customer portal — token-authenticated, no login required.
Route::get('/p/{token}', [\App\Http\Controllers\PortalController::class, 'show'])->name('portal.show');
Route::post('/p/{token}/proofs/{proof}/approve', [\App\Http\Controllers\PortalController::class, 'approveProof'])->name('portal.proofs.approve');
Route::post('/p/{token}/proofs/{proof}/reject', [\App\Http\Controllers\PortalController::class, 'rejectProof'])->name('portal.proofs.reject');

// Mock marketplace API — public, simulates an external Shopee/Lazada/TikTok host.
Route::get('/mock-api/{platform}/orders', [\App\Http\Controllers\MockApiController::class, 'orders'])
    ->name('mock-api.orders');

// Redirect root to dashboard or login
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->isMaterialsStaff() ? 'materials.index' : 'dashboard');
});

// Protected routes
Route::middleware(['auth', 'dept'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Account / profile (any logged-in user)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Sales
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales-export', [SaleController::class, 'export'])->name('sales.export');
    Route::get('/sell/{inventoryItem}', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sell/{inventoryItem}', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');

    // Stock requests (store requests items from the stockroom)
    Route::get('/requests', [StockRequestController::class, 'index'])->name('requests.index');
    Route::post('/requests', [StockRequestController::class, 'store'])->name('requests.store');
    Route::post('/requests-restock-low', [StockRequestController::class, 'restockLow'])->name('requests.restockLow');
    Route::get('/requests/{stockRequest}', [StockRequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{stockRequest}/items', [StockRequestController::class, 'addItem'])->name('requests.items.add');
    Route::delete('/requests/{stockRequest}/items/{item}', [StockRequestController::class, 'removeItem'])->name('requests.items.remove');
    Route::post('/requests/{stockRequest}/fulfill', [StockRequestController::class, 'fulfill'])->name('requests.fulfill');
    Route::post('/requests/{stockRequest}/reject', [StockRequestController::class, 'reject'])->name('requests.reject');
    Route::post('/requests/{stockRequest}/cancel', [StockRequestController::class, 'cancel'])->name('requests.cancel');

    // Events (pull stock from inventory to an event location)
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

    // Materials (separate department — only materials staff + admins)
    Route::middleware('materials')->group(function () {
        Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
        Route::get('/materials-export', [MaterialController::class, 'export'])->name('materials.export');
        Route::get('/materials/create', [MaterialController::class, 'create'])->name('materials.create');
        Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store');
        Route::get('/materials/{material}/edit', [MaterialController::class, 'edit'])->name('materials.edit');
        Route::put('/materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
        Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->middleware('admin')->name('materials.destroy');
        Route::get('/materials/{material}/movement', [MaterialController::class, 'movementForm'])->name('materials.movementForm');
        Route::post('/materials/{material}/movement', [MaterialController::class, 'recordMovement'])->name('materials.recordMovement');
        Route::get('/materials/{material}/movements', [MaterialController::class, 'movements'])->name('materials.movements');

        // Suppliers
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers-export', [SupplierController::class, 'export'])->name('suppliers.export');
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('admin')->name('suppliers.destroy');

        // Purchase orders
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchases.index');
        Route::get('/purchase-orders-export', [PurchaseOrderController::class, 'export'])->name('purchases.export');
        Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchases.create');
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchases.store');
        Route::get('/purchase-orders/{purchase}', [PurchaseOrderController::class, 'show'])->name('purchases.show');
        Route::get('/purchase-orders/{purchase}/edit', [PurchaseOrderController::class, 'edit'])->name('purchases.edit');
        Route::put('/purchase-orders/{purchase}', [PurchaseOrderController::class, 'update'])->name('purchases.update');
        Route::delete('/purchase-orders/{purchase}', [PurchaseOrderController::class, 'destroy'])->middleware('admin')->name('purchases.destroy');
        Route::get('/purchase-orders/{purchase}/pdf', [PurchaseOrderController::class, 'pdf'])->name('purchases.pdf');

        Route::post('/purchase-orders/{purchase}/items', [PurchaseOrderController::class, 'addItem'])->name('purchases.items.add');
        Route::delete('/purchase-orders/{purchase}/items/{item}', [PurchaseOrderController::class, 'removeItem'])->name('purchases.items.remove');

        Route::post('/purchase-orders/{purchase}/order', [PurchaseOrderController::class, 'markOrdered'])->name('purchases.markOrdered');
        Route::post('/purchase-orders/{purchase}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchases.cancel');
        Route::post('/purchase-orders/{purchase}/receive', [PurchaseOrderController::class, 'receiveAll'])->name('purchases.receiveAll');
        Route::post('/purchase-orders/{purchase}/items/{item}/receive', [PurchaseOrderController::class, 'receiveItem'])->name('purchases.items.receive');
    });

    // Products (each groups its size variants)
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products-export', [ProductController::class, 'export'])->name('products.export');
    Route::get('/products-import', [ProductController::class, 'importForm'])->middleware('admin')->name('products.importForm');
    Route::post('/products-import', [ProductController::class, 'import'])->middleware('admin')->name('products.import');
    Route::post('/products-bulk', [ProductController::class, 'bulk'])->middleware('admin')->name('products.bulk');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('admin')->name('products.destroy');
    Route::post('/products/{product}/sizes', [ProductController::class, 'addSize'])->name('products.addSize');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');

    Route::get('/inventory-low-stock', [InventoryController::class, 'lowStock'])->name('inventory.lowStock');
    Route::get('/inventory-movements', [InventoryController::class, 'allMovements'])->name('inventory.allMovements');

    Route::get('/inventory-export', [InventoryController::class, 'export'])->name('inventory.export');
    Route::get('/inventory-movements-export', [InventoryController::class, 'exportMovements'])->name('inventory.movements.export');

    // CSV import / restore (admin only)
    Route::get('/inventory-import', [InventoryController::class, 'importForm'])->middleware('admin')->name('inventory.importForm');
    Route::post('/inventory-import', [InventoryController::class, 'import'])->middleware('admin')->name('inventory.import');

    // Trash bin (admin only)
    Route::get('/inventory-trash', [InventoryController::class, 'trash'])->middleware('admin')->name('inventory.trash');
    Route::post('/inventory/{id}/restore', [InventoryController::class, 'restore'])->middleware('admin')->name('inventory.restore');
    Route::delete('/inventory/{id}/force', [InventoryController::class, 'forceDelete'])->middleware('admin')->name('inventory.forceDelete');

    // Warehouse transfer
    Route::get('/inventory/{inventoryItem}/transfer', [InventoryController::class, 'transferForm'])->name('inventory.transferForm');
    Route::post('/inventory/{inventoryItem}/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer');

    Route::get('/inventory/{inventoryItem}/adjust', [InventoryController::class, 'adjustForm'])->name('inventory.adjustForm');
    Route::post('/inventory/{inventoryItem}/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjustStock');

    Route::get('/inventory/{inventoryItem}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{inventoryItem}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{inventoryItem}', [InventoryController::class, 'destroy'])->middleware('admin')->name('inventory.destroy');

    Route::get('/inventory/{inventoryItem}/stock-in', [InventoryController::class, 'stockInForm'])->name('inventory.stockInForm');
    Route::get('/inventory/{inventoryItem}/stock-out', [InventoryController::class, 'stockOutForm'])->name('inventory.stockOutForm');

    Route::post('/inventory/{inventoryItem}/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stockIn');
    Route::post('/inventory/{inventoryItem}/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stockOut');

    Route::get('/inventory/{inventoryItem}/movements', [InventoryController::class, 'movements'])->name('inventory.movements');

    // Projects
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects-export', [ProjectController::class, 'export'])->name('projects.export');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->middleware('admin')->name('projects.destroy');

    Route::post('/projects/{project}/materials', [ProjectController::class, 'addMaterial'])->name('projects.materials.add');
    Route::delete('/projects/{project}/materials/{material}', [ProjectController::class, 'removeMaterial'])->name('projects.materials.remove');

    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search.index');
    Route::get('/activity', [\App\Http\Controllers\ActivityController::class, 'index'])->name('activity.index');
    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');

    // Online store (mock marketplace integration)
    Route::get('/channels', [\App\Http\Controllers\ChannelController::class, 'index'])->name('channels.index');
    Route::post('/channels/{channel}/toggle', [\App\Http\Controllers\ChannelController::class, 'toggle'])->name('channels.toggle');
    Route::post('/channels/{channel}/sync', [\App\Http\Controllers\ChannelController::class, 'sync'])->name('channels.sync');
    Route::get('/online-orders', [\App\Http\Controllers\OnlineOrderController::class, 'index'])->name('online-orders.index');
    Route::post('/online-orders/simulate', [\App\Http\Controllers\OnlineOrderController::class, 'simulate'])->name('online-orders.simulate');
    Route::post('/online-orders/{onlineOrder}/route', [\App\Http\Controllers\OnlineOrderController::class, 'route'])->name('online-orders.route');
    Route::post('/online-orders/{onlineOrder}/ignore', [\App\Http\Controllers\OnlineOrderController::class, 'ignore'])->name('online-orders.ignore');
    Route::delete('/online-orders/{onlineOrder}', [\App\Http\Controllers\OnlineOrderController::class, 'destroy'])->name('online-orders.destroy');
    Route::get('/quality', [\App\Http\Controllers\QualityController::class, 'index'])->name('quality.index');
    Route::get('/feedback', [\App\Http\Controllers\FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/projects/{project}/feedback', [\App\Http\Controllers\FeedbackController::class, 'store'])->name('projects.feedback.store');
    Route::delete('/projects/{project}/feedback/{feedback}', [\App\Http\Controllers\FeedbackController::class, 'destroy'])->name('projects.feedback.destroy');
    Route::post('/projects/{project}/issues', [\App\Http\Controllers\QualityController::class, 'store'])->name('projects.issues.store');
    Route::post('/projects/{project}/issues/{issue}/status', [\App\Http\Controllers\QualityController::class, 'updateStatus'])->name('projects.issues.status');
    Route::delete('/projects/{project}/issues/{issue}', [\App\Http\Controllers\QualityController::class, 'destroy'])->name('projects.issues.destroy');
    Route::get('/deliveries', [\App\Http\Controllers\DeliveryController::class, 'index'])->name('deliveries.index');
    Route::post('/projects/{project}/deliveries', [\App\Http\Controllers\DeliveryController::class, 'store'])->name('projects.deliveries.store');
    Route::post('/projects/{project}/deliveries/{delivery}/status', [\App\Http\Controllers\DeliveryController::class, 'updateStatus'])->name('projects.deliveries.status');
    Route::delete('/projects/{project}/deliveries/{delivery}', [\App\Http\Controllers\DeliveryController::class, 'destroy'])->name('projects.deliveries.destroy');

    Route::post('/projects/{project}/proofs', [ProjectController::class, 'uploadProof'])->name('projects.proofs.upload');
    Route::post('/projects/{project}/proofs/{proof}/approve', [ProjectController::class, 'approveProof'])->name('projects.proofs.approve');
    Route::post('/projects/{project}/proofs/{proof}/reject', [ProjectController::class, 'rejectProof'])->name('projects.proofs.reject');
    Route::delete('/projects/{project}/proofs/{proof}', [ProjectController::class, 'deleteProof'])->name('projects.proofs.delete');

    Route::post('/projects/{project}/share', [ProjectController::class, 'share'])->name('projects.share');
    Route::post('/projects/{project}/start-production', [ProjectController::class, 'startProduction'])->name('projects.startProduction');
    Route::post('/projects/{project}/complete', [ProjectController::class, 'markCompleted'])->name('projects.complete');
    Route::get('/projects/{project}/pdf', [ProjectController::class, 'pdf'])->name('projects.pdf');

    // Customers (CRM)
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers-export', [CustomerController::class, 'export'])->name('customers.export');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
    Route::get('/customers/{customer}/statement/pdf', [CustomerController::class, 'statementPdf'])->name('customers.statement.pdf');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->middleware('admin')->name('customers.destroy');

    // Quotes
    Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes-export', [QuoteController::class, 'export'])->name('quotes.export');
    Route::get('/quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::post('/quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::get('/quotes/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit');
    Route::put('/quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::delete('/quotes/{quote}', [QuoteController::class, 'destroy'])->middleware('admin')->name('quotes.destroy');

    Route::post('/quotes/{quote}/items', [QuoteController::class, 'addItem'])->name('quotes.items.add');
    Route::delete('/quotes/{quote}/items/{item}', [QuoteController::class, 'removeItem'])->name('quotes.items.remove');

    Route::post('/quotes/{quote}/promo', [QuoteController::class, 'applyPromo'])->name('quotes.promo.apply');
    Route::delete('/quotes/{quote}/promo', [QuoteController::class, 'removePromo'])->name('quotes.promo.remove');
    Route::post('/quotes/{quote}/status', [QuoteController::class, 'changeStatus'])->name('quotes.status');
    Route::post('/quotes/{quote}/convert', [QuoteController::class, 'convert'])->name('quotes.convert');
    Route::post('/quotes/{quote}/invoice', [QuoteController::class, 'createInvoice'])->name('quotes.invoice');
    Route::get('/quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices-export', [InvoiceController::class, 'export'])->name('invoices.export');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->middleware('admin')->name('invoices.destroy');

    Route::post('/invoices/{invoice}/items', [InvoiceController::class, 'addItem'])->name('invoices.items.add');
    Route::delete('/invoices/{invoice}/items/{item}', [InvoiceController::class, 'removeItem'])->name('invoices.items.remove');

    Route::post('/invoices/{invoice}/payments', [InvoiceController::class, 'addPayment'])->name('invoices.payments.add');
    Route::delete('/invoices/{invoice}/payments/{payment}', [InvoiceController::class, 'removePayment'])->name('invoices.payments.remove');

    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

    // Admin-only
    Route::middleware('admin')->group(function () {
        // Staff & permissions
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::put('/staff/{user}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{user}', [StaffController::class, 'destroy'])->name('staff.destroy');

        // Expenses & overhead
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses-export', [ExpenseController::class, 'export'])->name('expenses.export');
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

        // Promo codes
        Route::get('/promo-codes', [\App\Http\Controllers\PromoCodeController::class, 'index'])->name('promo-codes.index');
        Route::post('/promo-codes', [\App\Http\Controllers\PromoCodeController::class, 'store'])->name('promo-codes.store');
        Route::post('/promo-codes/{promoCode}/toggle', [\App\Http\Controllers\PromoCodeController::class, 'toggle'])->name('promo-codes.toggle');
        Route::delete('/promo-codes/{promoCode}', [\App\Http\Controllers\PromoCodeController::class, 'destroy'])->name('promo-codes.destroy');

    });
});
