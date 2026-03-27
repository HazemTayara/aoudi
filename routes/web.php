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

Route::resource('cities', CityController::class);
Route::get('/cities/{city}/orders', [CityController::class, 'orders'])->name('cities.orders');

Route::resource('drivers', DriverController::class);
// Replace the single resource route with these
Route::prefix('menafests')->name('menafests.')->group(function () {
    // Incoming manifests (to local city)
    Route::get('/incoming', [MenafestController::class, 'incoming'])->name('incoming');

    // Outgoing manifests (from local city)
    Route::get('/outgoing', [MenafestController::class, 'outgoing'])->name('outgoing');
    Route::get('/menafests/{menafest}/export-outgoing', [MenafestController::class, 'exportOutgoing'])->name('export-outgoing');
    // Keep the resource routes for CRUD operations
    Route::get('/create', [MenafestController::class, 'create'])->name('create');
    Route::post('/', [MenafestController::class, 'store'])->name('store');
    Route::get('/{menafest}/edit', [MenafestController::class, 'edit'])->name('edit');
    Route::put('/{menafest}', [MenafestController::class, 'update'])->name('update');
    Route::delete('/{menafest}', [MenafestController::class, 'destroy'])->name('destroy');
});

// Orders under menafest
Route::prefix('menafests/{menafest}/orders')->name('menafests.orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::post('/', [OrderController::class, 'store'])->name(name: 'store');
});

// Individual order actions (AJAX)
Route::patch('/orders/{order}/toggle-paid', [OrderController::class, 'toggleIsPaid'])->name('orders.toggle-paid');
Route::patch('/orders/{order}/toggle-exist', [OrderController::class, 'toggleIsExist'])->name('orders.toggle-exist');
Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');

Route::get('/menafests/{menafest}/orders/upload', [OrderImportController::class, 'upload'])->name('menafests.orders.upload');
Route::post('/menafests/{menafest}/orders/preview', [OrderImportController::class, 'preview'])->name('menafests.orders.preview');
Route::post('/menafests/{menafest}/orders/import', [OrderImportController::class, 'import'])->name('menafests.orders.import');

// Driver orders management
Route::get('/drivers/{driver}/orders', [DriverOrderController::class, 'index'])->name('drivers.orders');
Route::get('/drivers/{driver}/attach-orders', [DriverOrderController::class, 'attachForm'])->name('drivers.attach-orders');
Route::post('/drivers/{driver}/attach-orders', [DriverOrderController::class, 'attach'])->name('drivers.attach-orders.store');
Route::delete('/drivers/{driver}/detach-order/{order}', [DriverOrderController::class, 'detach'])->name('drivers.detach-order');

// Driver order AJAX actions
Route::patch('/drivers/orders/{order}/toggle-paid', [DriverOrderController::class, 'toggleIsPaid'])->name('drivers.orders.toggle-paid');
Route::patch('/drivers/orders/{order}/toggle-exist', [DriverOrderController::class, 'toggleIsExist'])->name('drivers.orders.toggle-exist');
Route::patch('/drivers/orders/{order}/update-notes', [DriverOrderController::class, 'updateNotes'])->name('drivers.orders.update-notes');

Route::get('/manage-orders', [App\Http\Controllers\ManageOrderController::class, 'index'])->name('manage-orders.index');

// Route::get('/all-orders', [AllOrderController::class, 'index'])->name('all-orders.index');
Route::patch('/manage-orders/{order}/toggle-paid', [ManageOrderController::class, 'togglePaid'])->name('manage-orders.toggle-paid');
Route::patch('/manage-orders/{order}/toggle-exist', [ManageOrderController::class, 'toggleExist'])->name('manage-orders.toggle-exist');
Route::patch('/manage-orders/{order}/update-notes', [ManageOrderController::class, 'updateNotes'])->name('manage-orders.update-notes');

// Route::get('/check-orders', [App\Http\Controllers\CheckOrderController::class, 'index'])->name('check-orders.index');
Route::get('/orders/pay', [CheckOrderController::class, 'payIndex'])->name('orders.pay');
Route::post('/orders/mark-paid', [CheckOrderController::class, 'markPaid'])->name('orders.mark-paid');
Route::get('/orders/search', [CheckOrderController::class, 'search'])->name('orders.search');
Route::get('/orders/today-stats', [CheckOrderController::class, 'todayStats'])->name('orders.today-stats');

Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings/local-city', [SettingsController::class, 'updateLocalCity'])->name('settings.local-city.update');