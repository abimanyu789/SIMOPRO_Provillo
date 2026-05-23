@extends('layouts.app')

@section('title', 'Invoice - ' . $pesanan->kode_pesanan)
@section('page-title', 'Invoice & Label Pengiriman')
@section('breadcrumb', 'Pesanan / ' . $pesanan->kode_pesanan . ' / Invoice')

@section('header-actions')
    <a href="{{ route('pesanan.invoice.print', $pesanan) }}" target="_blank" class="btn btn-outline btn-sm">
        <i class="ri-printer-line"></i> Cetak Invoice
    </a>
    <a href="{{ route('pesanan.invoice.pdf', $pesanan) }}" class="btn btn-primary btn-sm">
        <i class="ri-file-pdf-line"></i> Download PDF
    </a>
    <a href="{{ route('pesanan.show', $pesanan) }}" class="btn btn-outline btn-sm">
        <i class="ri-arrow-left-line"></i> Kembali
    </a>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    {{-- INVOICE --}}
    <div class="card p-8" id="invoice-section">
        {{-- Header Invoice --}}
        <div class="flex items-start justify-between mb-8">
            <div class="flex items-center gap-4">
                @if($pengaturan->logo)
                    <img src="{{ $pengaturan->logo_url }}" alt="Logo" class="h-14 w-14 rounded-xl object-cover">
                @else
                    <div class="h-14 w-14 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center">
                        <i class="ri-shoe-line text-white text-2xl"></i>
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-black text-gray-900">{{ $pengaturan->nama_usaha }}</h1>
                    <p class="text-sm text-gray-500">{{ $pengaturan->deskripsi_usaha }}</p>
                    @if($pengaturan->alamat)
                        <p class="text-xs text-gray-400 mt-1">{{ $pengaturan->alamat }}</p>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-blue-600">INVOICE</h2>
                <p class="text-sm font-mono font-semibold text-gray-900 mt-1">{{ $pesanan->kode_pesanan }}</p>
                <p class="text-xs text-gray-500 mt-1">Tgl: {{ $pesanan->tanggal_pesanan?->format('d M Y') }}</p>
            </div>
        </div>

        {{-- Divider --}}
        <hr class="border-gray-200 mb-6">

        {{-- Info Customer & Pengiriman --}}
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Tagihan Kepada:</p>
                <p class="font-bold text-gray-900">{{ $pesanan->customer?->nama }}</p>
                @if($pesanan->customer?->no_hp)
                    <p class="text-sm text-gray-600">{{ $pesanan->customer->no_hp }}</p>
                @endif
                @if($pesanan->customer?->alamat)
                    <p class="text-sm text-gray-600">{{ $pesanan->customer->alamat }}</p>
                    <p class="text-sm text-gray-600">{{ $pesanan->customer->kota }}, {{ $pesanan->customer->provinsi }} {{ $pesanan->customer->kode_pos }}</p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Detail Pengiriman:</p>
                <div class="space-y-1">
                    <div class="flex justify-end gap-3">
                        <span class="text-sm text-gray-500">Status:</span>
                        @php $badgeClass = match($pesanan->status) { 'pending' => 'badge-warning', 'diproses' => 'badge-info', 'produksi' => 'badge-primary', 'selesai' => 'badge-success', 'closed' => 'badge-secondary', default => 'badge-secondary' }; @endphp
                        <span class="badge {{ $badgeClass }}">{{ $pesanan->status_label }}</span>
                    </div>
                    @if($pesanan->tanggal_kirim)
                        <div class="flex justify-end gap-3">
                            <span class="text-sm text-gray-500">Tgl Kirim:</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $pesanan->tanggal_kirim->format('d M Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tabel Item --}}
        <table class="w-full mb-6 text-sm">
            <thead>
                <tr class="bg-blue-600 text-white">
                    <th class="text-left px-4 py-3 rounded-tl-lg font-semibold text-xs uppercase">Produk</th>
                    <th class="text-center px-4 py-3 font-semibold text-xs uppercase">Ukuran</th>
                    <th class="text-center px-4 py-3 font-semibold text-xs uppercase">Qty</th>
                    <th class="text-right px-4 py-3 font-semibold text-xs uppercase">Harga Satuan</th>
                    <th class="text-right px-4 py-3 rounded-tr-lg font-semibold text-xs uppercase">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pesanan->detailPesanan as $idx => $detail)
                    <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $detail->nama_produk_snapshot }}
                            @if($detail->warna)
                                <span class="text-xs text-gray-400">({{ $detail->warna }})</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $detail->ukuran ?? '-' }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $detail->jumlah }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($detail->harga_satuan_snapshot, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-blue-200">
                    <td colspan="4" class="px-4 py-4 text-right font-black text-gray-900 text-base">TOTAL:</td>
                    <td class="px-4 py-4 text-right font-black text-blue-700 text-lg">{{ $pesanan->total_format }}</td>
                </tr>
            </tfoot>
        </table>

        @if($pesanan->catatan)
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-xs font-semibold text-gray-500 mb-1">Catatan:</p>
                <p class="text-sm text-gray-700">{{ $pesanan->catatan }}</p>
            </div>
        @endif

        <div class="mt-8 pt-6 border-t border-gray-100 text-center text-xs text-gray-400">
            <p>Terima kasih telah berbelanja di <strong>{{ $pengaturan->nama_usaha }}</strong></p>
            @if($pengaturan->no_hp)
                <p>Hubungi kami: {{ $pengaturan->no_hp }} | {{ $pengaturan->email }}</p>
            @endif
        </div>
    </div>

    {{-- LABEL PENGIRIMAN --}}
    <div class="card p-6" id="label-section">
        <div class="flex items-center gap-2 mb-4">
            <i class="ri-truck-line text-blue-600 text-xl"></i>
            <h3 class="font-bold text-gray-900">Label Pengiriman</h3>
        </div>
        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Pengirim:</p>
                    <p class="font-bold text-gray-900">{{ $pengaturan->nama_usaha }}</p>
                    @if($pengaturan->alamat)
                        <p class="text-sm text-gray-600 mt-1">{{ $pengaturan->alamat }}</p>
                    @endif
                    @if($pengaturan->no_hp)
                        <p class="text-sm text-gray-600">Telp: {{ $pengaturan->no_hp }}</p>
                    @endif
                </div>
                <div class="border-l border-gray-200 pl-6">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Penerima:</p>
                    <p class="font-bold text-xl text-gray-900">{{ $pesanan->customer?->nama }}</p>
                    @if($pesanan->customer?->alamat)
                        <p class="text-sm text-gray-600 mt-1">{{ $pesanan->customer->alamat }}</p>
                        <p class="text-sm text-gray-600">{{ $pesanan->customer->kota }}, {{ $pesanan->customer->provinsi }} {{ $pesanan->customer->kode_pos }}</p>
                    @endif
                    @if($pesanan->customer?->no_hp)
                        <p class="text-sm text-gray-600">Telp: {{ $pesanan->customer->no_hp }}</p>
                    @endif
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">No. Order:</p>
                    <p class="font-mono font-bold text-blue-700">{{ $pesanan->kode_pesanan }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">Jumlah Item:</p>
                    <p class="font-bold text-gray-900">{{ $pesanan->detailPesanan->sum('jumlah') }} psg</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
