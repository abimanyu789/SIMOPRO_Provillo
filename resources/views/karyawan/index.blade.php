@extends('layouts.app')
@section('title', 'Data Master Karyawan')
@section('page-title', 'Data Master Karyawan')
@section('breadcrumb', 'Kelola data karyawan Provillo')

@section('header-actions')
    <div class="relative" id="exportKry">
        <button onclick="toggleDropdown('exportKry')" class="btn btn-outline btn-sm"><i class="ri-download-2-line"></i> Export <i class="ri-arrow-down-s-line"></i></button>
        <div id="exportKry-menu" class="hidden absolute right-0 top-full mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-10">
            <a href="{{ route('karyawan.export.excel') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50"><i class="ri-file-excel-2-line text-green-600"></i> Excel</a>
            <a href="{{ route('karyawan.export.pdf') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50"><i class="ri-file-pdf-line text-red-500"></i> PDF</a>
        </div>
    </div>
    <button onclick="openModal('modal-tambah-kry')" class="btn btn-primary btn-sm"><i class="ri-add-line"></i> Tambah Karyawan</button>
@endsection

@section('content')
<div class="card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48"><label class="form-label">Cari</label><div class="relative"><i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i><input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau kode karyawan..." class="form-input pl-9"></div></div>
        <div class="min-w-36"><label class="form-label">Posisi</label><select name="posisi" class="form-input"><option value="">Semua</option>@foreach($posisiList as $p)<option value="{{ $p }}" {{ request('posisi') == $p ? 'selected' : '' }}>{{ $p }}</option>@endforeach</select></div>
        <div class="min-w-36"><label class="form-label">Status</label><select name="status" class="form-input"><option value="">Semua</option><option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option><option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option></select></div>
        <div class="flex gap-2"><button type="submit" class="btn btn-primary btn-sm">Cari</button>@if(request()->hasAny(['search','posisi','status']))<a href="{{ route('karyawan.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif</div>
    </form>
</div>

