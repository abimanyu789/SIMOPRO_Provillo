@extends('layouts.app')
@section('title', 'Data Master Customer')
@section('page-title', 'Data Master Customer')
@section('breadcrumb', 'Kelola data pelanggan Provillo')

@section('header-actions')
    <div class="relative" id="exportCst">
        <button onclick="toggleDropdown('exportCst')" class="btn btn-outline btn-sm"><i class="ri-download-2-line"></i> Export <i class="ri-arrow-down-s-line"></i></button>
        <div id="exportCst-menu" class="hidden absolute right-0 top-full mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-10">
            <a href="{{ route('customer.export.excel') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50"><i class="ri-file-excel-2-line text-green-600"></i> Excel</a>
            <a href="{{ route('customer.export.pdf') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50"><i class="ri-file-pdf-line text-red-500"></i> PDF</a>
        </div>
    </div>
    <button onclick="openModal('modal-tambah-cst')" class="btn btn-primary btn-sm"><i class="ri-add-line"></i> Tambah Customer</button>
@endsection

@section('content')
<div class="card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48"><label class="form-label">Cari</label><div class="relative"><i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i><input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau no HP..." class="form-input pl-9"></div></div>
        <div class="min-w-36"><label class="form-label">Kota</label><select name="kota" class="form-input"><option value="">Semua</option>@foreach($kotaList as $kt)<option value="{{ $kt }}" {{ request('kota') == $kt ? 'selected' : '' }}>{{ $kt }}</option>@endforeach</select></div>
        <div class="flex gap-2"><button type="submit" class="btn btn-primary btn-sm">Cari</button>@if(request()->hasAny(['search','kota']))<a href="{{ route('customer.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif</div>
    </form>
</div>

<div class="card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b"><h3 class="font-semibold text-gray-900">Daftar Customer <span class="text-sm font-normal text-gray-500">({{ $customer->total() }} data)</span></h3></div>
    @if($customer->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">No</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Kode</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Nama Customer</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">No HP</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Kota</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Total Pesanan</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($customer as $idx => $c)
                        <tr class="table-row">
                            <td class="px-5 py-4 text-gray-500">{{ $customer->firstItem() + $idx }}</td>
                            <td class="px-5 py-4"><code class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded font-mono">{{ $c->kode_customer }}</code></td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-teal-100 rounded-full flex items-center justify-center text-teal-700 font-bold text-sm">{{ strtoupper(substr($c->nama, 0, 1)) }}</div>
                                    <div><p class="font-semibold text-gray-900">{{ $c->nama }}</p>@if($c->email)<p class="text-xs text-gray-400">{{ $c->email }}</p>@endif</div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $c->no_hp ?? '-' }}</td>
                            <td class="px-5 py-4">@if($c->kota)<span class="badge badge-secondary">{{ $c->kota }}</span>@else<span class="text-gray-300">-</span>@endif</td>
                            <td class="px-5 py-4"><span class="font-semibold text-gray-900">{{ $c->pesanan_count ?? 0 }}</span> pesanan</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button onclick="openModal('modal-edit-cst-{{ $c->id }}')" class="btn btn-outline btn-icon btn-sm"><i class="ri-pencil-line text-amber-500"></i></button>
                                    <button onclick="openModal('modal-hapus-cst-{{ $c->id }}')" class="btn btn-outline btn-icon btn-sm"><i class="ri-delete-bin-line text-red-500"></i></button>
                                </div>
                            </td>
                        </tr>

                        <div id="modal-edit-cst-{{ $c->id }}" class="modal-overlay">
                            <div class="modal-box max-w-lg mx-4">
                                <div class="flex items-center justify-between p-5 border-b"><h3 class="font-bold">Edit Customer</h3><button onclick="closeModal('modal-edit-cst-{{ $c->id }}')" class="text-gray-400"><i class="ri-close-line text-xl"></i></button></div>
                                <form method="POST" action="{{ route('customer.update', $c) }}">
                                    @csrf @method('PUT')
                                    <div class="p-5 grid grid-cols-2 gap-4">
                                        <div class="col-span-2"><label class="form-label">Nama *</label><input type="text" name="nama" value="{{ $c->nama }}" class="form-input" required></div>
                                        <div><label class="form-label">No HP</label><input type="text" name="no_hp" value="{{ $c->no_hp }}" class="form-input"></div>
                                        <div><label class="form-label">Email</label><input type="email" name="email" value="{{ $c->email }}" class="form-input"></div>
                                        <div class="col-span-2"><label class="form-label">Alamat</label><textarea name="alamat" rows="2" class="form-input">{{ $c->alamat }}</textarea></div>
                                        <div><label class="form-label">Kota</label><input type="text" name="kota" value="{{ $c->kota }}" class="form-input"></div>
                                        <div><label class="form-label">Provinsi</label><input type="text" name="provinsi" value="{{ $c->provinsi }}" class="form-input"></div>
                                        <div><label class="form-label">Kode Pos</label><input type="text" name="kode_pos" value="{{ $c->kode_pos }}" class="form-input"></div>
                                    </div>
                                    <div class="flex justify-end gap-3 px-5 py-4 border-t"><button type="submit" class="btn btn-primary btn-sm">Simpan</button><button type="button" onclick="closeModal('modal-edit-cst-{{ $c->id }}')" class="btn btn-outline btn-sm">Batal</button></div>
                                </form>
                            </div>
                        </div>

                        <div id="modal-hapus-cst-{{ $c->id }}" class="modal-overlay">
                            <div class="modal-box max-w-md mx-4">
                                <div class="p-6 text-center"><div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="ri-delete-bin-line text-3xl text-red-500"></i></div><h3 class="text-lg font-bold mb-2">Hapus Customer?</h3><p class="text-sm text-gray-500">{{ $c->nama }}</p></div>
                                <div class="flex gap-3 px-6 py-4 border-t"><form method="POST" action="{{ route('customer.destroy', $c) }}" class="flex-1">@csrf @method('DELETE')<button type="submit" class="btn btn-danger w-full">Hapus</button></form><button onclick="closeModal('modal-hapus-cst-{{ $c->id }}')" class="btn btn-outline flex-1">Batal</button></div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t">{{ $customer->links() }}</div>
    @else
        <div class="empty-state"><i class="ri-user-star-line"></i><h3>Belum Ada Customer</h3><p>Tambahkan data pelanggan untuk memulai.</p></div>
    @endif
