import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { AuthenticatedLayout } from '@/layouts/AuthenticatedLayout';

type UserInfo = {
    id: number;
    name: string;
    email: string;
    jabatan?: string;
    foto?: string;
};

type PengaturanUsaha = {
    id: number;
    nama_usaha: string;
    deskripsi_usaha?: string;
    alamat?: string;
    no_hp?: string;
    email?: string;
    logo?: string;
};

type Props = {
    user: UserInfo;
    pengaturan: PengaturanUsaha;
};

export default function PengaturanIndex({ user, pengaturan }: Props) {
    const [activeTab, setActiveTab] = useState<'profile' | 'password' | 'usaha'>('profile');

    // Profil Form
    const profilForm = useForm({
        _method: 'PUT',
        name: user.name,
        jabatan: user.jabatan || '',
        foto: null as File | null,
    });

    // Password Form
    const passwordForm = useForm({
        password_lama: '',
        password_baru: '',
        password_baru_confirmation: '',
    });

    // Usaha Form
    const usahaForm = useForm({
        _method: 'PUT',
        nama_usaha: pengaturan.nama_usaha,
        deskripsi_usaha: pengaturan.deskripsi_usaha || '',
        alamat: pengaturan.alamat || '',
        no_hp: pengaturan.no_hp || '',
        email: pengaturan.email || '',
        logo: null as File | null,
    });

    const [blurErrors, setBlurErrors] = useState<Record<string, string>>({});

    const validateField = (formName: 'profil' | 'password' | 'usaha', field: string, val: any) => {
        let err = '';
        if (formName === 'profil') {
            if (field === 'name' && !val) {
                err = 'Nama wajib diisi.';
            }
        } else if (formName === 'password') {
            if (field === 'password_lama' && !val) {
                err = 'Password saat ini wajib diisi.';
            } else if (field === 'password_baru') {
                if (!val) err = 'Password baru wajib diisi.';
                else if (val.length < 8) err = 'Password baru minimal 8 karakter.';
            } else if (field === 'password_baru_confirmation') {
                if (!val) err = 'Konfirmasi password baru wajib diisi.';
                else if (val !== passwordForm.data.password_baru) err = 'Konfirmasi password tidak cocok.';
            }
        } else if (formName === 'usaha') {
            if (field === 'nama_usaha' && !val) {
                err = 'Nama usaha wajib diisi.';
            }
        }
        setBlurErrors(prev => ({ ...prev, [`${formName}_${field}`]: err }));
    };

    const handleProfilSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        profilForm.post('/pengaturan/profil', {
            forceFormData: true,
            onSuccess: () => {
                profilForm.reset('foto');
                setBlurErrors({});
            },
        });
    };

    const handlePasswordSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        passwordForm.put('/pengaturan/password', {
            onSuccess: () => {
                passwordForm.reset();
                setBlurErrors({});
            },
        });
    };

    const handleUsahaSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        usahaForm.post('/pengaturan/usaha', {
            forceFormData: true,
            onSuccess: () => {
                usahaForm.reset('logo');
                setBlurErrors({});
            },
        });
    };

    return (
        <AuthenticatedLayout
            pageTitle="Pengaturan Sistem"
            breadcrumb="Ubah profil personal, amankan kata sandi, dan sesuaikan profil usaha UMKM Anda"
        >
            <Head title="Pengaturan" />

            <div className="flex flex-col lg:flex-row gap-6">
                {/* Tabs Sidebar */}
                <div className="w-full lg:w-1/4 flex flex-col gap-2">
                    <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center text-center">
                        <div className="w-20 h-20 rounded-full overflow-hidden bg-slate-100 border-2 border-blue-100 flex items-center justify-center relative mb-3">
                            {user.foto ? (
                                <img src={`/storage/${user.foto}`} alt={user.name} className="w-full h-full object-cover" />
                            ) : (
                                <i className="ri-user-line text-4xl text-slate-400"></i>
                            )}
                        </div>
                        <h4 className="font-bold text-slate-800 text-sm">{user.name}</h4>
                        <span className="text-[11px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">
                            {user.jabatan || 'Admin'}
                        </span>
                        <span className="text-xs text-gray-400 mt-1">{user.email}</span>
                    </div>

                    <div className="bg-white p-2 rounded-xl border border-gray-100 shadow-sm flex flex-row lg:flex-col gap-1 overflow-x-auto lg:overflow-x-visible">
                        <button
                            onClick={() => setActiveTab('profile')}
                            className={`flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap cursor-pointer w-full text-left ${
                                activeTab === 'profile'
                                    ? 'bg-blue-600 text-white shadow shadow-blue-500/20'
                                    : 'text-slate-600 hover:bg-slate-50'
                            }`}
                        >
                            <i className="ri-user-settings-line text-sm"></i> Profil Pengguna
                        </button>
                        <button
                            onClick={() => setActiveTab('password')}
                            className={`flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap cursor-pointer w-full text-left ${
                                activeTab === 'password'
                                    ? 'bg-blue-600 text-white shadow shadow-blue-500/20'
                                    : 'text-slate-600 hover:bg-slate-50'
                            }`}
                        >
                            <i className="ri-lock-password-line text-sm"></i> Keamanan Sandi
                        </button>
                        <button
                            onClick={() => setActiveTab('usaha')}
                            className={`flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap cursor-pointer w-full text-left ${
                                activeTab === 'usaha'
                                    ? 'bg-blue-600 text-white shadow shadow-blue-500/20'
                                    : 'text-slate-600 hover:bg-slate-50'
                            }`}
                        >
                            <i className="ri-store-2-line text-sm"></i> Informasi Usaha
                        </button>
                    </div>
                </div>

                {/* Main Content Area */}
                <div className="flex-1 bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    {/* TAB: PROFILE */}
                    {activeTab === 'profile' && (
                        <div className="space-y-6">
                            <div>
                                <h3 className="text-base font-bold text-slate-800">Profil Pengguna</h3>
                                <p className="text-xs text-gray-400">Sesuaikan data identitas diri Anda pada sistem SIMOPRO</p>
                            </div>
                            <hr className="border-gray-50" />
                            <form onSubmit={handleProfilSubmit} className="space-y-4 max-w-xl">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Nama Lengkap <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={profilForm.data.name}
                                        onChange={(e) => profilForm.setData('name', e.target.value)}
                                        onBlur={(e) => validateField('profil', 'name', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            profilForm.errors.name || blurErrors.profil_name ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(profilForm.errors.name || blurErrors.profil_name) && (
                                        <p className="mt-1 text-xs text-rose-500">{profilForm.errors.name || blurErrors.profil_name}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Jabatan
                                    </label>
                                    <input
                                        type="text"
                                        placeholder="cth: Owner, Manajer, Staf Gudang..."
                                        value={profilForm.data.jabatan}
                                        onChange={(e) => profilForm.setData('jabatan', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Foto Profil Baru
                                    </label>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        onChange={(e) => profilForm.setData('foto', e.target.files ? e.target.files[0] : null)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                    />
                                    {profilForm.errors.foto && (
                                        <p className="mt-1 text-xs text-rose-500">{profilForm.errors.foto}</p>
                                    )}
                                </div>

                                <div className="pt-3 flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={profilForm.processing}
                                        className="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm cursor-pointer"
                                    >
                                        {profilForm.processing ? 'Menyimpan...' : 'Simpan Profil'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    )}

                    {/* TAB: PASSWORD */}
                    {activeTab === 'password' && (
                        <div className="space-y-6">
                            <div>
                                <h3 className="text-base font-bold text-slate-800">Keamanan Sandi</h3>
                                <p className="text-xs text-gray-400">Perbarui kata sandi Anda secara berkala demi keamanan data operasional</p>
                            </div>
                            <hr className="border-gray-50" />
                            <form onSubmit={handlePasswordSubmit} className="space-y-4 max-w-xl">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Password Saat Ini <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="password"
                                        value={passwordForm.data.password_lama}
                                        onChange={(e) => passwordForm.setData('password_lama', e.target.value)}
                                        onBlur={(e) => validateField('password', 'password_lama', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            passwordForm.errors.password_lama || blurErrors.password_password_lama ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(passwordForm.errors.password_lama || blurErrors.password_password_lama) && (
                                        <p className="mt-1 text-xs text-rose-500">{passwordForm.errors.password_lama || blurErrors.password_password_lama}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Password Baru <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="password"
                                        value={passwordForm.data.password_baru}
                                        onChange={(e) => passwordForm.setData('password_baru', e.target.value)}
                                        onBlur={(e) => validateField('password', 'password_baru', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            passwordForm.errors.password_baru || blurErrors.password_password_baru ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(passwordForm.errors.password_baru || blurErrors.password_password_baru) && (
                                        <p className="mt-1 text-xs text-rose-500">{passwordForm.errors.password_baru || blurErrors.password_password_baru}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Konfirmasi Password Baru <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="password"
                                        value={passwordForm.data.password_baru_confirmation}
                                        onChange={(e) => passwordForm.setData('password_baru_confirmation', e.target.value)}
                                        onBlur={(e) => validateField('password', 'password_baru_confirmation', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            passwordForm.errors.password_baru_confirmation || blurErrors.password_password_baru_confirmation ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(passwordForm.errors.password_baru_confirmation || blurErrors.password_password_baru_confirmation) && (
                                        <p className="mt-1 text-xs text-rose-500">
                                            {passwordForm.errors.password_baru_confirmation || blurErrors.password_password_baru_confirmation}
                                        </p>
                                    )}
                                </div>

                                <div className="pt-3 flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={passwordForm.processing}
                                        className="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm cursor-pointer animate-fade-in"
                                    >
                                        {passwordForm.processing ? 'Memproses...' : 'Perbarui Password'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    )}

                    {/* TAB: USAHA */}
                    {activeTab === 'usaha' && (
                        <div className="space-y-6 animate-fade-in">
                            <div className="flex justify-between items-start">
                                <div>
                                    <h3 className="text-base font-bold text-slate-800">Informasi Usaha (UMKM)</h3>
                                    <p className="text-xs text-gray-400">Ubah identitas profil usaha yang akan dicantumkan pada kuitansi/invoice</p>
                                </div>
                                {pengaturan.logo && (
                                    <div className="w-12 h-12 rounded bg-slate-50 border border-gray-100 p-1 flex items-center justify-center">
                                        <img src={`/storage/${pengaturan.logo}`} alt="Logo Usaha" className="max-w-full max-h-full object-contain" />
                                    </div>
                                )}
                            </div>
                            <hr className="border-gray-50" />
                            <form onSubmit={handleUsahaSubmit} className="space-y-4 max-w-xl">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Nama Usaha <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={usahaForm.data.nama_usaha}
                                        onChange={(e) => usahaForm.setData('nama_usaha', e.target.value)}
                                        onBlur={(e) => validateField('usaha', 'nama_usaha', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            usahaForm.errors.nama_usaha || blurErrors.usaha_nama_usaha ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(usahaForm.errors.nama_usaha || blurErrors.usaha_nama_usaha) && (
                                        <p className="mt-1 text-xs text-rose-500">{usahaForm.errors.nama_usaha || blurErrors.usaha_nama_usaha}</p>
                                    )}
                                </div>

                                <div className="grid grid-cols-2 gap-4 animate-scale-up">
                                    <div>
                                        <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                            No. Telepon / HP
                                        </label>
                                        <input
                                            type="text"
                                            value={usahaForm.data.no_hp}
                                            onChange={(e) => usahaForm.setData('no_hp', e.target.value)}
                                            className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                            E-mail Usaha
                                        </label>
                                        <input
                                            type="email"
                                            value={usahaForm.data.email}
                                            onChange={(e) => usahaForm.setData('email', e.target.value)}
                                            className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Alamat Lengkap
                                    </label>
                                    <textarea
                                        rows={2}
                                        value={usahaForm.data.alamat}
                                        onChange={(e) => usahaForm.setData('alamat', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Deskripsi Usaha
                                    </label>
                                    <textarea
                                        rows={2}
                                        value={usahaForm.data.deskripsi_usaha}
                                        onChange={(e) => usahaForm.setData('deskripsi_usaha', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Logo Usaha Baru
                                    </label>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        onChange={(e) => usahaForm.setData('logo', e.target.files ? e.target.files[0] : null)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                    />
                                    {usahaForm.errors.logo && (
                                        <p className="mt-1 text-xs text-rose-500">{usahaForm.errors.logo}</p>
                                    )}
                                </div>

                                <div className="pt-3 flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={usahaForm.processing}
                                        className="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm cursor-pointer"
                                    >
                                        {usahaForm.processing ? 'Menyimpan...' : 'Simpan Informasi Usaha'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
