<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;

// Authentication (accounts are created by an admin, not self-service)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Protected routes
Route::middleware('auth')->group(function () {
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

    // Materials (raw materials department)
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

    // Products (each groups its size variants)
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products-export', [ProductController::class, 'export'])->name('products.export');
    Route::get('/products-import', [ProductController::class, 'importForm'])->middleware('admin')->name('products.importForm');
    Route::post('/products-import', [ProductController::class, 'import'])->middleware('admin')->name('products.import');
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

    Route::post('/projects/{project}/start-production', [ProjectController::class, 'startProduction'])->name('projects.startProduction');
    Route::post('/projects/{project}/complete', [ProjectController::class, 'markCompleted'])->name('projects.complete');
    Route::get('/projects/{project}/pdf', [ProjectController::class, 'pdf'])->name('projects.pdf');

    // Admin-only
    Route::middleware('admin')->group(function () {
        // User management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
