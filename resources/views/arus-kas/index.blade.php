@extends('layouts.app')

@section('title', 'Laporan Arus Kas')
@section('page-title', 'Laporan Arus Kas')
@section('breadcrumb', 'Pencatatan keuangan masuk dan keluar Provillo')

@section('header-actions')
    <div class="relative" id="exportAk">
        <button onclick="toggleDropdown('exportAk')" class="btn btn-outline btn-sm">
            <i class="ri-download-2-line"></i> Export <i class="ri-arrow-down-s-line"></i>
        </button>
        <div id="exportAk-menu" class="hidden absolute right-0 top-full mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-10">
            <a href="{{ route('arus-kas.export.excel') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50"><i class="ri-file-excel-2-line text-green-600"></i> Export Excel</a>
            <a href="{{ route('arus-kas.export.pdf') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50"><i class="ri-file-pdf-line text-red-500"></i> Export PDF</a>
        </div>
    </div>
    <button onclick="openModal('modal-tambah-ak')" class="btn btn-primary btn-sm">
        <i class="ri-add-line"></i> Tambah Transaksi
    </button>
@endsection

@section('content')

{{-- Ringkasan Saldo --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
    <div class="stat-card border-l-4 border-l-green-500">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                <i class="ri-arrow-up-circle-line text-xl text-green-600"></i>
            </div>
            <span class="text-sm font-semibold text-gray-600">Total Pemasukan</span>
        </div>
        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-1">Bulan ini: Rp {{ number_format($pemasukanBulan, 0, ',', '.') }}</p>
    </div>
    <div class="stat-card border-l-4 border-l-red-500">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center">
                <i class="ri-arrow-down-circle-line text-xl text-red-500"></i>
            </div>
            <span class="text-sm font-semibold text-gray-600">Total Pengeluaran</span>
        </div>
        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-1">Bulan ini: Rp {{ number_format($pengeluaranBulan, 0, ',', '.') }}</p>
    </div>
    <div class="stat-card border-l-4 {{ $saldo >= 0 ? 'border-l-blue-500' : 'border-l-red-600' }}">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 {{ $saldo >= 0 ? 'bg-blue-50' : 'bg-red-50' }} rounded-xl flex items-center justify-center">
                <i class="ri-wallet-3-line text-xl {{ $saldo >= 0 ? 'text-blue-600' : 'text-red-600' }}"></i>
            </div>
            <span class="text-sm font-semibold text-gray-600">Saldo Bersih</span>
        </div>
        <p class="text-2xl font-bold {{ $saldo >= 0 ? 'text-blue-700' : 'text-red-600' }}">
            {{ $saldo >= 0 ? '' : '-' }}Rp {{ number_format(abs($saldo), 0, ',', '.') }}
        </p>
        <p class="text-xs {{ $saldo >= 0 ? 'text-blue-400' : 'text-red-400' }} mt-1">{{ $saldo >= 0 ? 'Surplus' : 'Defisit' }}</p>
    </div>
</div>

{{-- Filter --}}
<div class="card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="form-label">Cari Transaksi</label>
            <div class="relative">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode, deskripsi, atau kategori..." class="form-input pl-9">
            </div>
        </div>
        <div class="min-w-36">
            <label class="form-label">Jenis</label>
            <select name="jenis" class="form-input">
                <option value="">Semua</option>
                <option value="pemasukan" {{ request('jenis') === 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                <option value="pengeluaran" {{ request('jenis') === 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
            </select>
        </div>
        <div class="min-w-36">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-input">
        </div>
        <div class="min-w-36">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-input">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line"></i> Filter</button>
            @if(request()->hasAny(['search', 'jenis', 'tanggal_dari', 'tanggal_sampai']))
                <a href="{{ route('arus-kas.index') }}" class="btn btn-outline btn-sm"><i class="ri-refresh-line"></i> Reset</a>
            @endif
        </div>
    </form>
</div>

{{-- Tabel Arus Kas --}}
<div class="card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-900">Riwayat Transaksi <span class="text-sm font-normal text-gray-500">({{ $arusKas->total() }} data)</span></h3>
    </div>

    @if($arusKas->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Kode</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Jenis</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Kategori</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Deskripsi</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Tanggal</th>
                        <th class="text-right px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Jumlah</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($arusKas as $ak)
                        <tr class="table-row">
                            <td class="px-5 py-4">
                                <code class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded font-mono">{{ $ak->kode_transaksi }}</code>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1.5">
                                    <i class="{{ $ak->jenis === 'pemasukan' ? 'ri-arrow-up-circle-fill text-green-500' : 'ri-arrow-down-circle-fill text-red-500' }}"></i>
                                    <span class="{{ $ak->jenis === 'pemasukan' ? 'text-green-700' : 'text-red-700' }} font-semibold capitalize">{{ $ak->jenis }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="badge badge-secondary">{{ $ak->kategori }}</span>
                            </td>
                            <td class="px-5 py-4 text-gray-600 max-w-64">
                                <p class="truncate">{{ $ak->deskripsi }}</p>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $ak->tanggal?->format('d M Y') }}</td>
                            <td class="px-5 py-4 text-right">
                                <span class="font-bold {{ $ak->jenis === 'pemasukan' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $ak->jenis === 'pemasukan' ? '+' : '-' }} Rp {{ number_format($ak->jumlah, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button onclick="openModal('modal-edit-ak-{{ $ak->id }}')" class="btn btn-outline btn-icon btn-sm" title="Edit">
                                        <i class="ri-pencil-line text-amber-500"></i>
                                    </button>
                                    <button onclick="openModal('modal-hapus-ak-{{ $ak->id }}')" class="btn btn-outline btn-icon btn-sm" title="Hapus">
                                        <i class="ri-delete-bin-line text-red-500"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Modal Edit --}}
                        <div id="modal-edit-ak-{{ $ak->id }}" class="modal-overlay">
                            <div class="modal-box max-w-lg mx-4">
                                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                                    <h3 class="font-bold text-gray-900">Edit Transaksi</h3>
                                    <button onclick="closeModal('modal-edit-ak-{{ $ak->id }}')" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-xl"></i></button>
                                </div>
                                <form method="POST" action="{{ route('arus-kas.update', $ak) }}">
                                    @csrf @method('PUT')
                                    <div class="p-5 space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="form-label">Jenis *</label>
                                                <select name="jenis" class="form-input" required>
                                                    <option value="pemasukan" {{ $ak->jenis === 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                                                    <option value="pengeluaran" {{ $ak->jenis === 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label">Kategori *</label>
                                                <input type="text" name="kategori" value="{{ $ak->kategori }}" class="form-input" required list="kategori-ak">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="form-label">Deskripsi *</label>
                                            <input type="text" name="deskripsi" value="{{ $ak->deskripsi }}" class="form-input" required>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="form-label">Jumlah (Rp) *</label>
                                                <input type="number" name="jumlah" value="{{ $ak->jumlah }}" class="form-input" min="0" required>
                                            </div>
                                            <div>
                                                <label class="form-label">Tanggal *</label>
                                                <input type="date" name="tanggal" value="{{ $ak->tanggal?->format('Y-m-d') }}" class="form-input" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-3 px-5 py-4 border-t">
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="ri-save-line"></i> Simpan</button>
                                        <button type="button" onclick="closeModal('modal-edit-ak-{{ $ak->id }}')" class="btn btn-outline btn-sm">Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Modal Hapus --}}
                        <div id="modal-hapus-ak-{{ $ak->id }}" class="modal-overlay">
                            <div class="modal-box max-w-md mx-4">
                                <div class="p-6 text-center">
                                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="ri-delete-bin-line text-3xl text-red-500"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Transaksi?</h3>
                                    <p class="text-sm text-gray-500">{{ $ak->kode_transaksi }} - {{ $ak->deskripsi }}</p>
                                </div>
                                <div class="flex gap-3 px-6 py-4 border-t">
                                    <form method="POST" action="{{ route('arus-kas.destroy', $ak) }}" class="flex-1">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger w-full"><i class="ri-delete-bin-line"></i> Hapus</button>
                                    </form>
                                    <button onclick="closeModal('modal-hapus-ak-{{ $ak->id }}')" class="btn btn-outline flex-1">Batal</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">{{ $arusKas->links() }}</div>
    @else
        <div class="empty-state">
            <i class="ri-money-dollar-circle-line"></i>
            <h3>Belum Ada Transaksi</h3>
            <p>Tambahkan transaksi pertama untuk mulai mencatat arus kas.</p>
        </div>
    @endif
</div>

{{-- Modal Tambah Transaksi --}}
<div id="modal-tambah-ak" class="modal-overlay">
    <div class="modal-box max-w-lg mx-4">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">Tambah Transaksi</h3>
            <button onclick="closeModal('modal-tambah-ak')" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-xl"></i></button>
        </div>
        <form method="POST" action="{{ route('arus-kas.store') }}">
            @csrf
            <div class="p-5 space-y-4">
                {{-- Jenis Toggle --}}
                <div>
                    <label class="form-label">Jenis Transaksi <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition-all has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                            <input type="radio" name="jenis" value="pemasukan" class="hidden" required>
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="ri-arrow-up-circle-line text-green-600"></i>
                            </div>
                            <span class="font-semibold text-gray-800">Pemasukan</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition-all has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                            <input type="radio" name="jenis" value="pengeluaran" class="hidden">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="ri-arrow-down-circle-line text-red-500"></i>
                            </div>
                            <span class="font-semibold text-gray-800">Pengeluaran</span>
                        </label>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Kategori <span class="text-red-500">*</span></label>
                        <input type="text" name="kategori" class="form-input" placeholder="Penjualan / Upah / dll" required list="kategori-ak">
                        <datalist id="kategori-ak">
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat }}">
                            @endforeach
                            <option value="Penjualan">
                            <option value="Upah Produksi">
                            <option value="Pembelian Bahan Baku">
                            <option value="Biaya Operasional">
                            <option value="Lain-lain">
                        </datalist>
                    </div>
                    <div>
                        <label class="form-label">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" class="form-input" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div>
                    <label class="form-label">Deskripsi <span class="text-red-500">*</span></label>
                    <input type="text" name="deskripsi" class="form-input" placeholder="Keterangan transaksi..." required>
                </div>
                <div>
                    <label class="form-label">Jumlah (Rp) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
                        <input type="number" name="jumlah" class="form-input pl-10" placeholder="0" min="0" required>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-5 py-4 border-t border-gray-100">
                <button type="submit" class="btn btn-primary btn-sm"><i class="ri-save-line"></i> Simpan Transaksi</button>
                <button type="button" onclick="closeModal('modal-tambah-ak')" class="btn btn-outline btn-sm">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleDropdown(dropdownId) {
    const menu = document.getElementById(dropdownId + '-menu');
    menu.classList.toggle('hidden');
    document.addEventListener('click', function closeDD(e) {
        if (!document.getElementById(dropdownId).contains(e.target)) {
            menu.classList.add('hidden');
            document.removeEventListener('click', closeDD);
        }
    });
}
</script>
@endpush
