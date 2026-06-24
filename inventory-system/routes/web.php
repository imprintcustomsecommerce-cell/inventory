<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BomTemplateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
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
    Route::post('/projects/{project}/apply-template', [ProjectController::class, 'applyTemplate'])->name('projects.applyTemplate');
    Route::get('/projects/{project}/pdf', [ProjectController::class, 'pdf'])->name('projects.pdf');

    // BOM templates (default materials per product type) — admin only
    Route::middleware('admin')->group(function () {
        Route::get('/bom-templates', [BomTemplateController::class, 'index'])->name('bomTemplates.index');
        Route::post('/bom-templates', [BomTemplateController::class, 'store'])->name('bomTemplates.store');
        Route::delete('/bom-templates/{bomTemplate}', [BomTemplateController::class, 'destroy'])->name('bomTemplates.destroy');

        // User management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
