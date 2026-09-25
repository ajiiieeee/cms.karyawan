<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PersonalDataController;
use App\Http\Controllers\EmployeeLeaveController;
use App\Http\Controllers\EmployeeFimpController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\AnnouncementController;
Route::middleware('guest')->group(function(){Route::get('/',[AuthController::class,'login'])->name('login');Route::post('/login',[AuthController::class,'loginSubmit'])->middleware('throttle:10,1')->name('login.submit');});
Route::middleware('auth')->group(function(){Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');Route::get('/personal-data',[PersonalDataController::class,'index'])->name('personal-data.index');Route::get('/personal-data/edit',[PersonalDataController::class,'edit'])->name('personal-data.edit');Route::put('/personal-data',[PersonalDataController::class,'update'])->name('personal-data.update');Route::resource('leave-requests',EmployeeLeaveController::class)->except(['show']);Route::resource('fimp',EmployeeFimpController::class)->except(['show']);Route::resource('overtime-requests',OvertimeRequestController::class)->except(['show']);Route::get('/payroll',[PayrollController::class,'index'])->name('payroll.index');Route::get('/announcements',[AnnouncementController::class,'index'])->name('announcements.index');Route::post('/logout',[AuthController::class,'logout'])->name('logout');});
