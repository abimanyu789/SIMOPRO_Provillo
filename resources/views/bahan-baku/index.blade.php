@extends('layouts.app')

@section('title', 'Data Master Bahan Baku')
@section('page-title', 'Data Master Bahan Baku')
@section('breadcrumb', 'Kelola data bahan baku produksi Provillo')

@section('header-actions')
    <button onclick="openModal('modal-import-bb')" class="btn btn-outline btn-sm"><i class="ri-upload-2-line"></i> Import</button>
    <div class="relative" id="exportBB">
        <button onclick="toggleDropdown('exportBB')" class="btn btn-outline btn-sm"><i class="ri-download-2-line"></i> Export <i class="ri-arrow-down-s-line"></i></button>
        <div id="exportBB-menu" class="hidden absolute right-0 top-full mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-10">
            <a href="{{ route('bahan-baku.export.excel') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50"><i class="ri-file-excel-2-line text-green-600"></i> Excel</a>
            <a href="{{ route('bahan-baku.export.pdf') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50"><i class="ri-file-pdf-line text-red-500"></i> PDF</a>
        </div>
    </div>
    <button onclick="openModal('modal-tambah-bb')" class="btn btn-primary btn-sm"><i class="ri-add-line"></i> Tambah Bahan Baku</button>
@endsection

@section('content')

{{-- Filter --}}
<div class="card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="form-label">Cari</label>
            <div class="relative"><i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode atau nama bahan..." class="form-input pl-9"></div>
        </div>
        <div class="min-w-40">
            <label class="form-label">Kategori</label>
            <select name="kategori" class="form-input">
                <option value="">Semua</option>
                @foreach($kategoriList as $kat)
                    <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line"></i> Cari</button>
            @if(request()->hasAny(['search', 'kategori']))
                <a href="{{ route('bahan-baku.index') }}" class="btn btn-outline btn-sm"><i class="ri-refresh-line"></i> Reset</a>
            @endif
        </div>
    </form>
</div>

