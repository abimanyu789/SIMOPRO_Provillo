@extends('layouts.app')

@section('title', 'Data Master Produk')
@section('page-title', 'Data Master Produk')
@section('breadcrumb', 'Kelola data produk sepatu Provillo')

@section('header-actions')
    {{-- Import Excel --}}
    <button onclick="openModal('modal-import')" class="btn btn-outline btn-sm">
        <i class="ri-upload-2-line"></i> Import
    </button>

    {{-- Export --}}
    <div class="relative" id="exportDropdown">
        <button onclick="toggleDropdown('exportDropdown')" class="btn btn-outline btn-sm">
            <i class="ri-download-2-line"></i> Export
            <i class="ri-arrow-down-s-line"></i>
        </button>
        <div id="exportDropdown-menu" class="hidden absolute right-0 top-full mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-10">
            <a href="{{ route('produk.export.excel') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                <i class="ri-file-excel-2-line text-green-600"></i> Export Excel
            </a>
            <a href="{{ route('produk.export.pdf') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                <i class="ri-file-pdf-line text-red-500"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Tambah Produk --}}
    <button onclick="openModal('modal-tambah')" class="btn btn-primary btn-sm">
        <i class="ri-add-line"></i> Tambah Produk
    </button>
@endsection

@section('content')

{{-- ========================
     FILTER & PENCARIAN
     ======================== --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('produk.index') }}" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="form-label">Cari Produk</label>
            <div class="relative">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Kode atau nama produk..."
                       class="form-input pl-9">
            </div>
        </div>
        <div class="min-w-44">
            <label class="form-label">Kategori</label>
            <select name="kategori" class="form-input">
                <option value="">Semua Kategori</option>
                @foreach($kategoriList as $kat)
                    <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="ri-search-line"></i> Cari
            </button>
            @if(request()->hasAny(['search', 'kategori']))
                <a href="{{ route('produk.index') }}" class="btn btn-outline btn-sm">
                    <i class="ri-refresh-line"></i> Reset
                </a>
            @endif
        </div>
    </form>
</div>

{{-- ========================
     TABEL DATA PRODUK
     ======================== --}}
