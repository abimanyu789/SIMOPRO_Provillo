<?php

namespace App\Http\Controllers;

use App\Models\ArusKas;
use App\Models\Customer;
use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\StokProduk;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * PesananController - Manajemen Pesanan (Order Management).
 *
 * Mengelola workflow pesanan dari Pending hingga Closed.
 * Harga produk di-snapshot saat pesanan dibuat.
 *
 * Workflow: pending → diproses → produksi → selesai → closed
 */
class PesananController extends Controller
{
    /**
     * Daftar semua pesanan dengan filter status dan pencarian.
     */
    public function index(Request $request): View
    {
        $query = Pesanan::with('customer')->latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_pesanan', 'like', '%' . $request->search . '%')
                    ->orWhereHas('customer', fn ($cq) => $cq->where('nama', 'like', '%' . $request->search . '%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pesanan = $query->paginate(10)->withQueryString();
        $customers = Customer::orderBy('nama')->get();

        // Statistik cepat per status
        $statusCount = Pesanan::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('pesanan.index', compact('pesanan', 'customers', 'statusCount'));
    }

    /**
     * Buat pesanan baru dengan snapshot harga produk.
     *
     * PENTING: Harga diambil dari master produk saat ini dan disimpan sebagai snapshot.
     * Perubahan harga master setelahnya tidak akan mempengaruhi pesanan ini.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id'      => ['required', 'exists:customers,id'],
            'tanggal_pesanan'  => ['required', 'date'],
            'tanggal_kirim'    => ['nullable', 'date', 'after_or_equal:tanggal_pesanan'],
            'catatan'          => ['nullable', 'string'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.produk_id'=> ['required', 'exists:produk,id'],
            'items.*.jumlah'   => ['required', 'integer', 'min:1'],
            'items.*.ukuran'   => ['nullable', 'string'],
            'items.*.warna'    => ['nullable', 'string'],
        ], [
            'customer_id.required' => 'Pelanggan wajib dipilih.',
            'tanggal_pesanan.required' => 'Tanggal pesanan wajib diisi.',
            'items.required'       => 'Minimal satu produk harus ditambahkan.',
        ]);

        DB::transaction(function () use ($validated) {
            // Buat header pesanan
            $totalHarga = 0;
            $kodePesanan = $this->generateKodePesanan();

            $pesanan = Pesanan::create([
                'kode_pesanan'    => $kodePesanan,
                'customer_id'     => $validated['customer_id'],
                'tanggal_pesanan' => $validated['tanggal_pesanan'],
                'tanggal_kirim'   => $validated['tanggal_kirim'] ?? null,
                'catatan'         => $validated['catatan'] ?? null,
                'status'          => 'pending',
                'total_harga'     => 0, // Akan di-update setelah kalkulasi
            ]);

            // Buat detail pesanan dengan snapshot harga
            foreach ($validated['items'] as $item) {
                $produk = Produk::findOrFail($item['produk_id']);

                // SNAPSHOT: Ambil harga dari master saat ini
                $subtotal = $produk->harga_jual * $item['jumlah'];
                $totalHarga += $subtotal;

                DetailPesanan::create([
                    'pesanan_id'           => $pesanan->id,
                    'produk_id'            => $produk->id,
                    'nama_produk_snapshot' => $produk->nama_produk, // Snapshot nama
                    'harga_satuan_snapshot'=> $produk->harga_jual,  // Snapshot harga
                    'jumlah'               => $item['jumlah'],
                    'jumlah_terkirim'      => 0,
                    'ukuran'               => $item['ukuran'] ?? null,
                    'warna'                => $item['warna'] ?? null,
                    'subtotal'             => $subtotal,
                ]);
            }

            // Update total harga pesanan
            $pesanan->update(['total_harga' => $totalHarga]);

            // Catat pemasukan di arus kas
            ArusKas::create([
                'kode_transaksi'  => 'AK-' . date('Ym') . '-' . str_pad(ArusKas::count() + 1, 4, '0', STR_PAD_LEFT),
                'jenis'           => 'pemasukan',
                'kategori'        => 'Penjualan',
                'deskripsi'       => 'Pesanan #' . $kodePesanan,
                'jumlah'          => $totalHarga,
                'tanggal'         => $validated['tanggal_pesanan'],
                'referensi_id'    => $pesanan->id,
                'referensi_type'  => Pesanan::class,
            ]);
        });

        return redirect()->route('pesanan.index')
            ->with('success', 'Pesanan berhasil dibuat.');
    }

    /**
     * Detail pesanan lengkap.
     */
    public function show(Pesanan $pesanan): View
    {
        $pesanan->load(['customer', 'detailPesanan.produk', 'produksi.karyawan']);
        return view('pesanan.show', compact('pesanan'));
    }

    /**
     * Update pesanan (hanya data non-keuangan).
     * Harga tidak bisa diubah setelah pesanan dibuat.
     */
    public function update(Request $request, Pesanan $pesanan): RedirectResponse
    {
        // Hanya pesanan berstatus pending yang bisa diedit penuh
        if (!in_array($pesanan->status, ['pending', 'diproses'])) {
            return back()->with('error', 'Pesanan ini tidak dapat diedit dalam status ' . $pesanan->status . '.');
        }

        $validated = $request->validate([
            'tanggal_kirim' => ['nullable', 'date'],
            'catatan'       => ['nullable', 'string'],
        ]);

        $pesanan->update($validated);

        return redirect()->route('pesanan.index')
            ->with('success', 'Pesanan berhasil diperbarui.');
    }

    /**
     * Update status pesanan sesuai workflow.
     * pending → diproses → produksi → selesai → closed
     */
    public function updateStatus(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,diproses,produksi,selesai,closed'],
        ]);

        // Validasi urutan workflow status
        $workflowOrder = ['pending', 'diproses', 'produksi', 'selesai', 'closed'];
        $currentIndex = array_search($pesanan->status, $workflowOrder);
        $newIndex = array_search($request->status, $workflowOrder);

        if ($newIndex !== $currentIndex + 1 && $newIndex !== $currentIndex - 1) {
            return back()->with('error', 'Perubahan status tidak sesuai alur workflow.');
        }

        $pesanan->update(['status' => $request->status]);

        // Jika status selesai, kurangi stok produk
        if ($request->status === 'selesai') {
            $this->kurangiStokProduk($pesanan);
        }

        return back()->with('success', 'Status pesanan berhasil diperbarui ke ' . ucfirst($request->status) . '.');
    }