{{-- Tabel --}}
<div class="card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-900">Daftar Bahan Baku <span class="text-sm font-normal text-gray-500">({{ $bahanBaku->total() }} data)</span></h3>
    </div>
    @if($bahanBaku->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">No</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Kode</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Nama Bahan</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Kategori</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Satuan</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Harga Beli</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Stok</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Status</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($bahanBaku as $idx => $item)
                        <tr class="table-row">
                            <td class="px-5 py-4 text-gray-500">{{ $bahanBaku->firstItem() + $idx }}</td>
                            <td class="px-5 py-4"><code class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded font-mono">{{ $item->kode_bahan }}</code></td>
                            <td class="px-5 py-4"><p class="font-semibold text-gray-900">{{ $item->nama_bahan }}</p><p class="text-xs text-gray-400">{{ $item->deskripsi }}</p></td>
                            <td class="px-5 py-4"><span class="badge badge-info">{{ $item->kategori }}</span></td>
                            <td class="px-5 py-4 text-gray-600">{{ $item->satuan }}</td>
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $item->harga_format }}</td>
                            <td class="px-5 py-4">
                                <span class="font-semibold">{{ $item->stok?->jumlah_stok ?? 0 }}</span>
                                <span class="text-xs text-gray-400">/ min {{ $item->stok?->stok_minimum ?? 0 }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @php $statusStok = $item->stok?->status_stok ?? 'tersedia'; @endphp
                                <span class="badge {{ match($statusStok) { 'tersedia' => 'badge-success', 'menipis' => 'badge-warning', 'habis' => 'badge-danger', default => 'badge-secondary' } }}">{{ ucfirst($statusStok) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button onclick="openModal('modal-edit-bb-{{ $item->id }}')" class="btn btn-outline btn-icon btn-sm"><i class="ri-pencil-line text-amber-500"></i></button>
                                    <button onclick="openModal('modal-hapus-bb-{{ $item->id }}')" class="btn btn-outline btn-icon btn-sm"><i class="ri-delete-bin-line text-red-500"></i></button>
                                </div>
                            </td>
                        </tr>

                        {{-- Modal Edit --}}
                        <div id="modal-edit-bb-{{ $item->id }}" class="modal-overlay">
                            <div class="modal-box max-w-lg mx-4">
                                <div class="flex items-center justify-between p-5 border-b"><h3 class="font-bold">Edit Bahan Baku</h3><button onclick="closeModal('modal-edit-bb-{{ $item->id }}')" class="text-gray-400"><i class="ri-close-line text-xl"></i></button></div>
                                <form method="POST" action="{{ route('bahan-baku.update', $item) }}">
                                    @csrf @method('PUT')
                                    <div class="p-5 space-y-4">
                                        <div><label class="form-label">Nama Bahan *</label><input type="text" name="nama_bahan" value="{{ $item->nama_bahan }}" class="form-input" required></div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div><label class="form-label">Kategori *</label><input type="text" name="kategori" value="{{ $item->kategori }}" class="form-input" required></div>
                                            <div><label class="form-label">Satuan *</label><input type="text" name="satuan" value="{{ $item->satuan }}" class="form-input" required></div>
                                        </div>
                                        <div><label class="form-label">Harga Beli (Rp) *</label><input type="number" name="harga_beli" value="{{ $item->harga_beli }}" class="form-input" min="0" required></div>
                                        <div><label class="form-label">Deskripsi</label><textarea name="deskripsi" rows="2" class="form-input">{{ $item->deskripsi }}</textarea></div>
                                    </div>
                                    <div class="flex justify-end gap-3 px-5 py-4 border-t">
                                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                        <button type="button" onclick="closeModal('modal-edit-bb-{{ $item->id }}')" class="btn btn-outline btn-sm">Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Modal Hapus --}}
                        <div id="modal-hapus-bb-{{ $item->id }}" class="modal-overlay">
                            <div class="modal-box max-w-md mx-4">
                                <div class="p-6 text-center">
                                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="ri-delete-bin-line text-3xl text-red-500"></i></div>
                                    <h3 class="text-lg font-bold mb-2">Hapus Bahan Baku?</h3>
                                    <p class="text-sm text-gray-500">{{ $item->nama_bahan }}</p>
                                </div>
                                <div class="flex gap-3 px-6 py-4 border-t">
                                    <form method="POST" action="{{ route('bahan-baku.destroy', $item) }}" class="flex-1">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger w-full">Hapus</button>
                                    </form>
                                    <button onclick="closeModal('modal-hapus-bb-{{ $item->id }}')" class="btn btn-outline flex-1">Batal</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t">{{ $bahanBaku->links() }}</div>
    @else
        <div class="empty-state"><i class="ri-box-3-line"></i><h3>Belum Ada Data</h3><p>Tambahkan bahan baku untuk memulai.</p></div>
    @endif
</div>

{{-- Modal Tambah --}}
<div id="modal-tambah-bb" class="modal-overlay">
    <div class="modal-box max-w-lg mx-4">
        <div class="flex items-center justify-between p-5 border-b"><h3 class="font-bold">Tambah Bahan Baku</h3><button onclick="closeModal('modal-tambah-bb')" class="text-gray-400"><i class="ri-close-line text-xl"></i></button></div>
        <form method="POST" action="{{ route('bahan-baku.store') }}">
            @csrf
            <div class="p-5 space-y-4">
                <div><label class="form-label">Nama Bahan *</label><input type="text" name="nama_bahan" class="form-input" placeholder="Kulit Sapi Grade A" required></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="form-label">Kategori *</label><input type="text" name="kategori" class="form-input" placeholder="Kulit / Karet..." required></div>
                    <div><label class="form-label">Satuan *</label><input type="text" name="satuan" class="form-input" placeholder="meter / pcs / kg" required list="satuan-list"><datalist id="satuan-list"><option value="meter"><option value="pcs"><option value="kg"><option value="gulung"><option value="kaleng"></datalist></div>
                </div>
                <div><label class="form-label">Harga Beli (Rp) *</label><div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span><input type="number" name="harga_beli" class="form-input pl-10" placeholder="0" min="0" required></div></div>
                <div><label class="form-label">Deskripsi</label><textarea name="deskripsi" rows="2" class="form-input" placeholder="Keterangan..."></textarea></div>
            </div>
            <div class="flex justify-end gap-3 px-5 py-4 border-t">
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                <button type="button" onclick="closeModal('modal-tambah-bb')" class="btn btn-outline btn-sm">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Import --}}
<div id="modal-import-bb" class="modal-overlay">
    <div class="modal-box max-w-md mx-4">
        <div class="flex items-center justify-between p-5 border-b"><h3 class="font-bold">Import Bahan Baku</h3><button onclick="closeModal('modal-import-bb')" class="text-gray-400"><i class="ri-close-line text-xl"></i></button></div>
        <form method="POST" action="{{ route('bahan-baku.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="p-5"><div class="p-4 bg-blue-50 rounded-xl text-sm text-blue-700 mb-4"><p class="font-semibold mb-1">Format: nama_bahan, kategori, satuan, harga_beli, deskripsi</p></div><div><label class="form-label">File Excel *</label><input type="file" name="file" class="form-input" accept=".xlsx,.xls" required></div></div>
            <div class="flex justify-end gap-3 px-5 py-4 border-t"><button type="submit" class="btn btn-success btn-sm">Import</button><button type="button" onclick="closeModal('modal-import-bb')" class="btn btn-outline btn-sm">Batal</button></div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleDropdown(id) {
    const menu = document.getElementById(id + '-menu');
    menu.classList.toggle('hidden');
    document.addEventListener('click', function close(e) {
        if (!document.getElementById(id).contains(e.target)) { menu.classList.add('hidden'); document.removeEventListener('click', close); }
    });
}
</script>
@endpush