<div class="card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-900">
            Daftar Produk
            <span class="text-sm font-normal text-gray-500 ml-2">({{ $produk->total() }} data)</span>
        </h3>
    </div>

    @if($produk->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">No</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Kode</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Produk</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Kategori</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Harga Jual</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Stok</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Status</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($produk as $idx => $item)
                        <tr class="table-row">
                            <td class="px-5 py-4 text-gray-500">{{ $produk->firstItem() + $idx }}</td>
                            <td class="px-5 py-4">
                                <code class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded font-mono">{{ $item->kode_produk }}</code>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($item->foto)
                                        <img src="{{ $item->foto_url }}" class="w-10 h-10 rounded-lg object-cover border border-gray-100" alt="">
                                    @else
                                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                                            <i class="ri-shoe-line text-blue-500"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $item->nama_produk }}</p>
                                        @if($item->deskripsi)
                                            <p class="text-xs text-gray-400 truncate max-w-48">{{ $item->deskripsi }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="badge badge-primary">{{ $item->kategori }}</span>
                            </td>
                            <td class="px-5 py-4 font-semibold text-gray-900">
                                {{ 'Rp ' . number_format($item->harga_jual, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-semibold text-gray-900">{{ $item->stok?->jumlah_stok ?? 0 }}</span>
                                <span class="text-xs text-gray-400">/ min {{ $item->stok?->stok_minimum ?? 0 }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusStok = $item->stok?->status_stok ?? 'tersedia';
                                    $badgeClass = match($statusStok) {
                                        'tersedia' => 'badge-success',
                                        'menipis'  => 'badge-warning',
                                        'habis'    => 'badge-danger',
                                        default    => 'badge-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($statusStok) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Detail --}}
                                    <button onclick="openModal('modal-detail-{{ $item->id }}')"
                                            class="btn btn-outline btn-icon btn-sm" title="Detail">
                                        <i class="ri-eye-line text-blue-500"></i>
                                    </button>

                                    {{-- Edit --}}
                                    <button onclick="openModal('modal-edit-{{ $item->id }}')"
                                            class="btn btn-outline btn-icon btn-sm" title="Edit">
                                        <i class="ri-pencil-line text-amber-500"></i>
                                    </button>

                                    {{-- Hapus --}}
                                    <button onclick="openModal('modal-hapus-{{ $item->id }}')"
                                            class="btn btn-outline btn-icon btn-sm" title="Hapus">
                                        <i class="ri-delete-bin-line text-red-500"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- MODAL DETAIL --}}
                        <div id="modal-detail-{{ $item->id }}" class="modal-overlay">
                            <div class="modal-box max-w-lg mx-4">
                                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                                    <h3 class="font-bold text-gray-900">Detail Produk</h3>
                                    <button onclick="closeModal('modal-detail-{{ $item->id }}')" class="text-gray-400 hover:text-gray-600">
                                        <i class="ri-close-line text-xl"></i>
                                    </button>
                                </div>
                                <div class="p-5 space-y-4">
                                    @if($item->foto)
                                        <img src="{{ $item->foto_url }}" class="w-full h-48 object-cover rounded-xl" alt="">
                                    @endif
                                    <div class="grid grid-cols-2 gap-4">
                                        <div><p class="text-xs text-gray-500 mb-1">Kode Produk</p><p class="font-semibold text-gray-900">{{ $item->kode_produk }}</p></div>
                                        <div><p class="text-xs text-gray-500 mb-1">Kategori</p><span class="badge badge-primary">{{ $item->kategori }}</span></div>
                                        <div class="col-span-2"><p class="text-xs text-gray-500 mb-1">Nama Produk</p><p class="font-semibold text-gray-900">{{ $item->nama_produk }}</p></div>
                                        <div><p class="text-xs text-gray-500 mb-1">Harga Jual</p><p class="font-bold text-green-600">{{ $item->harga_format }}</p></div>
                                        <div><p class="text-xs text-gray-500 mb-1">Stok Saat Ini</p><p class="font-semibold text-gray-900">{{ $item->stok?->jumlah_stok ?? 0 }}</p></div>
                                        @if($item->deskripsi)
                                            <div class="col-span-2"><p class="text-xs text-gray-500 mb-1">Deskripsi</p><p class="text-gray-700 text-sm">{{ $item->deskripsi }}</p></div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 px-5 py-4 border-t border-gray-100">
                                    <button onclick="closeModal('modal-detail-{{ $item->id }}'); openModal('modal-edit-{{ $item->id }}')" class="btn btn-primary btn-sm">
                                        <i class="ri-pencil-line"></i> Edit Data
                                    </button>
                                    <button onclick="closeModal('modal-detail-{{ $item->id }}')" class="btn btn-outline btn-sm">Tutup</button>
                                </div>
                            </div>
                        </div>

                        {{-- MODAL EDIT --}}
                        <div id="modal-edit-{{ $item->id }}" class="modal-overlay">
                            <div class="modal-box max-w-lg mx-4">
                                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                                    <h3 class="font-bold text-gray-900">Edit Produk</h3>
                                    <button onclick="closeModal('modal-edit-{{ $item->id }}')" class="text-gray-400 hover:text-gray-600">
                                        <i class="ri-close-line text-xl"></i>
                                    </button>
                                </div>
                                <form method="POST" action="{{ route('produk.update', $item) }}" enctype="multipart/form-data">
                                    @csrf @method('PUT')
                                    <div class="p-5 space-y-4">
                                        <div>
                                            <label class="form-label">Nama Produk <span class="text-red-500">*</span></label>
                                            <input type="text" name="nama_produk" value="{{ $item->nama_produk }}" class="form-input" required>
                                        </div>
                                        <div>
                                            <label class="form-label">Kategori <span class="text-red-500">*</span></label>
                                            <input type="text" name="kategori" value="{{ $item->kategori }}" class="form-input" required list="kategori-list">
                                            <datalist id="kategori-list">
                                                @foreach($kategoriList as $kat)
                                                    <option value="{{ $kat }}">
                                                @endforeach
                                            </datalist>
                                        </div>
                                        <div>
                                            <label class="form-label">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                                            <input type="number" name="harga_jual" value="{{ $item->harga_jual }}" class="form-input" min="0" required>
                                            <p class="text-xs text-amber-600 mt-1 flex items-center gap-1">
                                                <i class="ri-information-line"></i>
                                                Perubahan harga tidak mempengaruhi pesanan yang sudah ada.
                                            </p>
                                        </div>
                                        <div>
                                            <label class="form-label">Deskripsi</label>
                                            <textarea name="deskripsi" rows="3" class="form-input">{{ $item->deskripsi }}</textarea>
                                        </div>
                                        <div>
                                            <label class="form-label">Foto Produk</label>
                                            <input type="file" name="foto" class="form-input" accept="image/*">
                                            @if($item->foto)
                                                <p class="text-xs text-gray-400 mt-1">Foto saat ini: Ada. Kosongkan untuk tidak mengubah foto.</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-3 px-5 py-4 border-t border-gray-100">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="ri-save-line"></i> Simpan Perubahan
                                        </button>
                                        <button type="button" onclick="closeModal('modal-edit-{{ $item->id }}')" class="btn btn-outline btn-sm">Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- MODAL HAPUS --}}
                        <div id="modal-hapus-{{ $item->id }}" class="modal-overlay">
                            <div class="modal-box max-w-md mx-4">
                                <div class="p-6 text-center">
                                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="ri-delete-bin-line text-3xl text-red-500"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Produk?</h3>
                                    <p class="text-gray-500 text-sm mb-1">Anda akan menghapus produk:</p>
                                    <p class="font-semibold text-gray-900 mb-4">{{ $item->nama_produk }}</p>
                                    <p class="text-xs text-gray-400">Data yang dihapus dapat dipulihkan oleh admin. Pesanan yang sudah ada tidak akan terpengaruh.</p>
                                </div>
                                <div class="flex gap-3 px-6 py-4 border-t border-gray-100">
                                    <form method="POST" action="{{ route('produk.destroy', $item) }}" class="flex-1">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger w-full">
                                            <i class="ri-delete-bin-line"></i> Ya, Hapus
                                        </button>
                                    </form>
                                    <button onclick="closeModal('modal-hapus-{{ $item->id }}')" class="btn btn-outline flex-1">Batal</button>
                                </div>
                            </div>
                        </div>

                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $produk->links() }}
        </div>

    @else
        {{-- Empty State --}}
        <div class="empty-state">
            <i class="ri-shoe-line"></i>
            <h3>Belum Ada Data Produk</h3>
            <p>Tambahkan produk pertama Anda dengan klik tombol "Tambah Produk".</p>
            <button onclick="openModal('modal-tambah')" class="btn btn-primary mt-4">
                <i class="ri-add-line"></i> Tambah Produk
            </button>
        </div>
    @endif
