@extends('layouts.app')
@section('title', 'Log Produksi')
@section('page-title', 'Log Produksi Harian')
@section('breadcrumb', 'Kelola log produksi dan upah karyawan')

@section('header-actions')
    <a href="{{ route('produksi.export.excel') }}" class="btn btn-outline btn-sm"><i class="ri-file-excel-2-line"></i> Export</a>
    <button onclick="openModal('modal-tambah-prod')" class="btn btn-primary btn-sm"><i class="ri-add-line"></i> Tambah Log Produksi</button>
@endsection

@section('content')

{{-- Statistik --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
    <div class="stat-card border-l-4 border-l-purple-500">
        <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center mb-3"><i class="ri-tools-line text-xl text-purple-600"></i></div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($produksi->total()) }}</p>
        <p class="text-sm text-gray-500 mt-1">Total Log Produksi</p>
    </div>
    <div class="stat-card border-l-4 border-l-orange-500">
        <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center mb-3"><i class="ri-money-dollar-circle-line text-xl text-orange-600"></i></div>
        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalUpahBulanIni, 0, ',', '.') }}</p>
        <p class="text-sm text-gray-500 mt-1">Total Upah Bulan Ini</p>
    </div>
    <div class="stat-card border-l-4 border-l-blue-500">
        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center mb-3"><i class="ri-team-line text-xl text-blue-600"></i></div>
        <p class="text-2xl font-bold text-gray-900">{{ $karyawanList->count() }}</p>
        <p class="text-sm text-gray-500 mt-1">Karyawan Aktif</p>
    </div>
</div>