    /**
     * Hapus pesanan (soft delete).
     * Hanya pesanan pending yang bisa dihapus.
     */
    public function destroy(Pesanan $pesanan): RedirectResponse
    {
        if ($pesanan->status !== 'pending') {
            return back()->with('error', 'Hanya pesanan berstatus pending yang dapat dihapus.');
        }

        DB::transaction(function () use ($pesanan) {
            // Hapus entri arus kas terkait
            ArusKas::where('referensi_id', $pesanan->id)
                ->where('referensi_type', Pesanan::class)
                ->delete();

            $pesanan->detailPesanan()->delete();
            $pesanan->delete();
        });

        return redirect()->route('pesanan.index')
            ->with('success', 'Pesanan berhasil dihapus.');
    }

    /**
     * Kurangi stok produk saat pesanan selesai.
     * Menggunakan DB transaction untuk integritas data.
     */
    private function kurangiStokProduk(Pesanan $pesanan): void
    {
        DB::transaction(function () use ($pesanan) {
            foreach ($pesanan->detailPesanan as $detail) {
                $stok = StokProduk::where('produk_id', $detail->produk_id)->first();
                if ($stok) {
                    $stok->decrement('jumlah_stok', $detail->jumlah);
                }
            }
        });
    }

    /**
     * Generate kode pesanan otomatis: PO-YYYYMM-XXXX
     */
    private function generateKodePesanan(): string
    {
        $prefix = 'PO-' . date('Ym') . '-';
        $last = Pesanan::withTrashed()->where('kode_pesanan', 'like', $prefix . '%')
            ->orderByDesc('kode_pesanan')->first();
        $number = $last ? (int) substr($last->kode_pesanan, -4) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
