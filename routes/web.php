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
    return view('welcome');
});

Route::get('/dashboard', function () {
    return match (auth()->user()->role) {
        'administrative_admin' => view('admin.dashboard'),
        'technical_admin'      => view('techadmin.dashboard'),
        'asset_holder'         => view('holder.dashboard', [
   	     'pendingAssignments' => \App\Models\AssetAssignment::with('asset.category')
                 ->where('holder_id', auth()->id())
                 ->where('status', 'pending_acknowledgement')
                 ->get(),
        ]),
        'technician'           => view('technician.dashboard'),
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
