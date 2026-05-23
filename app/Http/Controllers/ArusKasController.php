<?php

namespace App\Http\Controllers;

use App\Models\ArusKas;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArusKasExport;
use Carbon\Carbon;

/**
 * ArusKasController - Manajemen Arus Kas (Cash Flow).
 * Mencatat semua transaksi keuangan masuk dan keluar.
 * Saldo dihitung real-time dari total pemasukan - pengeluaran.
 */
class ArusKasController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ArusKas::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_transaksi', 'like', '%' . $request->search . '%')
                    ->orWhere('deskripsi', 'like', '%' . $request->search . '%')
                    ->orWhere('kategori', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
        }

        $arusKas = $query->latest('tanggal')->paginate(10)->withQueryString();

        // Hitung saldo real-time
        $totalPemasukan   = (float) ArusKas::pemasukan()->sum('jumlah');
        $totalPengeluaran = (float) ArusKas::pengeluaran()->sum('jumlah');
        $saldo            = (float) ($totalPemasukan - $totalPengeluaran);

        // Saldo bulan ini
        $bulanIni = Carbon::now();
        $pemasukanBulan   = (float) ArusKas::pemasukan()->whereMonth('tanggal', $bulanIni->month)->whereYear('tanggal', $bulanIni->year)->sum('jumlah');
        $pengeluaranBulan = (float) ArusKas::pengeluaran()->whereMonth('tanggal', $bulanIni->month)->whereYear('tanggal', $bulanIni->year)->sum('jumlah');

        // Daftar kategori
        $kategoriList = ArusKas::distinct()->pluck('kategori');

        return Inertia::render('cash-flow/arus-kas/Index', [
            'arusKas' => $arusKas,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldo' => $saldo,
            'pemasukanBulan' => $pemasukanBulan,
            'pengeluaranBulan' => $pengeluaranBulan,
            'kategoriList' => $kategoriList,
            'filters' => $request->only(['search', 'jenis', 'kategori', 'tanggal_dari', 'tanggal_sampai']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis'     => ['required', 'in:pemasukan,pengeluaran'],
            'kategori'  => ['required', 'string', 'max:100'],
            'deskripsi' => ['required', 'string', 'max:500'],
            'jumlah'    => ['required', 'numeric', 'min:0'],
            'tanggal'   => ['required', 'date'],
        ], [
            'jenis.required'     => 'Jenis transaksi wajib dipilih.',
            'kategori.required'  => 'Kategori wajib diisi.',
            'deskripsi.required' => 'Deskripsi wajib diisi.',
            'jumlah.required'    => 'Jumlah wajib diisi.',
            'tanggal.required'   => 'Tanggal wajib diisi.',
        ]);

        $validated['kode_transaksi'] = $this->generateKodeTransaksi();
        ArusKas::create($validated);

        return redirect()->route('arus-kas.index')
            ->with('success', 'Transaksi arus kas berhasil dicatat.');
    }

    public function show(ArusKas $arusKas): \Illuminate\Http\JsonResponse
    {
        return response()->json($arusKas);
    }

    public function update(Request $request, ArusKas $arusKas): RedirectResponse
    {
        $validated = $request->validate([
            'jenis'     => ['required', 'in:pemasukan,pengeluaran'],
            'kategori'  => ['required', 'string', 'max:100'],
            'deskripsi' => ['required', 'string', 'max:500'],
            'jumlah'    => ['required', 'numeric', 'min:0'],
            'tanggal'   => ['required', 'date'],
        ]);

        $arusKas->update($validated);
        return redirect()->route('arus-kas.index')
            ->with('success', 'Data transaksi berhasil diperbarui.');
    }

    public function destroy(ArusKas $arusKas): RedirectResponse
    {
        $arusKas->delete();
        return redirect()->route('arus-kas.index')
            ->with('success', 'Data transaksi berhasil dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new ArusKasExport, 'laporan-arus-kas-' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $arusKas = ArusKas::orderBy('tanggal', 'desc')->get();
        $totalPemasukan = ArusKas::pemasukan()->sum('jumlah');
        $totalPengeluaran = ArusKas::pengeluaran()->sum('jumlah');
        $saldo = $totalPemasukan - $totalPengeluaran;

        $pdf = Pdf::loadView('exports.arus-kas-pdf', compact('arusKas', 'totalPemasukan', 'totalPengeluaran', 'saldo'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('laporan-arus-kas-' . date('Y-m-d') . '.pdf');
    }

    private function generateKodeTransaksi(): string
    {
        $prefix = 'AK-' . date('Ym') . '-';
        $last = ArusKas::withTrashed()->where('kode_transaksi', 'like', $prefix . '%')
            ->orderByDesc('kode_transaksi')->first();
        $number = $last ? (int) substr($last->kode_transaksi, -4) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
