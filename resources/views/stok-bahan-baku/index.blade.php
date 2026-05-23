@extends('layouts.app')
@section('title', 'Stok Bahan Baku')
@section('page-title', 'Manajemen Stok Bahan Baku')
@section('breadcrumb', 'Kelola stok bahan baku produksi Provillo')

@section('header-actions')
    <a href="{{ route('stok-bahan-baku.export.excel') }}" class="btn btn-outline btn-sm"><i class="ri-file-excel-2-line"></i> Export</a>
@endsection

@section('content')
<div class="card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48"><label class="form-label">Cari</label><div class="relative"><i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i><input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau kode bahan..." class="form-input pl-9"></div></div>
        <div class="min-w-36"><label class="form-label">Status</label><select name="status" class="form-input"><option value="">Semua</option><option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option><option value="menipis" {{ request('status') == 'menipis' ? 'selected' : '' }}>Menipis</option><option value="habis" {{ request('status') == 'habis' ? 'selected' : '' }}>Habis</option></select></div>
        <div class="flex gap-2"><button type="submit" class="btn btn-primary btn-sm">Filter</button>@if(request()->hasAny(['search','status']))<a href="{{ route('stok-bahan-baku.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif</div>
    </form>
</div>

<div class="card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b"><h3 class="font-semibold text-gray-900">Daftar Stok Bahan Baku <span class="text-sm font-normal text-gray-500">({{ $stokBahanBaku->total() }} data)</span></h3></div>
    @if($stokBahanBaku->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Bahan Baku</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Kategori</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Stok</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Minimum</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Satuan</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Status</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($stokBahanBaku as $stok)
                        <tr class="table-row">
                            <td class="px-5 py-4"><p class="font-semibold text-gray-900">{{ $stok->bahanBaku?->nama_bahan }}</p><code class="text-xs text-gray-400">{{ $stok->bahanBaku?->kode_bahan }}</code></td>
                            <td class="px-5 py-4"><span class="badge badge-info">{{ $stok->bahanBaku?->kategori }}</span></td>
                            <td class="px-5 py-4 text-center"><span class="text-2xl font-bold {{ $stok->jumlah_stok <= 0 ? 'text-red-600' : ($stok->jumlah_stok < $stok->stok_minimum ? 'text-yellow-600' : 'text-gray-900') }}">{{ $stok->jumlah_stok }}</span></td>
                            <td class="px-5 py-4 text-center text-gray-500">{{ $stok->stok_minimum }}</td>
                            <td class="px-5 py-4 text-center text-gray-600">{{ $stok->satuan }}</td>
                            <td class="px-5 py-4 text-center">
                                @php $ss = $stok->status_stok; @endphp
                                <span class="badge {{ match($ss) { 'tersedia' => 'badge-success', 'menipis' => 'badge-warning', 'habis' => 'badge-danger', default => 'badge-secondary' } }}">{{ ucfirst($ss) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-center">
                                    <button onclick="openModal('modal-edit-sbb-{{ $stok->id }}')" class="btn btn-outline btn-icon btn-sm"><i class="ri-edit-box-line text-blue-500"></i></button>
                                </div>
                            </td>
                        </tr>
                        <div id="modal-edit-sbb-{{ $stok->id }}" class="modal-overlay">
                            <div class="modal-box max-w-md mx-4">
                                <div class="flex items-center justify-between p-5 border-b"><h3 class="font-bold">Update Stok — {{ $stok->bahanBaku?->nama_bahan }}</h3><button onclick="closeModal('modal-edit-sbb-{{ $stok->id }}')" class="text-gray-400"><i class="ri-close-line text-xl"></i></button></div>
                                <form method="POST" action="{{ route('stok-bahan-baku.update', $stok) }}">
                                    @csrf @method('PUT')
                                    <div class="p-5 space-y-4">
                                        <div><label class="form-label">Jumlah Stok *</label><input type="number" name="jumlah_stok" value="{{ $stok->jumlah_stok }}" class="form-input" min="0" required></div>
                                        <div><label class="form-label">Stok Minimum *</label><input type="number" name="stok_minimum" value="{{ $stok->stok_minimum }}" class="form-input" min="0" required></div>
                                        <div><label class="form-label">Satuan *</label><input type="text" name="satuan" value="{{ $stok->satuan }}" class="form-input" required></div>
                                    </div>
                                    <div class="flex justify-end gap-3 px-5 py-4 border-t"><button type="submit" class="btn btn-primary btn-sm">Simpan</button><button type="button" onclick="closeModal('modal-edit-sbb-{{ $stok->id }}')" class="btn btn-outline btn-sm">Batal</button></div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t">{{ $stokBahanBaku->links() }}</div>
    @else
        <div class="empty-state"><i class="ri-stack-line"></i><h3>Belum Ada Data Stok Bahan Baku</h3></div>
    @endif
</div>
@endsection
