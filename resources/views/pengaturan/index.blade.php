@extends('layouts.app')
@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')
@section('breadcrumb', 'Kelola profil pengguna dan informasi usaha')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Kolom Kiri: Navigasi Tab --}}
    <div class="xl:col-span-1">
        <div class="card p-3">
            <nav class="space-y-1">
                <a href="#profil" onclick="showTab('tab-profil')"
                   class="tab-nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all active"
                   id="nav-profil">
                    <i class="ri-user-line text-lg"></i> Profil Pengguna
                </a>
                <a href="#password" onclick="showTab('tab-password')"
                   class="tab-nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all"
                   id="nav-password">
                    <i class="ri-lock-line text-lg"></i> Ubah Password
                </a>
                <a href="#usaha" onclick="showTab('tab-usaha')"
                   class="tab-nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all"
                   id="nav-usaha">
                    <i class="ri-store-line text-lg"></i> Informasi Usaha
                </a>
            </nav>
        </div>
    </div>

    {{-- Kolom Kanan: Konten Tab --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- TAB PROFIL --}}
        <div id="tab-profil" class="tab-content card p-6">
            <h3 class="font-bold text-gray-900 text-lg mb-5">Profil Pengguna</h3>
            <form method="POST" action="{{ route('pengaturan.profil') }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="flex items-center gap-5 mb-6">
                    @if($user->foto)
                        <img src="{{ $user->foto_url }}" class="w-20 h-20 rounded-2xl object-cover ring-4 ring-blue-100" id="fotoPreview" alt="">
                    @else
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center text-white font-bold text-2xl" id="fotoPreviewDefault">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    @endif
                    <div>
                        <label class="btn btn-outline btn-sm cursor-pointer">
                            <i class="ri-camera-line"></i> Ubah Foto
                            <input type="file" name="foto" class="hidden" accept="image/*" onchange="previewProfilePhoto(this)">
                        </label>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG maks 2MB</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ $user->name }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" value="{{ $user->email }}" class="form-input bg-gray-50" disabled>
                        <p class="text-xs text-gray-400 mt-1">Email tidak dapat diubah</p>
                    </div>
                    <div>
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" value="{{ $user->jabatan }}" class="form-input" placeholder="Owner / Manager / Staff">
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="ri-save-line"></i> Simpan Profil</button>
                </div>
            </form>
        </div>

        {{-- TAB PASSWORD --}}
        <div id="tab-password" class="tab-content card p-6 hidden">
            <h3 class="font-bold text-gray-900 text-lg mb-5">Ubah Password</h3>
            <form method="POST" action="{{ route('pengaturan.password') }}">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Password Saat Ini <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="password_lama" class="form-input pr-10" required placeholder="Masukkan password lama">
                            <button type="button" onclick="togglePass('password_lama')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="ri-eye-line"></i></button>
                        </div>
                        @error('password_lama')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Password Baru <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="password_baru" class="form-input pr-10" required placeholder="Minimal 8 karakter">
                            <button type="button" onclick="togglePass('password_baru')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="ri-eye-line"></i></button>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                        <input type="password" name="password_baru_confirmation" class="form-input" required placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="ri-lock-line"></i> Ubah Password</button>
                </div>
            </form>
        </div>

        {{-- TAB USAHA --}}
        <div id="tab-usaha" class="tab-content card p-6 hidden">
            <h3 class="font-bold text-gray-900 text-lg mb-5">Informasi Usaha</h3>
            <form method="POST" action="{{ route('pengaturan.usaha') }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="flex items-center gap-5 mb-6">
                    @if($pengaturan->logo)
                        <img src="{{ $pengaturan->logo_url }}" class="w-20 h-20 rounded-2xl object-cover border" alt="Logo">
                    @else
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl flex items-center justify-center text-white font-bold text-3xl"><i class="ri-shoe-line"></i></div>
                    @endif
                    <div>
                        <label class="btn btn-outline btn-sm cursor-pointer">
                            <i class="ri-image-line"></i> Ubah Logo
                            <input type="file" name="logo" class="hidden" accept="image/*">
                        </label>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, SVG maks 2MB</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Nama Usaha <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_usaha" value="{{ $pengaturan->nama_usaha }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Deskripsi Usaha</label>
                        <textarea name="deskripsi_usaha" rows="2" class="form-input">{{ $pengaturan->deskripsi_usaha }}</textarea>
                    </div>
                    <div>
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" rows="2" class="form-input">{{ $pengaturan->alamat }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="form-label">No HP</label><input type="text" name="no_hp" value="{{ $pengaturan->no_hp }}" class="form-input"></div>
                        <div><label class="form-label">Email</label><input type="email" name="email" value="{{ $pengaturan->email }}" class="form-input"></div>
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="ri-save-line"></i> Simpan Informasi Usaha</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.tab-nav-link { color: #64748b; }
.tab-nav-link.active { background: #eff6ff; color: #2563eb; }
.tab-nav-link:hover:not(.active) { background: #f8fafc; }
</style>
@endsection

@push('scripts')
<script>
function showTab(tabId) {
    // Sembunyikan semua tab
    document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
    document.querySelectorAll('.tab-nav-link').forEach(l => l.classList.remove('active'));

    // Tampilkan tab yang dipilih
    document.getElementById(tabId).classList.remove('hidden');
    const navId = 'nav-' + tabId.replace('tab-', '');
    document.getElementById(navId)?.classList.add('active');
}

function togglePass(inputId) {
    const input = document.getElementById(inputId) || document.querySelector(`[name="${inputId}"]`);
    if (input) input.type = input.type === 'password' ? 'text' : 'password';
}

function previewProfilePhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const preview = document.getElementById('fotoPreview');
            const def = document.getElementById('fotoPreviewDefault');
            if (preview) { preview.src = e.target.result; }
            else if (def) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-20 h-20 rounded-2xl object-cover ring-4 ring-blue-100';
                img.id = 'fotoPreview';
                def.replaceWith(img);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
