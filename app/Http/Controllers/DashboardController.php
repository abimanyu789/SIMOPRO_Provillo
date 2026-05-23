<?php

namespace App\Http\Controllers;

use App\Models\ArusKas;
use App\Models\Customer;
use App\Models\Karyawan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Produksi;
use App\Models\StokBahanBaku;
use App\Models\StokProduk;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

/**
 * DashboardController - Halaman utama dashboard SIMOPRO.
 * Menampilkan ringkasan data operasional Provillo.
 */
class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard dengan statistik dan data ringkasan.
     */
    public function index(): View
    {
        $now = Carbon::now();
        $bulanIni = $now->month;
        $tahunIni = $now->year;

        // ---- STATISTIK UTAMA ----
        // Total pesanan aktif (belum closed)
        $totalPesananAktif = Pesanan::whereNotIn('status', ['closed'])->count();

        // Total pemasukan bulan ini
        $pemasukanBulanIni = ArusKas::pemasukan()
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->sum('jumlah');

        // Total pengeluaran bulan ini
        $pengeluaranBulanIni = ArusKas::pengeluaran()
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->sum('jumlah');

        // Saldo arus kas
        $saldoTotal = ArusKas::pemasukan()->sum('jumlah') - ArusKas::pengeluaran()->sum('jumlah');

        // ---- STATISTIK PENDUKUNG ----
        $totalProduk    = Produk::count();
        $totalKaryawan  = Karyawan::where('status', 'aktif')->count();
        $totalCustomer  = Customer::count();

        // Produksi hari ini
        $produksiHariIni = Produksi::whereDate('tanggal_produksi', $now->toDateString())->sum('jumlah_produksi');

        // ---- ALERT STOK ----
        // Stok produk menipis / habis
        $stokProdukAlert = StokProduk::with('produk')
            ->where(function ($q) {
                $q->whereColumn('jumlah_stok', '<', 'stok_minimum')
                    ->orWhere('jumlah_stok', '<=', 0);
            })
            ->get();

        // Stok bahan baku menipis / habis
        $stokBahanAlert = StokBahanBaku::with('bahanBaku')
            ->where(function ($q) {
                $q->whereColumn('jumlah_stok', '<', 'stok_minimum')
                    ->orWhere('jumlah_stok', '<=', 0);
            })
            ->get();

        // ---- DATA GRAFIK ARUS KAS (6 bulan terakhir) ----
        $chartData = $this->getChartData($tahunIni);

        // ---- PESANAN TERBARU ----
        $pesananTerbaru = Pesanan::with('customer')
            ->latest()
            ->take(5)
            ->get();

        // ---- DISTRIBUSI STATUS PESANAN ----
        $statusPesanan = Pesanan::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('dashboard.index', compact(
            'totalPesananAktif',
            'pemasukanBulanIni',
            'pengeluaranBulanIni',
            'saldoTotal',
            'totalProduk',
            'totalKaryawan',
            'totalCustomer',
            'produksiHariIni',
            'stokProdukAlert',
            'stokBahanAlert',
            'chartData',
            'pesananTerbaru',
            'statusPesanan'
        ));
    }

    /**
     * Data grafik arus kas bulanan untuk 12 bulan dalam satu tahun.
     * Mengembalikan array dengan label bulan, pemasukan, dan pengeluaran.
     */
    private function getChartData(int $tahun): array
    {
        $bulanLabels = [];
        $dataPemasukan = [];
        $dataPengeluaran = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $bulanLabels[] = Carbon::create($tahun, $bulan, 1)->translatedFormat('M');

            $dataPemasukan[] = ArusKas::pemasukan()
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->sum('jumlah');

            $dataPengeluaran[] = ArusKas::pengeluaran()
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->sum('jumlah');
        }

        return [
            'labels'       => $bulanLabels,
            'pemasukan'    => $dataPemasukan,
            'pengeluaran'  => $dataPengeluaran,
        ];
    }
}
