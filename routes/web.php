<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\MenuManagementController;
use App\Http\Controllers\GrupManagementController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\PengajuanCutiController;
use App\Http\Controllers\SaldoCutiController;
use App\Http\Controllers\FimpController;
use App\Http\Controllers\BidangStudiController;
use App\Http\Controllers\PelatihanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\PenjadwalanController;
use App\Http\Controllers\SertifikatController;
use App\Http\Controllers\SiswaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes - Verifikasi Sertifikat (tanpa login)
Route::get('/verifikasi-sertifikat/{token}', [SertifikatController::class, 'verifikasi'])
    ->name('sertifikat.verifikasi');

// Public Routes - Pendaftaran Cabang Nginden (tanpa login)
// Route::prefix('pendaftaran-nginden')->name('pendaftaran.')->group(function () {
//     Route::get('/', [PendaftaranController::class, 'publicIndex'])->name('nginden');
//     Route::post('/', [PendaftaranController::class, 'publicStore'])->name('public-store');
//     Route::get('/member', [PendaftaranController::class, 'publicMemberCreate'])->name('nginden.member');
//     Route::post('/member', [PendaftaranController::class, 'publicMemberStore'])->name('public-member-store');
//     Route::get('/cek-nik', [PendaftaranController::class, 'publicCekNik'])->name('nginden.cek-nik');
//     Route::get('/success', [PendaftaranController::class, 'sukses'])->name('success');
// });

Route::prefix('pendaftaran-crtv')->name('pendaftaran.')->group(function () {
    Route::get('/', [PendaftaranController::class, 'publicIndex'])->name('nginden');
    Route::post('/', [PendaftaranController::class, 'publicStore'])->name('public-store');
    Route::get('/export', [PendaftaranController::class, 'export'])->name('export');
    Route::get('/member', [PendaftaranController::class, 'publicMemberCreate'])->name('nginden.member');
    Route::post('/member', [PendaftaranController::class, 'publicMemberStore'])->name('public-member-store');
    Route::get('/cek-nik', [PendaftaranController::class, 'publicCekNik'])->name('nginden.cek-nik');
    Route::get('/success', [PendaftaranController::class, 'sukses'])->name('success');
});

