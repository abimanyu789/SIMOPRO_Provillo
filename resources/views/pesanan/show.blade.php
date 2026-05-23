@extends('layouts.app')

@section('title', 'Detail Pesanan - ' . $pesanan->kode_pesanan)
@section('page-title', 'Detail Pesanan')
@section('breadcrumb', 'Pesanan / ' . $pesanan->kode_pesanan)

@section('header-actions')
    <a href="{{ route('pesanan.invoice', $pesanan) }}" class="btn btn-outline btn-sm">
        <i class="ri-file-text-line"></i> Invoice
    </a>
    <a href="{{ route('pesanan.index') }}" class="btn btn-outline btn-sm">
        <i class="ri-arrow-left-line"></i> Kembali
    </a>
@endsection

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Kolom Kiri: Info & Timeline --}}
    <div class="xl:col-span-1 space-y-5">

        {{-- Info Pesanan --}}
        <div class="card p-5">
            <h3 class="font-bold text-gray-900 mb-4">Informasi Pesanan</h3>
            <div class="space-y-3">
                <div class="flex items-start justify-between">
                    <span class="text-sm text-gray-500">Kode Pesanan</span>
                    <code class="text-sm bg-blue-50 text-blue-700 px-2 py-0.5 rounded font-mono font-semibold">{{ $pesanan->kode_pesanan }}</code>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Status</span>
                    @php $badgeClass = match($pesanan->status) { 'pending' => 'badge-warning', 'diproses' => 'badge-info', 'produksi' => 'badge-primary', 'selesai' => 'badge-success', 'closed' => 'badge-secondary', default => 'badge-secondary' }; @endphp
                    <span class="badge {{ $badgeClass }}">{{ $pesanan->status_label }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Tgl Pesanan</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $pesanan->tanggal_pesanan?->format('d M Y') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Target Kirim</span>
                    <span class="text-sm font-semibold {{ $pesanan->tanggal_kirim?->isPast() && $pesanan->status !== 'closed' ? 'text-red-500' : 'text-gray-900' }}">
                        {{ $pesanan->tanggal_kirim?->format('d M Y') ?? '-' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Total</span>
                    <span class="text-lg font-bold text-blue-600">{{ $pesanan->total_format }}</span>
                </div>
                @if($pesanan->catatan)
                    <div class="pt-2 border-t border-gray-100">
                        <p class="text-xs text-gray-500 mb-1">Catatan:</p>
                        <p class="text-sm text-gray-700">{{ $pesanan->catatan }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Info Customer --}}
        <div class="card p-5">
            <h3 class="font-bold text-gray-900 mb-4">Informasi Customer</h3>
            <div class="space-y-2">
                <p class="font-semibold text-gray-900">{{ $pesanan->customer?->nama }}</p>
                @if($pesanan->customer?->no_hp)
                    <p class="text-sm text-gray-600 flex items-center gap-2">
                        <i class="ri-phone-line text-gray-400"></i> {{ $pesanan->customer->no_hp }}
                    </p>
                @endif
                @if($pesanan->customer?->alamat)
                    <p class="text-sm text-gray-600 flex items-start gap-2">
                        <i class="ri-map-pin-line text-gray-400 mt-0.5"></i>
                        <span>{{ $pesanan->customer->alamat }}, {{ $pesanan->customer->kota }}, {{ $pesanan->customer->provinsi }}</span>
                    </p>
                @endif
            </div>
        </div>

        {{-- Update Status --}}
        @php
            $workflowNext = [
                'pending'  => 'diproses',
                'diproses' => 'produksi',
                'produksi' => 'selesai',
                'selesai'  => 'closed',
            ];
            $nextStatus = $workflowNext[$pesanan->status] ?? null;
        @endphp

        @if($nextStatus)
            <div class="card p-5">
                <h3 class="font-bold text-gray-900 mb-3">Update Status</h3>
                <form method="POST" action="{{ route('pesanan.status', $pesanan) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ $nextStatus }}">
                    <p class="text-sm text-gray-500 mb-3">
                        Ubah status ke: <strong class="text-gray-900 capitalize">{{ $nextStatus }}</strong>?
                    </p>
                    @if($nextStatus === 'selesai')
                        <p class="text-xs text-amber-600 bg-amber-50 rounded-lg p-2 mb-3">
                            <i class="ri-alert-line"></i> Mengubah ke "Selesai" akan mengurangi stok produk secara otomatis.
                        </p>
                    @endif
                    <button type="submit" class="btn btn-primary w-full btn-sm">
                        <i class="ri-arrow-right-circle-line"></i> Pindah ke {{ ucfirst($nextStatus) }}
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- Kolom Kanan: Detail Item & Produksi --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- Detail Item Pesanan --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-900">Detail Item Pesanan</h3>
                <p class="text-xs text-gray-500 mt-0.5">Harga merupakan snapshot saat pesanan dibuat</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-gray-600 text-xs uppercase">Produk</th>
                            <th class="text-center px-5 py-3 font-semibold text-gray-600 text-xs uppercase">Ukuran</th>
                            <th class="text-center px-5 py-3 font-semibold text-gray-600 text-xs uppercase">Jumlah</th>
                            <th class="text-right px-5 py-3 font-semibold text-gray-600 text-xs uppercase">Harga Satuan</th>
                            <th class="text-right px-5 py-3 font-semibold text-gray-600 text-xs uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($pesanan->detailPesanan as $detail)
                            <tr class="table-row">
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-gray-900">{{ $detail->nama_produk_snapshot }}</p>
                                    @if($detail->warna)
                                        <p class="text-xs text-gray-400">Warna: {{ $detail->warna }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center text-gray-600">{{ $detail->ukuran ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="font-semibold">{{ $detail->jumlah }}</span>
                                    @if($detail->jumlah_terkirim > 0)
                                        <span class="text-xs text-green-600 ml-1">({{ $detail->jumlah_terkirim }} terkirim)</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right text-gray-600">
                                    Rp {{ number_format($detail->harga_satuan_snapshot, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-bold text-gray-900">
                                    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                        <tr class="bg-blue-50">
                            <td colspan="4" class="px-5 py-3 text-right font-bold text-gray-900">Total:</td>
                            <td class="px-5 py-3 text-right font-bold text-blue-700 text-base">{{ $pesanan->total_format }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Log Produksi --}}
        @if($pesanan->produksi->count() > 0)
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900">Log Produksi</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="table-header">
                            <tr>
                                <th class="text-left px-5 py-3 font-semibold text-gray-600 text-xs uppercase">Karyawan</th>
                                <th class="text-left px-5 py-3 font-semibold text-gray-600 text-xs uppercase">Tanggal</th>
                                <th class="text-center px-5 py-3 font-semibold text-gray-600 text-xs uppercase">Jumlah</th>
                                <th class="text-right px-5 py-3 font-semibold text-gray-600 text-xs uppercase">Total Upah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($pesanan->produksi as $prod)
                                <tr class="table-row">
                                    <td class="px-5 py-3.5 font-medium text-gray-900">{{ $prod->karyawan?->nama }}</td>
                                    <td class="px-5 py-3.5 text-gray-600">{{ $prod->tanggal_produksi?->format('d M Y') }}</td>
                                    <td class="px-5 py-3.5 text-center font-semibold text-gray-900">{{ $prod->jumlah_produksi }} psg</td>
                                    <td class="px-5 py-3.5 text-right font-semibold text-gray-900">{{ $prod->total_upah_format }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
