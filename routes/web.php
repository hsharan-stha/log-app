<?php

use App\Http\Controllers\Admin\AcademicSetupController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\KioskDeviceController;
use App\Http\Controllers\Admin\NoticeController as AdminNoticeController;
use App\Http\Controllers\Admin\OfficeUserController;
use App\Http\Controllers\Admin\PeopleSetupController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Attendance\KioskSetupController;
use App\Http\Controllers\Attendance\PortalController as AttendancePortalController;
use App\Http\Controllers\Billing\PortalBillingController;
use App\Http\Controllers\FaceAttendanceController;
use App\Http\Controllers\Finance\DashboardController as FinanceDashboardController;
use App\Http\Controllers\Finance\FeeTypeController;
use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Guardian\DashboardController as GuardianDashboardController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffPortal\DashboardController as OfficeDashboardController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\PortalController as TeacherPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->homeRoute());
    }

    return redirect()->route('login');
});

Route::get('/attendance/unauthorized', [FaceAttendanceController::class, 'unauthorized'])
    ->name('attendance.unauthorized');

Route::middleware(['auth', 'attendance'])->group(function () {
    Route::get('/attendance/home', [AttendancePortalController::class, 'home'])->name('attendance.home');
    Route::get('/attendance/setup', [KioskSetupController::class, 'show'])->name('attendance.setup');
    Route::post('/attendance/setup', [KioskSetupController::class, 'store'])->name('attendance.setup.store');
});

Route::middleware(['auth', 'attendance', 'kiosk'])->group(function () {
    Route::get('/attendance', [FaceAttendanceController::class, 'show'])->name('attendance.scan');
    Route::post('/attendance/verify', [FaceAttendanceController::class, 'verify'])
        ->middleware('throttle:120,1')
        ->name('attendance.verify');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/day/{date}', [AttendanceController::class, 'day'])
        ->where('date', '\d{4}-\d{2}-\d{2}')
        ->name('attendance.day');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/billing', [ReportController::class, 'billing'])->name('reports.billing');
    Route::get('/reports/attendance', [AttendanceReportController::class, 'monthly'])->name('reports.attendance');
    Route::get('/reports/attendance/teachers', [AttendanceReportController::class, 'teachers'])->name('reports.attendance.teachers');
    Route::get('/reports/attendance/students', [AttendanceReportController::class, 'students'])->name('reports.attendance.students');
    Route::get('/reports/attendance/staff/{staff}', [AttendanceReportController::class, 'staffMonthly'])
        ->name('reports.attendance.staff');

    Route::get('devices', [KioskDeviceController::class, 'index'])->name('devices.index');
    Route::post('devices/{device}/revoke', [KioskDeviceController::class, 'revoke'])->name('devices.revoke');

    Route::get('notices', [AdminNoticeController::class, 'index'])->name('notices.index');
    Route::get('notices/create', [AdminNoticeController::class, 'create'])->name('notices.create');
    Route::post('notices', [AdminNoticeController::class, 'store'])->name('notices.store');
    Route::delete('notices/{notice}', [AdminNoticeController::class, 'destroy'])->name('notices.destroy');

    Route::resource('office-users', OfficeUserController::class)
        ->except(['show', 'destroy'])
        ->parameters(['office-users' => 'officeUser']);
});

Route::middleware(['auth', 'role:admin,hr'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/academics', [AcademicSetupController::class, 'index'])->name('academics.index');
    Route::get('/people', [PeopleSetupController::class, 'index'])->name('people.index');

    Route::get('staff/{staff}/register-face', [StaffController::class, 'registerFace'])->name('staff.register-face');
    Route::post('staff/{staff}/register-face', [StaffController::class, 'storeFaceDescriptor'])->name('staff.register-face.store');
    Route::get('staff/bulk-create', [StaffController::class, 'bulkCreate'])->name('staff.bulk-create');
    Route::post('staff/bulk-create', [StaffController::class, 'bulkStore'])->name('staff.bulk-create.store');
    Route::resource('staff', StaffController::class)->except(['show']);

    Route::get('students/{student}/register-face', [StudentController::class, 'registerFace'])->name('students.register-face');
    Route::post('students/{student}/register-face', [StudentController::class, 'storeFaceDescriptor'])->name('students.register-face.store');
    Route::resource('students', StudentController::class)->except(['show']);

    Route::resource('guardians', GuardianController::class)->except(['show']);
    Route::resource('classes', SchoolClassController::class)->except(['show']);
    Route::resource('subjects', SubjectController::class)->except(['show']);
    Route::resource('courses', AdminCourseController::class)->except(['show']);
});

