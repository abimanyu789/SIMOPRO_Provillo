@extends('layouts.app')
@section('title', 'Stok Produk')
@section('page-title', 'Manajemen Stok Produk')
@section('breadcrumb', 'Kelola stok produk jadi Provillo')

@section('header-actions')
    <a href="{{ route('stok-produk.export.excel') }}" class="btn btn-outline btn-sm"><i class="ri-file-excel-2-line"></i> Export</a>
@endsection

@section('content')
<div class="card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48"><label class="form-label">Cari</label><div class="relative"><i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i><input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau kode produk..." class="form-input pl-9"></div></div>
        <div class="min-w-36"><label class="form-label">Status Stok</label><select name="status" class="form-input"><option value="">Semua</option><option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option><option value="menipis" {{ request('status') == 'menipis' ? 'selected' : '' }}>Menipis</option><option value="habis" {{ request('status') == 'habis' ? 'selected' : '' }}>Habis</option></select></div>
        <div class="min-w-40"><label class="form-label">Kategori</label><select name="kategori" class="form-input"><option value="">Semua</option>@foreach($kategoriList as $kat)<option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>@endforeach</select></div>
        <div class="flex gap-2"><button type="submit" class="btn btn-primary btn-sm">Filter</button>@if(request()->hasAny(['search','status','kategori']))<a href="{{ route('stok-produk.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif</div>
    </form>
</div>

<div class="card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b">
        <h3 class="font-semibold text-gray-900">Daftar Stok Produk <span class="text-sm font-normal text-gray-500">({{ $stokProduk->total() }} data)</span></h3>
    </div>
    @if($stokProduk->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Produk</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Kategori</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Stok Saat Ini</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Stok Minimum</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Status</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Progress</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($stokProduk as $stok)
                        <tr class="table-row">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($stok->produk?->foto)
                                        <img src="{{ $stok->produk->foto_url }}" class="w-9 h-9 rounded-lg object-cover" alt="">
                                    @else
                                        <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center"><i class="ri-shoe-line text-blue-500"></i></div>
                                    @endif
                                    <div><p class="font-semibold text-gray-900">{{ $stok->produk?->nama_produk }}</p><code class="text-xs text-gray-400">{{ $stok->produk?->kode_produk }}</code></div>
                                </div>
                            </td>
                            <td class="px-5 py-4"><span class="badge badge-primary">{{ $stok->produk?->kategori }}</span></td>
                            <td class="px-5 py-4 text-center"><span class="text-2xl font-bold {{ $stok->jumlah_stok <= 0 ? 'text-red-600' : ($stok->jumlah_stok < $stok->stok_minimum ? 'text-yellow-600' : 'text-gray-900') }}">{{ $stok->jumlah_stok }}</span></td>
                            <td class="px-5 py-4 text-center text-gray-500">{{ $stok->stok_minimum }}</td>
                            <td class="px-5 py-4 text-center">
                                @php $ss = $stok->status_stok; @endphp
                                <span class="badge {{ match($ss) { 'tersedia' => 'badge-success', 'menipis' => 'badge-warning', 'habis' => 'badge-danger', default => 'badge-secondary' } }}">{{ ucfirst($ss) }}</span>
                            </td>
                            <td class="px-5 py-4 min-w-32">
                                @php $pct = $stok->stok_minimum > 0 ? min(100, round($stok->jumlah_stok / $stok->stok_minimum * 100)) : 100; @endphp
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $pct <= 0 ? 'bg-red-500' : ($pct < 100 ? 'bg-yellow-400' : 'bg-green-500') }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">{{ $pct }}% dari minimum</p>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button onclick="openModal('modal-edit-stok-{{ $stok->id }}')" class="btn btn-outline btn-icon btn-sm" title="Update Stok"><i class="ri-edit-box-line text-blue-500"></i></button>
                                </div>
                            </td>
                        </tr>
                        <div id="modal-edit-stok-{{ $stok->id }}" class="modal-overlay">
                            <div class="modal-box max-w-md mx-4">
                                <div class="flex items-center justify-between p-5 border-b"><h3 class="font-bold">Update Stok — {{ $stok->produk?->nama_produk }}</h3><button onclick="closeModal('modal-edit-stok-{{ $stok->id }}')" class="text-gray-400"><i class="ri-close-line text-xl"></i></button></div>
                                <form method="POST" action="{{ route('stok-produk.update', $stok) }}">
                                    @csrf @method('PUT')
                                    <div class="p-5 space-y-4">
                                        <div><label class="form-label">Jumlah Stok *</label><input type="number" name="jumlah_stok" value="{{ $stok->jumlah_stok }}" class="form-input" min="0" required></div>
                                        <div><label class="form-label">Stok Minimum *</label><input type="number" name="stok_minimum" value="{{ $stok->stok_minimum }}" class="form-input" min="0" required></div>
                                    </div>
                                    <div class="flex justify-end gap-3 px-5 py-4 border-t"><button type="submit" class="btn btn-primary btn-sm">Simpan</button><button type="button" onclick="closeModal('modal-edit-stok-{{ $stok->id }}')" class="btn btn-outline btn-sm">Batal</button></div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t">{{ $stokProduk->links() }}</div>
    @else
        <div class="empty-state"><i class="ri-archive-drawer-line"></i><h3>Belum Ada Data Stok</h3><p>Data stok akan muncul setelah produk ditambahkan.</p></div>
    @endif
</div>
@endsection
