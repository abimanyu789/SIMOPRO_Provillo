<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\StokProdukController;
use App\Http\Controllers\StokBahanBakuController;
use App\Http\Controllers\ArusKasController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\InvoiceController;

/*
|--------------------------------------------------------------------------
| Web Routes - SIMOPRO Provillo
|--------------------------------------------------------------------------
| Semua route web aplikasi SIMOPRO didefinisikan di sini.
| Export/import routes HARUS didefinisikan SEBELUM resource route
| agar tidak terambil oleh route/{model} dari resource.
*/

// ============================================================
// RUTE GUEST (Belum Login)
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// ============================================================
// RUTE AUTHENTICATED (Sudah Login)
// ============================================================
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Auth - Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ---- DATA MASTER: PRODUK ----
    Route::get('produk/export/excel', [ProdukController::class, 'exportExcel'])->name('produk.export.excel');
    Route::get('produk/export/pdf', [ProdukController::class, 'exportPdf'])->name('produk.export.pdf');
    Route::post('produk/import', [ProdukController::class, 'import'])->name('produk.import');
    Route::resource('produk', ProdukController::class);

    // ---- DATA MASTER: BAHAN BAKU ----
    Route::get('bahan-baku/export/excel', [BahanBakuController::class, 'exportExcel'])->name('bahan-baku.export.excel');
    Route::get('bahan-baku/export/pdf', [BahanBakuController::class, 'exportPdf'])->name('bahan-baku.export.pdf');
    Route::post('bahan-baku/import', [BahanBakuController::class, 'import'])->name('bahan-baku.import');
    Route::resource('bahan-baku', BahanBakuController::class);

    // ---- DATA MASTER: KARYAWAN ----
    Route::get('karyawan/export/excel', [KaryawanController::class, 'exportExcel'])->name('karyawan.export.excel');
    Route::get('karyawan/export/pdf', [KaryawanController::class, 'exportPdf'])->name('karyawan.export.pdf');
    Route::post('karyawan/import', [KaryawanController::class, 'import'])->name('karyawan.import');
    Route::resource('karyawan', KaryawanController::class);

    // ---- DATA MASTER: CUSTOMER ----
    Route::get('customer/export/excel', [CustomerController::class, 'exportExcel'])->name('customer.export.excel');
    Route::get('customer/export/pdf', [CustomerController::class, 'exportPdf'])->name('customer.export.pdf');
    Route::post('customer/import', [CustomerController::class, 'import'])->name('customer.import');
    Route::resource('customer', CustomerController::class);

    // ---- PESANAN (ORDERS) ----
    Route::patch('pesanan/{pesanan}/status', [PesananController::class, 'updateStatus'])->name('pesanan.status');
    Route::get('pesanan/{pesanan}/invoice', [InvoiceController::class, 'show'])->name('pesanan.invoice');
    Route::get('pesanan/{pesanan}/invoice/print', [InvoiceController::class, 'print'])->name('pesanan.invoice.print');
    Route::get('pesanan/{pesanan}/invoice/pdf', [InvoiceController::class, 'pdf'])->name('pesanan.invoice.pdf');
    Route::resource('pesanan', PesananController::class);

    // ---- PRODUKSI ----
    Route::get('produksi/export/excel', [ProduksiController::class, 'exportExcel'])->name('produksi.export.excel');
    Route::get('produksi/export/pdf', [ProduksiController::class, 'exportPdf'])->name('produksi.export.pdf');
    Route::resource('produksi', ProduksiController::class);

    // ---- STOK PRODUK ----
    Route::get('stok-produk/export/excel', [StokProdukController::class, 'exportExcel'])->name('stok-produk.export.excel');
    Route::get('stok-produk/export/pdf', [StokProdukController::class, 'exportPdf'])->name('stok-produk.export.pdf');
    Route::post('stok-produk/import', [StokProdukController::class, 'import'])->name('stok-produk.import');
    Route::resource('stok-produk', StokProdukController::class);

    // ---- STOK BAHAN BAKU ----
    Route::get('stok-bahan-baku/export/excel', [StokBahanBakuController::class, 'exportExcel'])->name('stok-bahan-baku.export.excel');
    Route::get('stok-bahan-baku/export/pdf', [StokBahanBakuController::class, 'exportPdf'])->name('stok-bahan-baku.export.pdf');
    Route::post('stok-bahan-baku/import', [StokBahanBakuController::class, 'import'])->name('stok-bahan-baku.import');
    Route::resource('stok-bahan-baku', StokBahanBakuController::class);

    // ---- ARUS KAS ----
    Route::get('arus-kas/export/excel', [ArusKasController::class, 'exportExcel'])->name('arus-kas.export.excel');
    Route::get('arus-kas/export/pdf', [ArusKasController::class, 'exportPdf'])->name('arus-kas.export.pdf');
    Route::resource('arus-kas', ArusKasController::class);

    // ---- PENGATURAN ----
    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::put('/pengaturan/profil', [PengaturanController::class, 'updateProfil'])->name('pengaturan.profil');
    Route::put('/pengaturan/password', [PengaturanController::class, 'updatePassword'])->name('pengaturan.password');
    Route::put('/pengaturan/usaha', [PengaturanController::class, 'updateUsaha'])->name('pengaturan.usaha');
});
