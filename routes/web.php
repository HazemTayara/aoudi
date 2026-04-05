<?php

use App\Http\Controllers\CheckOrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DriverOrderController;
use App\Http\Controllers\ManageOrderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderImportController;
use App\Http\Controllers\MenafestController;
use App\Http\Controllers\SettingsController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index']);

// Cities routes
Route::resource('cities', CityController::class);
Route::get('cities/trashed', [CityController::class, 'show'])->name('cities.trashed');
Route::put('cities/{id}/restore', [CityController::class, 'restore'])->name('cities.restore');
Route::delete('cities/{id}/force-delete', [CityController::class, 'forceDelete'])->name('cities.force-delete');
Route::get('cities/{city}/orders', [CityController::class, 'orders'])->name('cities.orders');

// Drivers routes
Route::resource('drivers', DriverController::class);
Route::get('drivers/trashed', [DriverController::class, 'show'])->name('drivers.trashed');
Route::put('drivers/{id}/restore', [DriverController::class, 'restore'])->name('drivers.restore');
Route::delete('drivers/{id}/force-delete', [DriverController::class, 'forceDelete'])->name('drivers.force-delete');

// Menafests routes
Route::prefix('menafests')->name('menafests.')->group(function () {

    // Incoming manifests (to local city)
    Route::get('/incoming', [MenafestController::class, 'incoming'])->name('incoming');
    Route::get('/incoming/trashed', [MenafestController::class, 'incomingTrashed'])->name('incoming.trashed');
    // Outgoing manifests (from local city)
    Route::get('/outgoing', [MenafestController::class, 'outgoing'])->name('outgoing');
    Route::get('/outgoing/trashed', [MenafestController::class, 'outgoingTrashed'])->name('outgoing.trashed');
    Route::get('/menafests/{menafest}/export-outgoing', [MenafestController::class, 'exportOutgoing'])->name('export-outgoing');
    // Keep the resource routes for CRUD operations
    Route::get('/create', [MenafestController::class, 'create'])->name('create');
    Route::post('/', [MenafestController::class, 'store'])->name('store');
    Route::get('/{menafest}/edit', [MenafestController::class, 'edit'])->name('edit');
    Route::put('/{menafest}', [MenafestController::class, 'update'])->name('update');
    Route::delete('/{menafest}', [MenafestController::class, 'destroy'])->name('destroy');
    Route::put('/{id}/restore', [MenafestController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [MenafestController::class, 'forceDelete'])->name('force-delete');
});

// Orders under menafest
Route::prefix('menafests/{menafest}/orders')->name('menafests.orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/trashed', [OrderController::class, 'trashedOrders'])->name('trashed');
    Route::post('/', [OrderController::class, 'store'])->name('store');
});

// Edit Order
Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');

// Handling Excel files to import
Route::get('/menafests/{menafest}/orders/upload', [OrderImportController::class, 'upload'])->name('menafests.orders.upload');
Route::post('/menafests/{menafest}/orders/preview', [OrderImportController::class, 'preview'])->name('menafests.orders.preview');
Route::post('/menafests/{menafest}/orders/import', [OrderImportController::class, 'import'])->name('menafests.orders.import');

// Driver orders management
Route::get('/drivers/{driver}/orders', [DriverOrderController::class, 'index'])->name('drivers.orders');
Route::get('/drivers/{driver}/attach-orders', [DriverOrderController::class, 'attachForm'])->name('drivers.attach-orders');
Route::post('/drivers/{driver}/attach-orders', [DriverOrderController::class, 'attach'])->name('drivers.attach-orders.store');
Route::delete('/drivers/{driver}/detach-order/{order}', [DriverOrderController::class, 'detach'])->name('drivers.detach-order');

// Manage Orders routes
Route::get('/manage-orders', [ManageOrderController::class, 'index'])->name('manage-orders.index');
Route::delete('/orders/{order}', [ManageOrderController::class, 'destroy'])->name('orders.delete');
Route::get('/orders/trashed', [ManageOrderController::class, 'show'])->name('orders.trashed');
Route::put('orders/{id}/restore', [ManageOrderController::class, 'restore'])->name('orders.restore');
Route::delete('orders/{id}/force-delete', [ManageOrderController::class, 'forceDelete'])->name('orders.force-delete');
Route::patch('/manage-orders/{order}/toggle-paid', [ManageOrderController::class, 'togglePaid'])->name('manage-orders.toggle-paid');
Route::patch('/manage-orders/{order}/toggle-exist', [ManageOrderController::class, 'toggleExist'])->name('manage-orders.toggle-exist');
Route::patch('/manage-orders/{order}/update-notes', [ManageOrderController::class, 'updateNotes'])->name('manage-orders.update-notes');

// Check orders routes
Route::get('/orders/pay', [CheckOrderController::class, 'payIndex'])->name('orders.pay');
Route::post('/orders/mark-paid', [CheckOrderController::class, 'markPaid'])->name('orders.mark-paid');
Route::get('/orders/search', [CheckOrderController::class, 'search'])->name('orders.search');
Route::get('/orders/today-stats', [CheckOrderController::class, 'todayStats'])->name('orders.today-stats');

// Settings routes
Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings/local-city', [SettingsController::class, 'updateLocalCity'])->name('settings.local-city.update');


// Route::patch('/orders/{order}/toggle-paid', [OrderController::class, 'toggleIsPaid'])->name('orders.toggle-paid');
// Route::patch('/orders/{order}/toggle-exist', [OrderController::class, 'toggleIsExist'])->name('orders.toggle-exist');

// Route::patch('/drivers/orders/{order}/toggle-paid', [DriverOrderController::class, 'toggleIsPaid'])->name('drivers.orders.toggle-paid');
// Route::patch('/drivers/orders/{order}/toggle-exist', [DriverOrderController::class, 'toggleIsExist'])->name('drivers.orders.toggle-exist');
// Route::patch('/drivers/orders/{order}/update-notes', [DriverOrderController::class, 'updateNotes'])->name('drivers.orders.update-notes');