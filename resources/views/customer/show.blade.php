@extends('layouts.app')

@section('title', 'Detail Customer - ' . $customer->nama)
@section('page-title', 'Detail Customer')
@section('breadcrumb', 'Customer / ' . $customer->nama)

@section('header-actions')
    <a href="{{ route('customer.index') }}" class="btn btn-outline btn-sm">
        <i class="ri-arrow-left-line"></i> Kembali
    </a>
@endsection

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Kolom Kiri: Profil & Ringkasan --}}
    <div class="xl:col-span-1 space-y-5">
        <div class="card p-6 text-center">
            <div class="w-20 h-20 bg-teal-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-teal-600 font-bold text-3xl">
                {{ strtoupper(substr($customer->nama, 0, 1)) }}
            </div>
            <h3 class="text-xl font-bold text-gray-900">{{ $customer->nama }}</h3>
            <span class="badge badge-secondary mt-1">{{ $customer->kode_customer }}</span>

            <div class="mt-6 border-t pt-5 text-left space-y-3 text-sm">
                @if($customer->email)
                    <div>
                        <p class="text-xs text-gray-400">Email</p>
                        <p class="font-medium text-gray-800">{{ $customer->email }}</p>
                    </div>
                @endif
                @if($customer->no_hp)
                    <div>
                        <p class="text-xs text-gray-400">No HP</p>
                        <p class="font-medium text-gray-800">{{ $customer->no_hp }}</p>
                    </div>
                @endif
                @if($customer->alamat)
                    <div>
                        <p class="text-xs text-gray-400">Alamat</p>
                        <p class="font-medium text-gray-800">{{ $customer->alamat }}</p>
                        <p class="text-xs text-gray-500">{{ $customer->kota }}, {{ $customer->provinsi }} {{ $customer->kode_pos }}</p>
                    </div>
                @endif
                @if($customer->deskripsi)
                    <div>
                        <p class="text-xs text-gray-400">Catatan/Keterangan</p>
                        <p class="text-gray-600 italic">{{ $customer->deskripsi }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Riwayat Pesanan --}}
    <div class="xl:col-span-2 space-y-5">
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b">
                <h3 class="font-bold text-gray-900">Riwayat Pesanan Terbaru</h3>
            </div>
            @if($customer->pesanan->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="table-header">
                            <tr>
                                <th class="text-left px-5 py-3 font-semibold text-gray-600 text-xs uppercase">Kode</th>
                                <th class="text-left px-5 py-3 font-semibold text-gray-600 text-xs uppercase">Tanggal</th>
                                <th class="text-center px-5 py-3 font-semibold text-gray-600 text-xs uppercase">Status</th>
                                <th class="text-right px-5 py-3 font-semibold text-gray-600 text-xs uppercase">Total</th>
                                <th class="text-center px-5 py-3 font-semibold text-gray-600 text-xs uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($customer->pesanan as $order)
                                <tr class="table-row">
                                    <td class="px-5 py-3.5"><code class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded font-mono">{{ $order->kode_pesanan }}</code></td>
                                    <td class="px-5 py-3.5 text-gray-600">{{ $order->tanggal_pesanan?->format('d M Y') }}</td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="badge {{ $order->status_color }}">{{ $order->status_label }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-bold text-gray-900">{{ $order->total_format }}</td>
                                    <td class="px-5 py-3.5 text-center">
                                        <a href="{{ route('pesanan.show', $order) }}" class="btn btn-outline btn-icon btn-sm">
                                            <i class="ri-eye-line text-blue-500"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state py-12">
                    <i class="ri-shopping-bag-3-line"></i>
                    <h3>Belum Ada Riwayat Pesanan</h3>
                    <p>Pelanggan ini belum melakukan pemesanan.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