<div class="card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b"><h3 class="font-semibold text-gray-900">Daftar Karyawan <span class="text-sm font-normal text-gray-500">({{ $karyawan->total() }} data)</span></h3></div>
    @if($karyawan->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">No</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Karyawan</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Posisi</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">No HP</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Bergabung</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Status</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($karyawan as $idx => $k)
                        <tr class="table-row">
                            <td class="px-5 py-4 text-gray-500">{{ $karyawan->firstItem() + $idx }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($k->foto)
                                        <img src="{{ $k->foto_url }}" class="w-9 h-9 rounded-full object-cover" alt="">
                                    @else
                                        <div class="w-9 h-9 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-sm">{{ strtoupper(substr($k->nama, 0, 1)) }}</div>
                                    @endif
                                    <div><p class="font-semibold text-gray-900">{{ $k->nama }}</p><code class="text-xs text-gray-400">{{ $k->kode_karyawan }}</code></div>
                                </div>
                            </td>
                            <td class="px-5 py-4"><span class="badge badge-primary">{{ $k->posisi }}</span>@if($k->divisi)<p class="text-xs text-gray-400 mt-0.5">{{ $k->divisi }}</p>@endif</td>
                            <td class="px-5 py-4 text-gray-600">{{ $k->no_hp ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $k->tanggal_bergabung?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-4"><span class="badge {{ $k->status === 'aktif' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($k->status) }}</span></td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button onclick="openModal('modal-detail-kry-{{ $k->id }}')" class="btn btn-outline btn-icon btn-sm"><i class="ri-eye-line text-blue-500"></i></button>
                                    <button onclick="openModal('modal-edit-kry-{{ $k->id }}')" class="btn btn-outline btn-icon btn-sm"><i class="ri-pencil-line text-amber-500"></i></button>
                                    <button onclick="openModal('modal-hapus-kry-{{ $k->id }}')" class="btn btn-outline btn-icon btn-sm"><i class="ri-delete-bin-line text-red-500"></i></button>
                                </div>
                            </td>
                        </tr>

                        {{-- Modal Detail --}}
                        <div id="modal-detail-kry-{{ $k->id }}" class="modal-overlay">
                            <div class="modal-box max-w-lg mx-4">
                                <div class="flex items-center justify-between p-5 border-b"><h3 class="font-bold">Detail Karyawan</h3><button onclick="closeModal('modal-detail-kry-{{ $k->id }}')" class="text-gray-400"><i class="ri-close-line text-xl"></i></button></div>
                                <div class="p-5">
                                    <div class="flex items-center gap-4 mb-5">
                                        @if($k->foto)<img src="{{ $k->foto_url }}" class="w-20 h-20 rounded-2xl object-cover" alt="">@else<div class="w-20 h-20 bg-gradient-to-br from-blue-400 to-purple-500 rounded-2xl flex items-center justify-center text-white font-bold text-2xl">{{ strtoupper(substr($k->nama, 0, 1)) }}</div>@endif
                                        <div><p class="text-xl font-bold text-gray-900">{{ $k->nama }}</p><span class="badge badge-primary">{{ $k->posisi }}</span></div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div><p class="text-xs text-gray-400">Kode</p><p class="font-semibold">{{ $k->kode_karyawan }}</p></div>
                                        <div><p class="text-xs text-gray-400">Divisi</p><p class="font-semibold">{{ $k->divisi ?? '-' }}</p></div>
                                        <div><p class="text-xs text-gray-400">Tgl Lahir</p><p class="font-semibold">{{ $k->tanggal_lahir?->format('d M Y') ?? '-' }}</p></div>
                                        <div><p class="text-xs text-gray-400">No HP</p><p class="font-semibold">{{ $k->no_hp ?? '-' }}</p></div>
                                        <div><p class="text-xs text-gray-400">Email</p><p class="font-semibold">{{ $k->email ?? '-' }}</p></div>
                                        <div><p class="text-xs text-gray-400">No Rekening</p><p class="font-semibold">{{ $k->no_rekening ?? '-' }}</p></div>
                                        <div><p class="text-xs text-gray-400">Bergabung</p><p class="font-semibold">{{ $k->tanggal_bergabung?->format('d M Y') ?? '-' }}</p></div>
                                        <div><p class="text-xs text-gray-400">Status</p><span class="badge {{ $k->status === 'aktif' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($k->status) }}</span></div>
                                        @if($k->alamat)<div class="col-span-2"><p class="text-xs text-gray-400">Alamat</p><p class="font-semibold">{{ $k->alamat }}</p></div>@endif
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 px-5 py-4 border-t">
                                    <button onclick="closeModal('modal-detail-kry-{{ $k->id }}'); openModal('modal-edit-kry-{{ $k->id }}')" class="btn btn-primary btn-sm">Edit</button>
                                    <button onclick="closeModal('modal-detail-kry-{{ $k->id }}')" class="btn btn-outline btn-sm">Tutup</button>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Edit --}}
                        <div id="modal-edit-kry-{{ $k->id }}" class="modal-overlay">
                            <div class="modal-box max-w-2xl mx-4">
                                <div class="flex items-center justify-between p-5 border-b"><h3 class="font-bold">Edit Karyawan</h3><button onclick="closeModal('modal-edit-kry-{{ $k->id }}')" class="text-gray-400"><i class="ri-close-line text-xl"></i></button></div>
                                <form method="POST" action="{{ route('karyawan.update', $k) }}" enctype="multipart/form-data">
                                    @csrf @method('PUT')
                                    <div class="p-5 grid grid-cols-2 gap-4">
                                        <div><label class="form-label">Nama *</label><input type="text" name="nama" value="{{ $k->nama }}" class="form-input" required></div>
                                        <div><label class="form-label">Posisi *</label><input type="text" name="posisi" value="{{ $k->posisi }}" class="form-input" required></div>
                                        <div><label class="form-label">Divisi</label><input type="text" name="divisi" value="{{ $k->divisi }}" class="form-input"></div>
                                        <div><label class="form-label">No HP</label><input type="text" name="no_hp" value="{{ $k->no_hp }}" class="form-input"></div>
                                        <div><label class="form-label">Email</label><input type="email" name="email" value="{{ $k->email }}" class="form-input"></div>
                                        <div><label class="form-label">Status *</label><select name="status" class="form-input"><option value="aktif" {{ $k->status === 'aktif' ? 'selected' : '' }}>Aktif</option><option value="nonaktif" {{ $k->status === 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option></select></div>
                                        <div><label class="form-label">Tgl Lahir</label><input type="date" name="tanggal_lahir" value="{{ $k->tanggal_lahir?->format('Y-m-d') }}" class="form-input"></div>
                                        <div><label class="form-label">Tgl Bergabung</label><input type="date" name="tanggal_bergabung" value="{{ $k->tanggal_bergabung?->format('Y-m-d') }}" class="form-input"></div>
                                        <div><label class="form-label">No Rekening</label><input type="text" name="no_rekening" value="{{ $k->no_rekening }}" class="form-input"></div>
                                        <div><label class="form-label">Foto</label><input type="file" name="foto" class="form-input" accept="image/*"></div>
                                        <div class="col-span-2"><label class="form-label">Alamat</label><textarea name="alamat" rows="2" class="form-input">{{ $k->alamat }}</textarea></div>
                                    </div>
                                    <div class="flex justify-end gap-3 px-5 py-4 border-t"><button type="submit" class="btn btn-primary btn-sm">Simpan</button><button type="button" onclick="closeModal('modal-edit-kry-{{ $k->id }}')" class="btn btn-outline btn-sm">Batal</button></div>
                                </form>
                            </div>
                        </div>

                        {{-- Modal Hapus --}}
                        <div id="modal-hapus-kry-{{ $k->id }}" class="modal-overlay">
                            <div class="modal-box max-w-md mx-4">
                                <div class="p-6 text-center"><div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="ri-delete-bin-line text-3xl text-red-500"></i></div><h3 class="text-lg font-bold mb-2">Hapus Karyawan?</h3><p class="text-sm text-gray-500">{{ $k->nama }}</p></div>
                                <div class="flex gap-3 px-6 py-4 border-t"><form method="POST" action="{{ route('karyawan.destroy', $k) }}" class="flex-1">@csrf @method('DELETE')<button type="submit" class="btn btn-danger w-full">Hapus</button></form><button onclick="closeModal('modal-hapus-kry-{{ $k->id }}')" class="btn btn-outline flex-1">Batal</button></div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t">{{ $karyawan->links() }}</div>
    @else
        <div class="empty-state"><i class="ri-team-line"></i><h3>Belum Ada Karyawan</h3><p>Tambahkan data karyawan untuk memulai.</p></div>
    @endif
</div>

{{-- Modal Tambah --}}
<div id="modal-tambah-kry" class="modal-overlay">
    <div class="modal-box max-w-2xl mx-4">
        <div class="flex items-center justify-between p-5 border-b"><h3 class="font-bold">Tambah Karyawan Baru</h3><button onclick="closeModal('modal-tambah-kry')" class="text-gray-400"><i class="ri-close-line text-xl"></i></button></div>
        <form method="POST" action="{{ route('karyawan.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="p-5 grid grid-cols-2 gap-4">
                <div><label class="form-label">Nama *</label><input type="text" name="nama" class="form-input" placeholder="Nama lengkap" required></div>
                <div><label class="form-label">Posisi *</label><input type="text" name="posisi" class="form-input" placeholder="Penjahit / Finishing..." required list="posisi-list"><datalist id="posisi-list"><option value="Penjahit"><option value="Finishing"><option value="Cutting"><option value="Quality Control"><option value="Gudang"></datalist></div>
                <div><label class="form-label">Divisi</label><input type="text" name="divisi" class="form-input" placeholder="Produksi / QC..."></div>
                <div><label class="form-label">Status *</label><select name="status" class="form-input" required><option value="aktif">Aktif</option><option value="nonaktif">Non-Aktif</option></select></div>
                <div><label class="form-label">No HP</label><input type="text" name="no_hp" class="form-input" placeholder="08xxx"></div>
                <div><label class="form-label">Email</label><input type="email" name="email" class="form-input" placeholder="email@..."></div>
                <div><label class="form-label">Tgl Lahir</label><input type="date" name="tanggal_lahir" class="form-input"></div>
                <div><label class="form-label">Tgl Bergabung</label><input type="date" name="tanggal_bergabung" class="form-input" value="{{ date('Y-m-d') }}"></div>
                <div><label class="form-label">No Rekening</label><input type="text" name="no_rekening" class="form-input" placeholder="No rekening bank"></div>
                <div><label class="form-label">Foto</label><input type="file" name="foto" class="form-input" accept="image/*"></div>
                <div class="col-span-2"><label class="form-label">Alamat</label><textarea name="alamat" rows="2" class="form-input" placeholder="Alamat lengkap..."></textarea></div>
            </div>
            <div class="flex justify-end gap-3 px-5 py-4 border-t"><button type="submit" class="btn btn-primary btn-sm">Simpan</button><button type="button" onclick="closeModal('modal-tambah-kry')" class="btn btn-outline btn-sm">Batal</button></div>
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
