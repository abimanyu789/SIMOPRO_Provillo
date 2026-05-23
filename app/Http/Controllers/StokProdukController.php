<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\StokProduk;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StokProdukExport;
use App\Imports\StokProdukImport;

/**
 * StokProdukController - Manajemen Stok Produk Jadi.
 * Status stok: tersedia | menipis | habis.
 */
class StokProdukController extends Controller
{
    public function index(Request $request): Response
    {
        $query = StokProduk::with('produk');

        if ($request->filled('search')) {
            $query->whereHas('produk', fn ($q) => $q->where('nama_produk', 'like', '%' . $request->search . '%')
                ->orWhere('kode_produk', 'like', '%' . $request->search . '%'));
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'habis') {
                $query->where('jumlah_stok', '<=', 0);
            } elseif ($status === 'menipis') {
                $query->where('jumlah_stok', '>', 0)->whereColumn('jumlah_stok', '<', 'stok_minimum');
            } elseif ($status === 'tersedia') {
                $query->whereColumn('jumlah_stok', '>=', 'stok_minimum');
            }
        }

        if ($request->filled('kategori')) {
            $query->whereHas('produk', fn ($q) => $q->where('kategori', $request->kategori));
        }

        $stokProduk  = $query->paginate(10)->withQueryString();
        $kategoriList = Produk::distinct()->pluck('kategori');
        $produkTanpaStok = Produk::whereDoesntHave('stok')->get();

        return Inertia::render('inventory/stok-produk/Index', [
            'stokProduk' => $stokProduk,
            'kategoriList' => $kategoriList,
            'produkTanpaStok' => $produkTanpaStok,
            'filters' => $request->only(['search', 'status', 'kategori']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'produk_id'    => ['required', 'exists:produk,id', 'unique:stok_produk,produk_id'],
            'jumlah_stok'  => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
        ], [
            'produk_id.required'  => 'Produk wajib dipilih.',
            'produk_id.unique'    => 'Stok untuk produk ini sudah ada.',
            'jumlah_stok.required' => 'Jumlah stok wajib diisi.',
            'stok_minimum.required' => 'Stok minimum wajib diisi.',
        ]);

        StokProduk::create($validated);
        return redirect()->route('stok-produk.index')
            ->with('success', 'Data stok produk berhasil ditambahkan.');
    }

    public function show(StokProduk $stokProduk): \Illuminate\Http\JsonResponse
    {
        $stokProduk->load('produk');
        return response()->json($stokProduk);
    }

    public function update(Request $request, StokProduk $stokProduk): RedirectResponse
    {
        $validated = $request->validate([
            'jumlah_stok'  => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
        ]);

        $stokProduk->update($validated);
        return redirect()->route('stok-produk.index')
            ->with('success', 'Data stok berhasil diperbarui.');
    }

    public function destroy(StokProduk $stokProduk): RedirectResponse
    {
        $stokProduk->delete();
        return redirect()->route('stok-produk.index')
            ->with('success', 'Data stok berhasil dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new StokProdukExport, 'stok-produk-' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $stokProduk = StokProduk::with('produk')->get();
        $pdf = Pdf::loadView('exports.stok-produk-pdf', compact('stokProduk'))->setPaper('a4', 'landscape');
        return $pdf->download('stok-produk-' . date('Y-m-d') . '.pdf');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'mimes:xlsx,xls']]);
        Excel::import(new StokProdukImport, $request->file('file'));
        return redirect()->route('stok-produk.index')->with('success', 'Data stok berhasil diimport.');
    }
}