{{-- Filter --}}
<div class="card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48"><label class="form-label">Cari</label><div class="relative"><i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i><input type="text" name="search" value="{{ request('search') }}" placeholder="Kode atau nama karyawan..." class="form-input pl-9"></div></div>
        <div class="min-w-44"><label class="form-label">Karyawan</label><select name="karyawan_id" class="form-input"><option value="">Semua</option>@foreach($karyawanList as $k)<option value="{{ $k->id }}" {{ request('karyawan_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>@endforeach</select></div>
        <div class="min-w-40"><label class="form-label">Tanggal</label><input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-input"></div>
        <div class="flex gap-2"><button type="submit" class="btn btn-primary btn-sm">Cari</button>@if(request()->hasAny(['search','karyawan_id','tanggal']))<a href="{{ route('produksi.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif</div>
    </form>
</div>

{{-- Tabel --}}
<div class="card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b"><h3 class="font-semibold text-gray-900">Riwayat Produksi <span class="text-sm font-normal text-gray-500">({{ $produksi->total() }} data)</span></h3></div>
    @if($produksi->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Kode</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Karyawan</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Pesanan</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Tanggal</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Jumlah</th>
                        <th class="text-right px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Upah/Item</th>
                        <th class="text-right px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Total Upah</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($produksi as $p)
                        <tr class="table-row">
                            <td class="px-5 py-4"><code class="text-xs bg-purple-50 text-purple-700 px-2 py-1 rounded font-mono">{{ $p->kode_produksi }}</code></td>
                            <td class="px-5 py-4"><p class="font-semibold text-gray-900">{{ $p->karyawan?->nama }}</p><p class="text-xs text-gray-400">{{ $p->karyawan?->posisi }}</p></td>
                            <td class="px-5 py-4"><p class="text-sm font-medium text-blue-600">{{ $p->pesanan?->kode_pesanan }}</p><p class="text-xs text-gray-400">{{ $p->pesanan?->customer?->nama }}</p></td>
                            <td class="px-5 py-4 text-gray-600">{{ $p->tanggal_produksi?->format('d M Y') }}</td>
                            <td class="px-5 py-4 text-center font-semibold text-gray-900">{{ $p->jumlah_produksi }} psg</td>
                            <td class="px-5 py-4 text-right text-gray-600">Rp {{ number_format($p->upah_per_item, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right font-bold text-purple-700">{{ $p->total_upah_format }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button onclick="openModal('modal-hapus-prod-{{ $p->id }}')" class="btn btn-outline btn-icon btn-sm"><i class="ri-delete-bin-line text-red-500"></i></button>
                                </div>
                            </td>
                        </tr>
                        <div id="modal-hapus-prod-{{ $p->id }}" class="modal-overlay">
                            <div class="modal-box max-w-md mx-4">
                                <div class="p-6 text-center"><div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="ri-delete-bin-line text-3xl text-red-500"></i></div><h3 class="text-lg font-bold mb-2">Hapus Log Produksi?</h3><p class="text-sm text-gray-500">{{ $p->kode_produksi }} - {{ $p->karyawan?->nama }}</p><p class="text-xs text-amber-600 bg-amber-50 rounded-lg p-2 mt-3">Catatan upah di arus kas juga akan dihapus.</p></div>
                                <div class="flex gap-3 px-6 py-4 border-t"><form method="POST" action="{{ route('produksi.destroy', $p) }}" class="flex-1">@csrf @method('DELETE')<button type="submit" class="btn btn-danger w-full">Hapus</button></form><button onclick="closeModal('modal-hapus-prod-{{ $p->id }}')" class="btn btn-outline flex-1">Batal</button></div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t">{{ $produksi->links() }}</div>
    @else
        <div class="empty-state"><i class="ri-tools-line"></i><h3>Belum Ada Log Produksi</h3><p>Tambahkan log produksi harian karyawan.</p></div>
    @endif
</div>

{{-- Modal Tambah --}}
<div id="modal-tambah-prod" class="modal-overlay">
    <div class="modal-box max-w-lg mx-4">
        <div class="flex items-center justify-between p-5 border-b">
            <h3 class="font-bold">Tambah Log Produksi</h3>
            <button onclick="closeModal('modal-tambah-prod')" class="text-gray-400"><i class="ri-close-line text-xl"></i></button>
        </div>
        <form method="POST" action="{{ route('produksi.store') }}" id="formProduksi">
            @csrf
            <div class="p-5 space-y-4">
                <div>
                    <label class="form-label">Karyawan <span class="text-red-500">*</span></label>
                    <select name="karyawan_id" class="form-input" required>
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($karyawanList as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }} ({{ $k->posisi }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Pesanan <span class="text-red-500">*</span></label>
                    <select name="pesanan_id" class="form-input" required>
                        <option value="">-- Pilih Pesanan --</option>
                        @foreach($pesananList as $order)
                            <option value="{{ $order->id }}">{{ $order->kode_pesanan }} - {{ $order->customer?->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Tanggal Produksi <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_produksi" class="form-input" required value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Jumlah Produksi <span class="text-red-500">*</span></label>
                        <input type="number" name="jumlah_produksi" class="form-input" min="1" required placeholder="0" id="jumlahProd" oninput="calcUpah()">
                    </div>
                    <div>
                        <label class="form-label">Upah per Item (Rp) <span class="text-red-500">*</span></label>
                        <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span><input type="number" name="upah_per_item" class="form-input pl-10" min="0" required placeholder="0" id="upahItem" oninput="calcUpah()"></div>
                    </div>
                </div>
                <div class="p-3 bg-purple-50 rounded-xl flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">Total Upah:</span>
                    <span id="totalUpahPreview" class="text-lg font-bold text-purple-700">Rp 0</span>
                </div>
                <div>
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" rows="2" class="form-input" placeholder="Keterangan tambahan..."></textarea>
                </div>
                <div class="p-3 bg-blue-50 rounded-xl text-xs text-blue-600 flex items-start gap-2">
                    <i class="ri-information-line mt-0.5"></i>
                    <span>Total upah akan otomatis dicatat sebagai pengeluaran di Arus Kas.</span>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-5 py-4 border-t">
                <button type="submit" class="btn btn-primary btn-sm"><i class="ri-save-line"></i> Simpan Log</button>
                <button type="button" onclick="closeModal('modal-tambah-prod')" class="btn btn-outline btn-sm">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function calcUpah() {
    const j = parseFloat(document.getElementById('jumlahProd').value || 0);
    const u = parseFloat(document.getElementById('upahItem').value || 0);
    const total = j * u;
    document.getElementById('totalUpahPreview').textContent = 'Rp ' + total.toLocaleString('id-ID');
}
</script>
@endpush
