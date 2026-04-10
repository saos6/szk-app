<?php

use App\Http\Controllers\BillingBalanceController;
use App\Http\Controllers\BillingClosingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeptController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleModelController;
use App\Http\Controllers\InventoryBalanceController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // 所属マスタ
    Route::get('depts/export', [DeptController::class, 'export'])->name('depts.export');
    Route::get('depts/{dept}/replicate', [DeptController::class, 'replicate'])->name('depts.replicate');
    Route::resource('depts', DeptController::class);

    // 社員マスタ
    Route::get('employees/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::get('employees/{employee}/replicate', [EmployeeController::class, 'replicate'])->name('employees.replicate');
    Route::resource('employees', EmployeeController::class);

    // 得意先マスタ
    Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::get('customers/{customer}/replicate', [CustomerController::class, 'replicate'])->name('customers.replicate');
    Route::resource('customers', CustomerController::class);

    // 商品マスタ
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::get('products/{product}/replicate', [ProductController::class, 'replicate'])->name('products.replicate');
    Route::resource('products', ProductController::class);

    // 車両マスタ
    Route::get('vehicles/export', [VehicleController::class, 'export'])->name('vehicles.export');
    Route::get('vehicles/{vehicle}/replicate', [VehicleController::class, 'replicate'])->name('vehicles.replicate');
    Route::resource('vehicles', VehicleController::class);

    // 車両機種マスタ
    Route::get('vehicle-models/export', [VehicleModelController::class, 'export'])->name('vehicle-models.export');
    Route::get('vehicle-models/{vehicle_model}/replicate', [VehicleModelController::class, 'replicate'])->name('vehicle-models.replicate');
    Route::resource('vehicle-models', VehicleModelController::class);

    // 在庫残高マスタ
    Route::get('inventory-balances/export', [InventoryBalanceController::class, 'export'])->name('inventory-balances.export');
    Route::get('inventory-balances/{inventory_balance}/replicate', [InventoryBalanceController::class, 'replicate'])->name('inventory-balances.replicate');
    Route::resource('inventory-balances', InventoryBalanceController::class);

    // 倉庫マスタ
    Route::get('warehouses/export', [WarehouseController::class, 'export'])->name('warehouses.export');
    Route::get('warehouses/{warehouse}/replicate', [WarehouseController::class, 'replicate'])->name('warehouses.replicate');
    Route::resource('warehouses', WarehouseController::class);

    // 請求締め処理
    Route::get('billing-closing', [BillingClosingController::class, 'index'])->name('billing-closing.index');
    Route::post('billing-closing/search', [BillingClosingController::class, 'search'])->name('billing-closing.search');
    Route::post('billing-closing/aggregate', [BillingClosingController::class, 'doAggregate'])->name('billing-closing.aggregate');
    Route::post('billing-closing/confirm', [BillingClosingController::class, 'doConfirm'])->name('billing-closing.confirm');
    Route::post('billing-closing/cancel', [BillingClosingController::class, 'doCancel'])->name('billing-closing.cancel');
    Route::get('billing-closing/pdf', [BillingClosingController::class, 'pdf'])->name('billing-closing.pdf');
    Route::get('billing-closing/{billing_balance}/pdf', [BillingClosingController::class, 'pdfSingle'])->name('billing-closing.pdf-single');

    // 請求残高マスタ
    Route::get('billing-balances/export', [BillingBalanceController::class, 'export'])->name('billing-balances.export');
    Route::get('billing-balances/{billing_balance}/replicate', [BillingBalanceController::class, 'replicate'])->name('billing-balances.replicate');
    Route::resource('billing-balances', BillingBalanceController::class);

    // 入金
    Route::get('payments/export', [PaymentController::class, 'exportMethod'])->name('payments.export');
    Route::get('payments/{payment}/replicate', [PaymentController::class, 'replicate'])->name('payments.replicate');
    Route::get('payments/{payment}/pdf', [PaymentController::class, 'pdf'])->name('payments.pdf');
    Route::resource('payments', PaymentController::class);

    // 売上
    Route::get('sales/export', [SaleController::class, 'exportMethod'])->name('sales.export');
    Route::get('sales/{sale}/replicate', [SaleController::class, 'replicate'])->name('sales.replicate');
    Route::get('sales/{sale}/pdf', [SaleController::class, 'pdf'])->name('sales.pdf');
    Route::resource('sales', SaleController::class);

    // 見積
    Route::get('quotes/export', [QuoteController::class, 'exportMethod'])->name('quotes.export');
    Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');
    Route::get('quotes/{quote}/replicate', [QuoteController::class, 'replicate'])->name('quotes.replicate');
    Route::resource('quotes', QuoteController::class);
});

require __DIR__.'/settings.php';
