<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\StokBahanBaku;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StokBahanBakuExport;
use App\Imports\StokBahanBakuImport;

/**
 * StokBahanBakuController - Manajemen Stok Bahan Baku Produksi.
 */
class StokBahanBakuController extends Controller
{
    public function index(Request $request): View
    {
        $query = StokBahanBaku::with('bahanBaku');

        if ($request->filled('search')) {
            $query->whereHas('bahanBaku', fn ($q) => $q->where('nama_bahan', 'like', '%' . $request->search . '%')
                ->orWhere('kode_bahan', 'like', '%' . $request->search . '%'));
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
            $query->whereHas('bahanBaku', fn ($q) => $q->where('kategori', $request->kategori));
        }

        $stokBahanBaku = $query->paginate(10)->withQueryString();
        $kategoriList = BahanBaku::distinct()->pluck('kategori');
        $bahanTanpaStok = BahanBaku::whereDoesntHave('stok')->get();

        return view('stok-bahan-baku.index', compact('stokBahanBaku', 'kategoriList', 'bahanTanpaStok'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bahan_baku_id' => ['required', 'exists:bahan_baku,id', 'unique:stok_bahan_baku,bahan_baku_id'],
            'jumlah_stok'   => ['required', 'integer', 'min:0'],
            'stok_minimum'  => ['required', 'integer', 'min:0'],
            'satuan'        => ['required', 'string'],
        ]);

        StokBahanBaku::create($validated);
        return redirect()->route('stok-bahan-baku.index')
            ->with('success', 'Data stok bahan baku berhasil ditambahkan.');
    }

    public function show(StokBahanBaku $stokBahanBaku): View
    {
        $stokBahanBaku->load('bahanBaku');
        return view('stok-bahan-baku.show', compact('stokBahanBaku'));
    }

    public function update(Request $request, StokBahanBaku $stokBahanBaku): RedirectResponse
    {
        $validated = $request->validate([
            'jumlah_stok'  => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'satuan'       => ['required', 'string'],
        ]);

        $stokBahanBaku->update($validated);
        return redirect()->route('stok-bahan-baku.index')
            ->with('success', 'Data stok berhasil diperbarui.');
    }

    public function destroy(StokBahanBaku $stokBahanBaku): RedirectResponse
    {
        $stokBahanBaku->delete();
        return redirect()->route('stok-bahan-baku.index')
            ->with('success', 'Data stok berhasil dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new StokBahanBakuExport, 'stok-bahan-baku-' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $stokBahanBaku = StokBahanBaku::with('bahanBaku')->get();
        $pdf = Pdf::loadView('exports.stok-bahan-baku-pdf', compact('stokBahanBaku'))->setPaper('a4', 'landscape');
        return $pdf->download('stok-bahan-baku-' . date('Y-m-d') . '.pdf');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'mimes:xlsx,xls']]);
        Excel::import(new StokBahanBakuImport, $request->file('file'));
        return redirect()->route('stok-bahan-baku.index')->with('success', 'Data stok berhasil diimport.');
    }
}
