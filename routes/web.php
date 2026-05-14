<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\FaceAttendanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\RegisterMyFaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('attendance.scan');
});

Route::get('/attendance', [FaceAttendanceController::class, 'show'])->name('attendance.scan');
Route::post('/attendance/verify', [FaceAttendanceController::class, 'verify'])
    ->middleware('throttle:120,1')
    ->name('attendance.verify');

Route::middleware(['auth', 'staff'])->group(function () {
    Route::get('my-face', [RegisterMyFaceController::class, 'show'])->name('staff.register-face');
    Route::post('my-face', [RegisterMyFaceController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('staff.register-face.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/reports/attendance', [AttendanceReportController::class, 'monthly'])->name('reports.attendance');
    Route::get('/reports/attendance/staff/{staff}', [AttendanceReportController::class, 'staffMonthly'])
        ->name('reports.attendance.staff');

    Route::get('staff/{staff}/register-face', [StaffController::class, 'registerFace'])->name('staff.register-face');
    Route::post('staff/{staff}/register-face', [StaffController::class, 'storeFaceDescriptor'])->name('staff.register-face.store');

    Route::get('staff/bulk-create', [StaffController::class, 'bulkCreate'])->name('staff.bulk-create');
    Route::post('staff/bulk-create', [StaffController::class, 'bulkStore'])->name('staff.bulk-create.store');

    Route::resource('staff', StaffController::class)->except(['show']);
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('staff.register-face');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
