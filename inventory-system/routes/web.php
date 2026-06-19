<?php

use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return redirect('/inventory');
});

use App\Http\Controllers\InventoryController;

Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');

Route::get('/inventory-low-stock', [InventoryController::class, 'lowStock'])->name('inventory.lowStock');

Route::get('/inventory/{inventoryItem}/adjust', [InventoryController::class, 'adjustForm'])->name('inventory.adjustForm');
Route::post('/inventory/{inventoryItem}/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjustStock');

Route::get('/inventory/{inventoryItem}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
Route::put('/inventory/{inventoryItem}', [InventoryController::class, 'update'])->name('inventory.update');
Route::delete('/inventory/{inventoryItem}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

Route::get('/inventory-movements', [InventoryController::class, 'allMovements'])->name('inventory.allMovements');

Route::get('/inventory/{inventoryItem}/stock-in', [InventoryController::class, 'stockInForm'])->name('inventory.stockInForm');
Route::get('/inventory/{inventoryItem}/stock-out', [InventoryController::class, 'stockOutForm'])->name('inventory.stockOutForm');

Route::post('/inventory/{inventoryItem}/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stockIn');
Route::post('/inventory/{inventoryItem}/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stockOut');

Route::get('/inventory/{inventoryItem}/movements', [InventoryController::class, 'movements'])->name('inventory.movements');