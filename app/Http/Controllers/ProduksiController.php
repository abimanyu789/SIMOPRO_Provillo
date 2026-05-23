<?php

namespace App\Http\Controllers;

use App\Models\ArusKas;
use App\Models\Karyawan;
use App\Models\Pesanan;
use App\Models\Produksi;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProduksiExport;

/**
 * ProduksiController - Manajemen Log Produksi Harian Karyawan.
 * Upah karyawan dihitung otomatis: upah_per_item × jumlah_produksi.
 * Setiap log produksi otomatis mencatat pengeluaran di arus kas.
 */
class ProduksiController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Produksi::with(['karyawan', 'pesanan.customer'])->latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_produksi', 'like', '%' . $request->search . '%')
                    ->orWhereHas('karyawan', fn ($kq) => $kq->where('nama', 'like', '%' . $request->search . '%'));
            });
        }

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_produksi', $request->tanggal);
        }

        $produksi = $query->paginate(10)->withQueryString();
        $karyawanList = Karyawan::where('status', 'aktif')->orderBy('nama')->get();
        $pesananList = Pesanan::whereIn('status', ['diproses', 'produksi'])->with('customer')->get();

        // Statistik total upah bulan ini
        $totalUpahBulanIni = (float) Produksi::whereMonth('tanggal_produksi', now()->month)
            ->whereYear('tanggal_produksi', now()->year)
            ->sum('total_upah');

        return Inertia::render('transactional/produksi/Index', [
            'produksi' => $produksi,
            'karyawanList' => $karyawanList,
            'pesananList' => $pesananList,
            'totalUpahBulanIni' => $totalUpahBulanIni,
            'filters' => $request->only(['search', 'karyawan_id', 'tanggal']),
        ]);
    }

    /**
     * Simpan log produksi baru.
     * Hitung total upah otomatis dan catat di arus kas.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'karyawan_id'      => ['required', 'exists:karyawan,id'],
            'pesanan_id'       => ['required', 'exists:pesanan,id'],
            'tanggal_produksi' => ['required', 'date', 'before_or_equal:today'],
            'jumlah_produksi'  => ['required', 'integer', 'min:1'],
            'upah_per_item'    => ['required', 'numeric', 'min:0'],
            'catatan'          => ['nullable', 'string'],
        ], [
            'karyawan_id.required'      => 'Karyawan wajib dipilih.',
            'pesanan_id.required'       => 'Pesanan wajib dipilih.',
            'tanggal_produksi.required' => 'Tanggal produksi wajib diisi.',
            'jumlah_produksi.required'  => 'Jumlah produksi wajib diisi.',
            'upah_per_item.required'    => 'Upah per item wajib diisi.',
        ]);

        DB::transaction(function () use ($validated) {
            // Hitung total upah otomatis
            $totalUpah = $validated['upah_per_item'] * $validated['jumlah_produksi'];
            $kodeProduksi = $this->generateKodeProduksi();

            $produksi = Produksi::create([
                'kode_produksi'    => $kodeProduksi,
                'karyawan_id'      => $validated['karyawan_id'],
                'pesanan_id'       => $validated['pesanan_id'],
                'tanggal_produksi' => $validated['tanggal_produksi'],
                'jumlah_produksi'  => $validated['jumlah_produksi'],
                'upah_per_item'    => $validated['upah_per_item'],
                'total_upah'       => $totalUpah, // Kalkulasi otomatis
                'catatan'          => $validated['catatan'] ?? null,
            ]);

            // Update status pesanan ke produksi jika masih diproses
            $pesanan = Pesanan::find($validated['pesanan_id']);
            if ($pesanan && $pesanan->status === 'diproses') {
                $pesanan->update(['status' => 'produksi']);
            }

            // Otomatis catat pengeluaran upah di arus kas
            $karyawan = \App\Models\Karyawan::find($validated['karyawan_id']);
            ArusKas::create([
                'kode_transaksi'  => 'AK-' . date('Ym') . '-' . str_pad(ArusKas::withTrashed()->count() + 1, 4, '0', STR_PAD_LEFT),
                'jenis'           => 'pengeluaran',
                'kategori'        => 'Upah Produksi',
                'deskripsi'       => 'Upah produksi ' . ($karyawan->nama ?? 'Karyawan') . ' - #' . $kodeProduksi,
                'jumlah'          => $totalUpah,
                'tanggal'         => $validated['tanggal_produksi'],
                'referensi_id'    => $produksi->id,
                'referensi_type'  => Produksi::class,
            ]);
        });

        return redirect()->route('produksi.index')
            ->with('success', 'Log produksi berhasil disimpan. Upah telah dicatat di arus kas.');
    }

    public function show(Produksi $produksi): \Illuminate\Http\JsonResponse
    {
        $produksi->load(['karyawan', 'pesanan.customer', 'pesanan.detailPesanan.produk']);
        return response()->json($produksi);
    }

    public function update(Request $request, Produksi $produksi): RedirectResponse
    {
        $validated = $request->validate([
            'jumlah_produksi' => ['required', 'integer', 'min:1'],
            'upah_per_item'   => ['required', 'numeric', 'min:0'],
            'catatan'         => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($produksi, $validated) {
            $totalUpah = $validated['upah_per_item'] * $validated['jumlah_produksi'];
            $produksi->update([...$validated, 'total_upah' => $totalUpah]);

            // Update arus kas terkait
            ArusKas::where('referensi_id', $produksi->id)
                ->where('referensi_type', Produksi::class)
                ->update(['jumlah' => $totalUpah]);
        });

        return redirect()->route('produksi.index')
            ->with('success', 'Data produksi berhasil diperbarui.');
    }

    public function destroy(Produksi $produksi): RedirectResponse
    {
        DB::transaction(function () use ($produksi) {
            // Hapus arus kas terkait
            ArusKas::where('referensi_id', $produksi->id)
                ->where('referensi_type', Produksi::class)
                ->delete();
            $produksi->delete();
        });

        return redirect()->route('produksi.index')
            ->with('success', 'Data produksi berhasil dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new ProduksiExport, 'data-produksi-' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $produksi = Produksi::with(['karyawan', 'pesanan'])->get();
        $pdf = Pdf::loadView('exports.produksi-pdf', compact('produksi'))->setPaper('a4', 'landscape');
        return $pdf->download('data-produksi-' . date('Y-m-d') . '.pdf');
    }

    private function generateKodeProduksi(): string
    {
        $prefix = 'PROD-' . date('Ym') . '-';
        $last = Produksi::withTrashed()->where('kode_produksi', 'like', $prefix . '%')
            ->orderByDesc('kode_produksi')->first();
        $number = $last ? (int) substr($last->kode_produksi, -4) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