</div>

{{-- ========================
     MODAL TAMBAH PRODUK
     ======================== --}}
<div id="modal-tambah" class="modal-overlay">
    <div class="modal-box max-w-lg mx-4">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">Tambah Produk Baru</h3>
            <button onclick="closeModal('modal-tambah')" class="text-gray-400 hover:text-gray-600">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('produk.store') }}" enctype="multipart/form-data" id="form-tambah-produk">
            @csrf
            <div class="p-5 space-y-4">
                <div>
                    <label class="form-label" for="nama_produk">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama_produk" name="nama_produk"
                           class="form-input" placeholder="Contoh: Sepatu Formal Kulit Classic"
                           required value="{{ old('nama_produk') }}">
                    @error('nama_produk')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label" for="kategori_new">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="kategori_new" name="kategori"
                           class="form-input" placeholder="Formal / Casual / Sport / Sandal"
                           required list="kategori-list-new" value="{{ old('kategori') }}">
                    <datalist id="kategori-list-new">
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}">
                        @endforeach
                        <option value="Formal">
                        <option value="Casual">
                        <option value="Sport">
                        <option value="Sandal">
                        <option value="Boots">
                    </datalist>
                    @error('kategori')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label" for="harga_jual_new">
                        Harga Jual (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
                        <input type="number" id="harga_jual_new" name="harga_jual"
                               class="form-input pl-10" placeholder="0" min="0" step="1000"
                               required value="{{ old('harga_jual') }}"
                               oninput="updateHargaPreview(this.value)">
                    </div>
                    <p id="harga-preview" class="text-xs text-gray-400 mt-1"></p>
                    @error('harga_jual')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label" for="deskripsi_new">Deskripsi</label>
                    <textarea id="deskripsi_new" name="deskripsi" rows="3"
                              class="form-input" placeholder="Deskripsi singkat produk...">{{ old('deskripsi') }}</textarea>
                </div>

                <div>
                    <label class="form-label">Foto Produk</label>
                    <input type="file" name="foto" class="form-input" accept="image/jpeg,image/png,image/webp"
                           onchange="previewFoto(this, 'foto-preview-new')">
                    <img id="foto-preview-new" src="" alt="" class="hidden mt-2 w-24 h-24 object-cover rounded-lg border">
                    @error('foto')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="flex justify-end gap-3 px-5 py-4 border-t border-gray-100">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="ri-save-line"></i> Simpan Produk
                </button>
                <button type="button" onclick="closeModal('modal-tambah')" class="btn btn-outline btn-sm">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- ========================
     MODAL IMPORT EXCEL
     ======================== --}}
