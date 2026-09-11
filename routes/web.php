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

use App\Http\Controllers\SlaPolicyController;

Route::middleware('auth')->group(function () {
    Route::get('/admin/sla-policies', [SlaPolicyController::class, 'index'])->name('admin.sla-policies.index');
    Route::get('/admin/sla-policies/create', [SlaPolicyController::class, 'create'])->name('admin.sla-policies.create');
    Route::post('/admin/sla-policies', [SlaPolicyController::class, 'store'])->name('admin.sla-policies.store');
    Route::get('/admin/sla-policies/{slaPolicy}/edit', [SlaPolicyController::class, 'edit'])->name('admin.sla-policies.edit');
    Route::patch('/admin/sla-policies/{slaPolicy}', [SlaPolicyController::class, 'update'])->name('admin.sla-policies.update');
    Route::patch('/admin/sla-policies/{slaPolicy}/toggle', [SlaPolicyController::class, 'toggleActive'])->name('admin.sla-policies.toggle');
});

use App\Http\Controllers\MaintenanceScheduleController;

Route::middleware('auth')->group(function () {
    Route::get('/admin/maintenance-schedules', [MaintenanceScheduleController::class, 'index'])->name('admin.maintenance-schedules.index');
    Route::get('/admin/maintenance-schedules/create', [MaintenanceScheduleController::class, 'create'])->name('admin.maintenance-schedules.create');
    Route::post('/admin/maintenance-schedules', [MaintenanceScheduleController::class, 'store'])->name('admin.maintenance-schedules.store');
    Route::get('/admin/maintenance-schedules/{maintenanceSchedule}/edit', [MaintenanceScheduleController::class, 'edit'])->name('admin.maintenance-schedules.edit');
    Route::patch('/admin/maintenance-schedules/{maintenanceSchedule}', [MaintenanceScheduleController::class, 'update'])->name('admin.maintenance-schedules.update');
    Route::patch('/admin/maintenance-schedules/{maintenanceSchedule}/toggle', [MaintenanceScheduleController::class, 'toggleActive'])->name('admin.maintenance-schedules.toggle');

    Route::get('/technician/maintenance-schedule', [MaintenanceScheduleController::class, 'technicianIndex'])->name('technician.maintenance-schedule.index');
});

use App\Http\Controllers\AssetTransferController;

Route::middleware('auth')->group(function () {
    Route::get('/admin/transfers', [AssetTransferController::class, 'index'])->name('admin.transfers.index');
    Route::get('/admin/transfers/create', [AssetTransferController::class, 'create'])->name('admin.transfers.create');
    Route::post('/admin/transfers', [AssetTransferController::class, 'store'])->name('admin.transfers.store');
    Route::patch('/admin/transfers/{transfer}/approve', [AssetTransferController::class, 'approve'])->name('admin.transfers.approve');
    Route::patch('/admin/transfers/{transfer}/reject', [AssetTransferController::class, 'reject'])->name('admin.transfers.reject');
});

Route::middleware('auth')->group(function () {
    Route::post('/holder/assets/{assignment}/request-transfer', [AssetTransferController::class, 'holderStore'])->name('holder.assets.requestTransfer');
});

use App\Http\Controllers\AssetDisposalController;

Route::middleware('auth')->group(function () {
    Route::get('/admin/disposals', [AssetDisposalController::class, 'index'])->name('admin.disposals.index');
    Route::get('/admin/disposals/create', [AssetDisposalController::class, 'create'])->name('admin.disposals.create');
    Route::post('/admin/disposals', [AssetDisposalController::class, 'store'])->name('admin.disposals.store');
    Route::patch('/admin/disposals/{disposal}/approve', [AssetDisposalController::class, 'approve'])->name('admin.disposals.approve');
    Route::patch('/admin/disposals/{disposal}/reject', [AssetDisposalController::class, 'reject'])->name('admin.disposals.reject');
});