Route::middleware(['auth', 'role:admin,finance'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/', [FinanceDashboardController::class, 'index'])->name('dashboard');
    Route::resource('fee-types', FeeTypeController::class)->except(['show', 'destroy']);
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::post('invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('invoices.void');
    Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'storePayment'])->name('invoices.payments.store');
});

Route::middleware(['auth', 'role:staff,other'])->prefix('office')->name('office.')->group(function () {
    Route::get('/', [OfficeDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/attendance', [TeacherPortalController::class, 'attendance'])->name('attendance');
    Route::get('/students', [TeacherPortalController::class, 'classStudents'])->name('students');
    Route::get('/notices', [TeacherPortalController::class, 'notices'])->name('notices');
    Route::post('/notices', [TeacherPortalController::class, 'storeNotice'])->name('notices.store');
    Route::get('/face', [TeacherPortalController::class, 'registerFace'])->name('face');
    Route::post('/face', [TeacherPortalController::class, 'storeFace'])->name('face.store');

    Route::get('/courses', [TeacherCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [TeacherCourseController::class, 'show'])->name('courses.show');
    Route::get('/courses/{course}/lessons/create', [TeacherCourseController::class, 'createLesson'])->name('lessons.create');
    Route::post('/courses/{course}/lessons', [TeacherCourseController::class, 'storeLesson'])->name('lessons.store');
    Route::get('/courses/{course}/lessons/{lesson}', [TeacherCourseController::class, 'showLesson'])->name('lessons.show');
    Route::get('/courses/{course}/lessons/{lesson}/edit', [TeacherCourseController::class, 'editLesson'])->name('lessons.edit');
    Route::put('/courses/{course}/lessons/{lesson}', [TeacherCourseController::class, 'updateLesson'])->name('lessons.update');
    Route::delete('/courses/{course}/lessons/{lesson}', [TeacherCourseController::class, 'destroyLesson'])->name('lessons.destroy');
    Route::delete('/courses/{course}/lessons/{lesson}/attachments/{attachment}', [TeacherCourseController::class, 'destroyAttachment'])
        ->name('attachments.destroy');
    Route::delete('/courses/{course}/lessons/{lesson}/whiteboards/{whiteboard}', [TeacherCourseController::class, 'destroyWhiteboard'])
        ->name('whiteboards.destroy');
});

Route::middleware(['auth', 'student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/', [StudentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/courses', [StudentCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [StudentCourseController::class, 'show'])->name('courses.show');
    Route::get('/courses/{course}/lessons/{lesson}', [StudentCourseController::class, 'showLesson'])->name('lessons.show');
    Route::get('/billing', [PortalBillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/{invoice}', [PortalBillingController::class, 'show'])->name('billing.show');
});

Route::middleware(['auth', 'guardian'])->prefix('guardian')->name('guardian.')->group(function () {
    Route::get('/', [GuardianDashboardController::class, 'index'])->name('dashboard');
    Route::get('/attendance', [GuardianDashboardController::class, 'attendance'])->name('attendance');
    Route::get('/attendance/day/{date}', [GuardianDashboardController::class, 'attendanceDay'])
        ->where('date', '\d{4}-\d{2}-\d{2}')
        ->name('attendance.day');
    Route::get('/billing', [PortalBillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/{invoice}', [PortalBillingController::class, 'show'])->name('billing.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::post('/messages/{message}/read', [MessageController::class, 'markRead'])->name('messages.read');

    Route::get('/dashboard', function () {
        return redirect()->route(auth()->user()->homeRoute());
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::redirect('/my-face', '/teacher/face');

require __DIR__.'/auth.php';
