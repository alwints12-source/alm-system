<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;

Route::middleware('auth')->group(function () {
    Route::get('/techadmin/users', [UserController::class, 'index'])->name('techadmin.users.index');
    Route::post('/techadmin/users', [UserController::class, 'store'])->name('techadmin.users.store');
    Route::patch('/techadmin/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('techadmin.users.toggle');
});

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', function () {
    $pendingAssignments = \App\Models\AssetAssignment::with('asset.category')
        ->where('holder_id', auth()->id())
        ->where('status', 'pending_acknowledgement')
        ->get();

    return match (auth()->user()->role) {
        'administrative_admin' => view('admin.dashboard', compact('pendingAssignments')),
        'technical_admin'      => view('techadmin.dashboard', compact('pendingAssignments')),
        'asset_holder'         => view('holder.dashboard', compact('pendingAssignments')),
        'technician'           => view('technician.dashboard', compact('pendingAssignments')),
        default                => view('dashboard'),
    };
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssignmentController;

Route::middleware('auth')->group(function () {
    Route::get('/admin/assets', [AssetController::class, 'index'])->name('admin.assets.index');
    Route::get('/admin/assets/create', [AssetController::class, 'create'])->name('admin.assets.create');
    Route::post('/admin/assets', [AssetController::class, 'store'])->name('admin.assets.store');

    Route::get('/holder/assets', [AssignmentController::class, 'index'])->name('holder.assets.index');
    Route::patch('/holder/assignments/{assignment}/acknowledge', [AssignmentController::class, 'acknowledge'])->name('holder.assignments.acknowledge');
});

use App\Http\Controllers\WorkOrderController;

Route::middleware('auth')->group(function () {
    Route::post('/holder/assets/{assignment}/report-issue', [WorkOrderController::class, 'store'])->name('holder.assets.reportIssue');

    Route::get('/admin/requests', [WorkOrderController::class, 'index'])->name('admin.requests.index');
    Route::patch('/admin/requests/{workOrder}/approve', [WorkOrderController::class, 'approve'])->name('admin.requests.approve');
    Route::patch('/admin/requests/{workOrder}/reject', [WorkOrderController::class, 'reject'])->name('admin.requests.reject');

    Route::get('/technician/work-orders', [WorkOrderController::class, 'technicianIndex'])->name('technician.workorders.index');
    Route::get('/technician/work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('technician.workorders.show');
    Route::patch('/technician/work-orders/{workOrder}/start', [WorkOrderController::class, 'startWork'])->name('technician.workorders.start');
    Route::patch('/technician/work-orders/{workOrder}/complete', [WorkOrderController::class, 'complete'])->name('technician.workorders.complete');
});

Route::middleware('auth')->group(function () {
    Route::get('/holder/requests', [WorkOrderController::class, 'myRequests'])->name('holder.requests.index');
    Route::get('/admin/asset-conditions', [WorkOrderController::class, 'assetConditions'])->name('admin.asset-conditions.index');
});

Route::middleware('auth')->group(function () {
    Route::patch('/technician/checklist/{item}/toggle', [WorkOrderController::class, 'toggleChecklistItem'])->name('technician.workorders.checklist.toggle');
});

use App\Http\Controllers\MfaController;

Route::middleware('auth')->group(function () {
    Route::get('/settings/mfa', [MfaController::class, 'show'])->name('settings.mfa');
    Route::post('/settings/mfa/enable', [MfaController::class, 'enable'])->name('settings.mfa.enable');
    Route::delete('/settings/mfa/disable', [MfaController::class, 'disable'])->name('settings.mfa.disable');
});

use App\Http\Controllers\Auth\MfaLoginController;

Route::middleware('guest')->group(function () {
    Route::get('/mfa/challenge', [MfaLoginController::class, 'challenge'])->name('mfa.challenge');
    Route::post('/mfa/verify', [MfaLoginController::class, 'verify'])->name('mfa.verify');
});