<div id="modal-import" class="modal-overlay">
    <div class="modal-box max-w-md mx-4">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">Import Data Produk</h3>
            <button onclick="closeModal('modal-import')" class="text-gray-400 hover:text-gray-600">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('produk.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="p-5 space-y-4">
                <div class="p-4 bg-blue-50 rounded-xl text-sm text-blue-700">
                    <p class="font-semibold mb-1 flex items-center gap-1">
                        <i class="ri-information-line"></i> Format Excel yang diharapkan:
                    </p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        <li>Kolom: nama_produk, kategori, harga_jual, deskripsi</li>
                        <li>Baris pertama adalah header</li>
                        <li>Format file: .xlsx atau .xls</li>
                    </ul>
                </div>
                <div>
                    <label class="form-label">File Excel <span class="text-red-500">*</span></label>
                    <input type="file" name="file" class="form-input" accept=".xlsx,.xls" required>
                    @error('file')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="flex justify-end gap-3 px-5 py-4 border-t border-gray-100">
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="ri-upload-2-line"></i> Import Data
                </button>
                <button type="button" onclick="closeModal('modal-import')" class="btn btn-outline btn-sm">Batal</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
/**
 * Toggle dropdown export menu.
 * @param {string} dropdownId - ID container dropdown
 */
function toggleDropdown(dropdownId) {
    const menu = document.getElementById(dropdownId + '-menu');
    menu.classList.toggle('hidden');

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function closeDropdown(e) {
        if (!document.getElementById(dropdownId).contains(e.target)) {
            menu.classList.add('hidden');
            document.removeEventListener('click', closeDropdown);
        }
    });
}

/**
 * Preview foto yang dipilih sebelum upload.
 * @param {HTMLInputElement} input - Elemen input file
 * @param {string} previewId - ID elemen img untuk preview
 */
function previewFoto(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Update preview harga dalam format Rupiah.
 * @param {string|number} value - Nilai harga
 */
function updateHargaPreview(value) {
    const preview = document.getElementById('harga-preview');
    if (value && value > 0) {
        preview.textContent = 'Preview: ' + formatRupiah(value);
    } else {
        preview.textContent = '';
    }
}

/**
 * Validasi form tambah produk pada blur event (onBlur).
 */
document.getElementById('form-tambah-produk')?.querySelectorAll('[required]').forEach(input => {
    input.addEventListener('blur', function() {
        if (!this.value.trim()) {
            this.classList.add('error');
        } else {
            this.classList.remove('error');
        }
    });
});

// Buka modal otomatis jika ada error dari server
@if($errors->any() && old('_method') !== 'PUT' && old('_method') !== 'DELETE')
    openModal('modal-tambah');
@endif
</script>
@endpush
