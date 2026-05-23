<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\Pengaturan;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * InvoiceController - Mengelola tampilan dan cetak invoice & label pengiriman.
 */
class InvoiceController extends Controller
{
    /**
     * Tampilkan preview invoice dan label pengiriman.
     */
    public function show(Pesanan $pesanan): View
    {
        $pesanan->load(['customer', 'detailPesanan.produk']);
        $pengaturan = Pengaturan::getSetting();
        return view('pesanan.invoice', compact('pesanan', 'pengaturan'));
    }

    /**
     * Print invoice (buka halaman print-friendly).
     */
    public function print(Pesanan $pesanan): View
    {
        $pesanan->load(['customer', 'detailPesanan.produk']);
        $pengaturan = Pengaturan::getSetting();
        return view('pesanan.invoice-print', compact('pesanan', 'pengaturan'));
    }

    /**
     * Download invoice dan label pengiriman sebagai PDF.
     */
    public function pdf(Pesanan $pesanan)
    {
        $pesanan->load(['customer', 'detailPesanan.produk']);
        $pengaturan = Pengaturan::getSetting();

        $pdf = Pdf::loadView('pesanan.invoice-pdf', compact('pesanan', 'pengaturan'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('invoice-' . $pesanan->kode_pesanan . '.pdf');
    }
}
