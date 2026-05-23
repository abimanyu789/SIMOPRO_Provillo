<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\StokProduk;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProdukExport;
use App\Imports\ProdukImport;

/**
 * ProdukController - Data Master Produk Sepatu Provillo.
 * CRUD + Export PDF/Excel + Import Excel.
 */
class ProdukController extends Controller
{
    /**
     * Tampilkan daftar produk dengan filter dan pencarian.
     */
    public function index(Request $request): View
    {
        $query = Produk::with('stok');

        // Filter pencarian
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_produk', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_produk', 'like', '%' . $request->search . '%');
            });
        }

        // Filter kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $produk = $query->latest()->paginate(10)->withQueryString();

        // Daftar kategori untuk dropdown filter
        $kategoriList = Produk::distinct()->pluck('kategori');

        return view('produk.index', compact('produk', 'kategoriList'));
    }

    /**
     * Simpan produk baru ke database.
     * Otomatis buat record stok kosong dan generate kode produk.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi input
        $validated = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'kategori'    => ['required', 'string', 'max:100'],
            'harga_jual'  => ['required', 'numeric', 'min:0'],
            'deskripsi'   => ['nullable', 'string'],
            'foto'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'kategori.required'    => 'Kategori wajib diisi.',
            'harga_jual.required'  => 'Harga jual wajib diisi.',
            'harga_jual.numeric'   => 'Harga jual harus berupa angka.',
            'foto.image'           => 'File harus berupa gambar.',
            'foto.max'             => 'Ukuran foto maksimal 2MB.',
        ]);

        // Gunakan database transaction untuk konsistensi data
        DB::transaction(function () use ($request, $validated) {
            // Generate kode produk otomatis
            $validated['kode_produk'] = $this->generateKodeProduk();

            // Upload foto jika ada
            if ($request->hasFile('foto')) {
                $validated['foto'] = $request->file('foto')->store('produk', 'public');
            }

            // Buat produk baru
            $produk = Produk::create($validated);

            // Otomatis buat record stok produk dengan nilai awal 0
            StokProduk::create([
                'produk_id'    => $produk->id,
                'jumlah_stok'  => 0,
                'stok_minimum' => 10,
            ]);
        });

        return redirect()->route('produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail produk (untuk modal).
     */
    public function show(Produk $produk): View
    {
        $produk->load('stok');
        return view('produk.show', compact('produk'));
    }

    /**
     * Update data produk.
     * Harga yang diubah tidak mempengaruhi pesanan yang sudah ada (snapshot).
     */
    public function update(Request $request, Produk $produk): RedirectResponse
    {
        $validated = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'kategori'    => ['required', 'string', 'max:100'],
            'harga_jual'  => ['required', 'numeric', 'min:0'],
            'deskripsi'   => ['nullable', 'string'],
            'foto'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Proses upload foto baru jika ada
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($produk->foto) {
                Storage::disk('public')->delete($produk->foto);
            }
            $validated['foto'] = $request->file('foto')->store('produk', 'public');
        }

        $produk->update($validated);

        return redirect()->route('produk.index')
            ->with('success', 'Data produk berhasil diperbarui.');
    }

    /**
     * Hapus produk (soft delete).
     */
    public function destroy(Produk $produk): RedirectResponse
    {
        // Cek apakah produk digunakan di pesanan aktif
        $adaPesananAktif = $produk->detailPesanan()
            ->whereHas('pesanan', fn ($q) => $q->whereNotIn('status', ['closed']))
            ->exists();

        if ($adaPesananAktif) {
            return back()->with('error', 'Produk tidak dapat dihapus karena masih digunakan di pesanan aktif.');
        }

        // Hapus foto dari storage
        if ($produk->foto) {
            Storage::disk('public')->delete($produk->foto);
        }

        $produk->delete();

        return redirect()->route('produk.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Export data produk ke Excel.
     */
    public function exportExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new ProdukExport, 'data-produk-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export data produk ke PDF.
     */
    public function exportPdf()
    {
        $produk = Produk::with('stok')->get();
        $pdf = Pdf::loadView('exports.produk-pdf', compact('produk'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('data-produk-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Import data produk dari Excel.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'mimes:xlsx,xls', 'max:5120'],
        ], [
            'file.required' => 'File Excel wajib dipilih.',
            'file.mimes'    => 'File harus berformat Excel (.xlsx atau .xls).',
        ]);

        Excel::import(new ProdukImport, $request->file('file'));

        return redirect()->route('produk.index')
            ->with('success', 'Data produk berhasil diimport dari Excel.');
    }

    /**
     * Generate kode produk otomatis format: PRD-YYYYMM-XXXX
     */
    private function generateKodeProduk(): string
    {
        $prefix = 'PRD-' . date('Ym') . '-';
        $lastProduk = Produk::withTrashed()
            ->where('kode_produk', 'like', $prefix . '%')
            ->orderByDesc('kode_produk')
            ->first();

        $number = $lastProduk
            ? (int) substr($lastProduk->kode_produk, -4) + 1
            : 1;

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