// Public Routes - Authentication
Route::middleware(['guest'])->group(function () {
    Route::get('/', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginSubmit'])
        ->middleware('throttle:10,1')
        ->name('login.submit');
});

// Protected Routes - Authenticated Users Only
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'dashboardData'])->name('dashboard.data');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart-data');
    Route::get('/dashboard/schedule-detail', [DashboardController::class, 'scheduleDetail'])->name('dashboard.schedule-detail');

    // Pembayaran
    Route::prefix('pembayaran')->name('pembayaran.')->middleware('menu.access:pembayaran,view')->group(function () {
        Route::get('/', [PembayaranController::class, 'index'])->name('index');
        Route::get('/data', [PembayaranController::class, 'data'])->name('data');
        Route::get('/search-kursus', [PembayaranController::class, 'searchKursus'])->name('search-kursus');
        Route::get('/create', [PembayaranController::class, 'create'])->name('create')->middleware('menu.access:pembayaran,add');
        Route::post('/', [PembayaranController::class, 'store'])->name('store')->middleware('menu.access:pembayaran,add');
        Route::get('/export', [PembayaranController::class, 'export'])->name('export');
        Route::get('/{encryptedId}', [PembayaranController::class, 'show'])->name('show');
        Route::post('/{encryptedId}/riwayat', [PembayaranController::class, 'storeRiwayat'])->name('riwayat.store')->middleware('menu.access:pembayaran,add');
        Route::get('/{encryptedId}/kwitansi-dp', [PembayaranController::class, 'kwitansiDp'])->name('kwitansi-dp');
        Route::get('/{encryptedId}/kwitansi-pelunasan', [PembayaranController::class, 'kwitansiPelunasan'])->name('kwitansi-pelunasan');
        Route::get('/{encryptedId}/edit', [PembayaranController::class, 'edit'])->name('edit')->middleware('menu.access:pembayaran,edit');
        Route::put('/{encryptedId}', [PembayaranController::class, 'update'])->name('update')->middleware('menu.access:pembayaran,edit');
        Route::delete('/{encryptedId}', [PembayaranController::class, 'destroy'])->name('destroy')->middleware('menu.access:pembayaran,delete');
    });

    // Penjadwalan
    Route::prefix('penjadwalan')->name('penjadwalan.')->middleware('menu.access:penjadwalan,view')->group(function () {
        Route::get('/', [PenjadwalanController::class, 'index'])->name('index');
        Route::get('/data', [PenjadwalanController::class, 'data'])->name('data');
        Route::get('/create', [PenjadwalanController::class, 'create'])->name('create')->middleware('menu.access:penjadwalan,add');
        Route::post('/', [PenjadwalanController::class, 'store'])->name('store')->middleware('menu.access:penjadwalan,add');
        Route::get('/{encryptedId}', [PenjadwalanController::class, 'show'])->name('show');
        Route::get('/{encryptedId}/edit', [PenjadwalanController::class, 'edit'])->name('edit')->middleware('menu.access:penjadwalan,edit');
        Route::put('/{encryptedId}', [PenjadwalanController::class, 'update'])->name('update')->middleware('menu.access:penjadwalan,edit');
        Route::delete('/{encryptedId}', [PenjadwalanController::class, 'destroy'])->name('destroy')->middleware('menu.access:penjadwalan,delete');
    });

    // Pendaftaran
    Route::prefix('pendaftaran')->name('pendaftaran.')->middleware('menu.access:pendaftaran,view')->group(function () {
        Route::get('/', [PendaftaranController::class, 'index'])->name('index');
        Route::get('/data', [PendaftaranController::class, 'data'])->name('data');
        Route::get('/export', [PendaftaranController::class, 'export'])->name('export');
        Route::get('/create', [PendaftaranController::class, 'create'])->name('create')->middleware('menu.access:pendaftaran,add');
        Route::post('/', [PendaftaranController::class, 'store'])->name('store')->middleware('menu.access:pendaftaran,add');
        Route::get('/member/create', [PendaftaranController::class, 'memberCreate'])->name('member.create')->middleware('menu.access:pendaftaran,add');
        Route::post('/member', [PendaftaranController::class, 'memberStore'])->name('member.store')->middleware('menu.access:pendaftaran,add');
        Route::get('/search-siswa', [PendaftaranController::class, 'searchSiswa'])->name('search-siswa');
        Route::get('/{encryptedId}', [PendaftaranController::class, 'show'])->name('show');
        Route::get('/{encryptedId}/edit', [PendaftaranController::class, 'edit'])->name('edit')->middleware('menu.access:pendaftaran,edit');
        Route::put('/{encryptedId}', [PendaftaranController::class, 'update'])->name('update')->middleware('menu.access:pendaftaran,edit');
        Route::delete('/{encryptedId}', [PendaftaranController::class, 'destroy'])->name('destroy')->middleware('menu.access:pendaftaran,delete');
    });

    // Pelatihan
    Route::prefix('pelatihan')->name('pelatihan.')->middleware('menu.access:pelatihan,view')->group(function () {
        Route::get('/', [PelatihanController::class, 'index'])->name('index');
        Route::get('/data', [PelatihanController::class, 'data'])->name('data');
        Route::get('/create', [PelatihanController::class, 'create'])->name('create')->middleware('menu.access:pelatihan,add');
        Route::post('/', [PelatihanController::class, 'store'])->name('store')->middleware('menu.access:pelatihan,add');
        Route::get('/{encryptedId}/edit', [PelatihanController::class, 'edit'])->name('edit')->middleware('menu.access:pelatihan,edit');
        Route::put('/{encryptedId}', [PelatihanController::class, 'update'])->name('update')->middleware('menu.access:pelatihan,edit');
        Route::delete('/{encryptedId}', [PelatihanController::class, 'destroy'])->name('destroy')->middleware('menu.access:pelatihan,delete');
    });

    // Pengajuan Cuti
    Route::prefix('pengajuan-cuti')->name('pengajuan-cuti.')->middleware('menu.access:pengajuan-cuti,view')->group(function () {
        Route::get('/', [PengajuanCutiController::class, 'index'])->name('index');
        Route::get('/data', [PengajuanCutiController::class, 'data'])->name('data');
        Route::get('/create', [PengajuanCutiController::class, 'create'])->name('create')->middleware('menu.access:pengajuan-cuti,add');
        Route::post('/', [PengajuanCutiController::class, 'store'])->name('store')->middleware('menu.access:pengajuan-cuti,add');
        Route::get('/check-saldo', [PengajuanCutiController::class, 'checkSaldo'])->name('check-saldo');
        Route::get('/{encryptedId}', [PengajuanCutiController::class, 'show'])->name('show');
        Route::get('/{encryptedId}/edit', [PengajuanCutiController::class, 'edit'])->name('edit')->middleware('menu.access:pengajuan-cuti,edit');
        Route::put('/{encryptedId}', [PengajuanCutiController::class, 'update'])->name('update')->middleware('menu.access:pengajuan-cuti,edit');
        Route::delete('/{encryptedId}', [PengajuanCutiController::class, 'destroy'])->name('destroy')->middleware('menu.access:pengajuan-cuti,delete');
        Route::post('/{encryptedId}/approve', [PengajuanCutiController::class, 'approve'])->name('approve')->middleware('menu.access:pengajuan-cuti,edit');
        Route::post('/{encryptedId}/reject', [PengajuanCutiController::class, 'reject'])->name('reject')->middleware('menu.access:pengajuan-cuti,edit');
    });

    // List Sertifikat
    Route::prefix('sertifikat')->name('sertifikat.')->middleware('menu.access:sertifikat,view')->group(function () {
        Route::get('/', [SertifikatController::class, 'index'])->name('index');
        Route::get('/data', [SertifikatController::class, 'data'])->name('data');
        Route::get('/generate-number', [SertifikatController::class, 'generateNumber'])->name('generate-number');
        Route::post('/bulk-sign', [SertifikatController::class, 'bulkSign'])->name('bulk-sign')->middleware('menu.access:sertifikat,edit');
        // Route::get('/create', [SertifikatController::class, 'create'])->name('create')->middleware('menu.access:sertifikat,add');
        // Route::post('/', [SertifikatController::class, 'store'])->name('store')->middleware('menu.access:sertifikat,add');
        Route::get('/{encryptedId}/preview', [SertifikatController::class, 'preview'])->name('preview');
        Route::get('/{encryptedId}/download-pdf', [SertifikatController::class, 'downloadPdf'])->name('download-pdf');
        Route::get('/{encryptedId}', [SertifikatController::class, 'show'])->name('show');
        Route::get('/{encryptedId}/edit', [SertifikatController::class, 'edit'])->name('edit')->middleware('menu.access:sertifikat,edit');
        Route::put('/{encryptedId}', [SertifikatController::class, 'update'])->name('update')->middleware('menu.access:sertifikat,edit');
        Route::post('/{encryptedId}/sign', [SertifikatController::class, 'sign'])->name('sign')->middleware('menu.access:sertifikat,edit');
        Route::delete('/{encryptedId}', [SertifikatController::class, 'destroy'])->name('destroy')->middleware('menu.access:sertifikat,delete');
    });

    // Saldo Cuti
    Route::prefix('saldo-cuti')->name('saldo-cuti.')->middleware('menu.access:saldo-cuti,view')->group(function () {
        Route::get('/', [SaldoCutiController::class, 'index'])->name('index');
        Route::get('/data', [SaldoCutiController::class, 'data'])->name('data');
        Route::get('/check-overlap', [SaldoCutiController::class, 'checkOverlap'])->name('check-overlap');
        Route::get('/create', [SaldoCutiController::class, 'create'])->name('create')->middleware('menu.access:saldo-cuti,add');
        Route::post('/', [SaldoCutiController::class, 'store'])->name('store')->middleware('menu.access:saldo-cuti,add');
        Route::get('/{encryptedId}/edit', [SaldoCutiController::class, 'edit'])->name('edit')->middleware('menu.access:saldo-cuti,edit');
        Route::put('/{encryptedId}', [SaldoCutiController::class, 'update'])->name('update')->middleware('menu.access:saldo-cuti,edit');
        Route::delete('/{encryptedId}', [SaldoCutiController::class, 'destroy'])->name('destroy')->middleware('menu.access:saldo-cuti,delete');
    });

    // Pengajuan FIMP
    Route::prefix('pengajuan-fimp')->name('pengajuan-fimp.')->middleware('menu.access:pengajuan-fimp,view')->group(function () {
        Route::get('/', [FimpController::class, 'index'])->name('index');
        Route::get('/data', [FimpController::class, 'data'])->name('data');
        Route::get('/create', [FimpController::class, 'create'])->name('create')->middleware('menu.access:pengajuan-fimp,add');
        Route::post('/', [FimpController::class, 'store'])->name('store')->middleware('menu.access:pengajuan-fimp,add');
        Route::get('/{encryptedId}', [FimpController::class, 'show'])->name('show');
        Route::get('/{encryptedId}/edit', [FimpController::class, 'edit'])->name('edit')->middleware('menu.access:pengajuan-fimp,edit');
        Route::put('/{encryptedId}', [FimpController::class, 'update'])->name('update')->middleware('menu.access:pengajuan-fimp,edit');
        Route::delete('/{encryptedId}', [FimpController::class, 'destroy'])->name('destroy')->middleware('menu.access:pengajuan-fimp,delete');
        Route::post('/{encryptedId}/approve', [FimpController::class, 'approve'])->name('approve')->middleware('menu.access:pengajuan-fimp,edit');
        Route::post('/{encryptedId}/reject', [FimpController::class, 'reject'])->name('reject')->middleware('menu.access:pengajuan-fimp,edit');
    });

    // Bidang Studi
    Route::prefix('bidang-studi')->name('bidang-studi.')->middleware('menu.access:bidang-studi,view')->group(function () {
        Route::get('/', [BidangStudiController::class, 'index'])->name('index');
        Route::get('/data', [BidangStudiController::class, 'data'])->name('data');
        Route::get('/create', [BidangStudiController::class, 'create'])->name('create')->middleware('menu.access:bidang-studi,add');
        Route::post('/', [BidangStudiController::class, 'store'])->name('store')->middleware('menu.access:bidang-studi,add');
        Route::get('/{encryptedId}/edit', [BidangStudiController::class, 'edit'])->name('edit')->middleware('menu.access:bidang-studi,edit');
        Route::put('/{encryptedId}', [BidangStudiController::class, 'update'])->name('update')->middleware('menu.access:bidang-studi,edit');
        Route::delete('/{encryptedId}', [BidangStudiController::class, 'destroy'])->name('destroy')->middleware('menu.access:bidang-studi,delete');
    });

    // User Management
    Route::prefix('settings/user-management')->name('user-management.')->middleware('menu.access:user-management,view')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/data', [UserManagementController::class, 'data'])->name('data');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create')->middleware('menu.access:user-management,add');
        Route::post('/', [UserManagementController::class, 'store'])->name('store')->middleware('menu.access:user-management,add');
        Route::get('/{encryptedId}/edit', [UserManagementController::class, 'edit'])->name('edit')->middleware('menu.access:user-management,edit');
        Route::put('/{encryptedId}', [UserManagementController::class, 'update'])->name('update')->middleware('menu.access:user-management,edit');
        Route::delete('/{encryptedId}', [UserManagementController::class, 'destroy'])->name('destroy')->middleware('menu.access:user-management,delete');
    });

    // Menu Management
    Route::prefix('settings/menu-management')->name('menu-management.')->middleware('menu.access:menu-management,view')->group(function () {
        Route::get('/', [MenuManagementController::class, 'index'])->name('index');
        Route::get('/data', [MenuManagementController::class, 'data'])->name('data');
        Route::get('/create', [MenuManagementController::class, 'create'])->name('create')->middleware('menu.access:menu-management,add');
        Route::post('/', [MenuManagementController::class, 'store'])->name('store')->middleware('menu.access:menu-management,add');
        Route::get('/{encryptedId}/edit', [MenuManagementController::class, 'edit'])->name('edit')->middleware('menu.access:menu-management,edit');
        Route::put('/{encryptedId}', [MenuManagementController::class, 'update'])->name('update')->middleware('menu.access:menu-management,edit');
        Route::delete('/{encryptedId}', [MenuManagementController::class, 'destroy'])->name('destroy')->middleware('menu.access:menu-management,delete');
    });

    // Grup Management
    Route::prefix('settings/grup-management')->name('grup-management.')->middleware('menu.access:grup-management,view')->group(function () {
        Route::get('/', [GrupManagementController::class, 'index'])->name('index');
        Route::get('/data', [GrupManagementController::class, 'data'])->name('data');
        Route::get('/create', [GrupManagementController::class, 'create'])->name('create')->middleware('menu.access:grup-management,add');
        Route::post('/', [GrupManagementController::class, 'store'])->name('store')->middleware('menu.access:grup-management,add');
        Route::get('/{encryptedId}/edit', [GrupManagementController::class, 'edit'])->name('edit')->middleware('menu.access:grup-management,edit');
        Route::put('/{encryptedId}', [GrupManagementController::class, 'update'])->name('update')->middleware('menu.access:grup-management,edit');
        Route::delete('/{encryptedId}', [GrupManagementController::class, 'destroy'])->name('destroy')->middleware('menu.access:grup-management,delete');
        Route::get('/{encryptedId}/hak-akses', [GrupManagementController::class, 'hakAkses'])->name('hak-akses')->middleware('menu.access:grup-management,edit');
        Route::put('/{encryptedId}/hak-akses', [GrupManagementController::class, 'hakAksesUpdate'])->name('hak-akses.update')->middleware('menu.access:grup-management,edit');
    });

    // Karyawan Management (Data Master)
    Route::prefix('data-master/karyawan')->name('karyawan.')->middleware('menu.access:karyawan,view')->group(function () {
        Route::get('/', [KaryawanController::class, 'index'])->name('index');
        Route::get('/data', [KaryawanController::class, 'data'])->name('data');
        Route::get('/create', [KaryawanController::class, 'create'])->name('create')->middleware('menu.access:karyawan,add');
        Route::post('/', [KaryawanController::class, 'store'])->name('store')->middleware('menu.access:karyawan,add');
        Route::get('/export', [KaryawanController::class, 'export'])->name('export');
        Route::get('/{encryptedId}/edit', [KaryawanController::class, 'edit'])->name('edit')->middleware('menu.access:karyawan,edit');
        Route::put('/{encryptedId}', [KaryawanController::class, 'update'])->name('update')->middleware('menu.access:karyawan,edit');
        Route::delete('/{encryptedId}', [KaryawanController::class, 'destroy'])->name('destroy')->middleware('menu.access:karyawan,delete');
    });

    // Siswa Management (Data Master)
    Route::prefix('data-master/siswa')->name('siswa.')->middleware('menu.access:siswa,view')->group(function () {
        Route::get('/', [SiswaController::class, 'index'])->name('index');
        Route::get('/data', [SiswaController::class, 'data'])->name('data');
        Route::get('/create', [SiswaController::class, 'create'])->name('create')->middleware('menu.access:siswa,add');
        Route::post('/', [SiswaController::class, 'store'])->name('store')->middleware('menu.access:siswa,add');
        Route::get('/export', [SiswaController::class, 'export'])->name('export');
        Route::get('/{encryptedId}', [SiswaController::class, 'show'])->name('show')->middleware('menu.access:siswa,view');
        Route::get('/{encryptedId}/edit', [SiswaController::class, 'edit'])->name('edit')->middleware('menu.access:siswa,edit');
        Route::put('/{encryptedId}', [SiswaController::class, 'update'])->name('update')->middleware('menu.access:siswa,edit');
        Route::delete('/{encryptedId}', [SiswaController::class, 'destroy'])->name('destroy')->middleware('menu.access:siswa,delete');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
