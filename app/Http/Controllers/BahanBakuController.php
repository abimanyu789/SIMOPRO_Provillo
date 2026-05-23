<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\StokBahanBaku;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BahanBakuExport;
use App\Imports\BahanBakuImport;

/**
 * BahanBakuController - Data Master Bahan Baku Produksi.
 */
class BahanBakuController extends Controller
{
    public function index(Request $request): View
    {
        $query = BahanBaku::with('stok');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_bahan', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_bahan', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $bahanBaku = $query->latest()->paginate(10)->withQueryString();
        $kategoriList = BahanBaku::distinct()->pluck('kategori');

        return view('bahan-baku.index', compact('bahanBaku', 'kategoriList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_bahan'  => ['required', 'string', 'max:255'],
            'kategori'    => ['required', 'string', 'max:100'],
            'satuan'      => ['required', 'string', 'max:50'],
            'harga_beli'  => ['required', 'numeric', 'min:0'],
            'deskripsi'   => ['nullable', 'string'],
        ], [
            'nama_bahan.required' => 'Nama bahan baku wajib diisi.',
            'kategori.required'   => 'Kategori wajib diisi.',
            'satuan.required'     => 'Satuan wajib diisi.',
            'harga_beli.required' => 'Harga beli wajib diisi.',
        ]);

        DB::transaction(function () use ($validated) {
            $validated['kode_bahan'] = $this->generateKodeBahan();
            $bahan = BahanBaku::create($validated);

            // Buat record stok bahan baku otomatis
            StokBahanBaku::create([
                'bahan_baku_id' => $bahan->id,
                'jumlah_stok'   => 0,
                'stok_minimum'  => 10,
                'satuan'        => $validated['satuan'],
            ]);
        });

        return redirect()->route('bahan-baku.index')
            ->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function show(BahanBaku $bahanBaku): View
    {
        $bahanBaku->load('stok');
        return view('bahan-baku.show', compact('bahanBaku'));
    }

    public function update(Request $request, BahanBaku $bahanBaku): RedirectResponse
    {
        $validated = $request->validate([
            'nama_bahan'  => ['required', 'string', 'max:255'],
            'kategori'    => ['required', 'string', 'max:100'],
            'satuan'      => ['required', 'string', 'max:50'],
            'harga_beli'  => ['required', 'numeric', 'min:0'],
            'deskripsi'   => ['nullable', 'string'],
        ]);

        $bahanBaku->update($validated);

        return redirect()->route('bahan-baku.index')
            ->with('success', 'Data bahan baku berhasil diperbarui.');
    }

    public function destroy(BahanBaku $bahanBaku): RedirectResponse
    {
        $bahanBaku->delete();
        return redirect()->route('bahan-baku.index')
            ->with('success', 'Bahan baku berhasil dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new BahanBakuExport, 'data-bahan-baku-' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $bahanBaku = BahanBaku::with('stok')->get();
        $pdf = Pdf::loadView('exports.bahan-baku-pdf', compact('bahanBaku'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('data-bahan-baku-' . date('Y-m-d') . '.pdf');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'mimes:xlsx,xls']]);
        Excel::import(new BahanBakuImport, $request->file('file'));
        return redirect()->route('bahan-baku.index')
            ->with('success', 'Data bahan baku berhasil diimport.');
    }

    private function generateKodeBahan(): string
    {
        $prefix = 'BB-' . date('Ym') . '-';
        $last = BahanBaku::withTrashed()->where('kode_bahan', 'like', $prefix . '%')
            ->orderByDesc('kode_bahan')->first();
        $number = $last ? (int) substr($last->kode_bahan, -4) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