</div>

<div id="modal-tambah-cst" class="modal-overlay">
    <div class="modal-box max-w-lg mx-4">
        <div class="flex items-center justify-between p-5 border-b"><h3 class="font-bold">Tambah Customer Baru</h3><button onclick="closeModal('modal-tambah-cst')" class="text-gray-400"><i class="ri-close-line text-xl"></i></button></div>
        <form method="POST" action="{{ route('customer.store') }}">
            @csrf
            <div class="p-5 grid grid-cols-2 gap-4">
                <div class="col-span-2"><label class="form-label">Nama Customer *</label><input type="text" name="nama" class="form-input" placeholder="Nama toko/individu" required></div>
                <div><label class="form-label">No HP</label><input type="text" name="no_hp" class="form-input" placeholder="08xxx"></div>
                <div><label class="form-label">Email</label><input type="email" name="email" class="form-input"></div>
                <div class="col-span-2"><label class="form-label">Alamat</label><textarea name="alamat" rows="2" class="form-input"></textarea></div>
                <div><label class="form-label">Kota</label><input type="text" name="kota" class="form-input"></div>
                <div><label class="form-label">Provinsi</label><input type="text" name="provinsi" class="form-input"></div>
                <div><label class="form-label">Kode Pos</label><input type="text" name="kode_pos" class="form-input"></div>
            </div>
            <div class="flex justify-end gap-3 px-5 py-4 border-t"><button type="submit" class="btn btn-primary btn-sm">Simpan</button><button type="button" onclick="closeModal('modal-tambah-cst')" class="btn btn-outline btn-sm">Batal</button></div>
        </form>
    </div>
</div>
@endsection
@push('scripts')<script>function toggleDropdown(id){const menu=document.getElementById(id+'-menu');menu.classList.toggle('hidden');document.addEventListener('click',function close(e){if(!document.getElementById(id).contains(e.target)){menu.classList.add('hidden');document.removeEventListener('click',close);}});}</script>@endpush
