import React, { useState, useEffect } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { AuthenticatedLayout } from '@/layouts/AuthenticatedLayout';
import { Karyawan, PaginatedData } from '@/types';

type Props = {
    karyawan: PaginatedData<Karyawan>;
    posisiList: string[];
    filters: {
        search?: string;
        posisi?: string;
        status?: string;
    };
};

export default function KaryawanIndex({ karyawan, posisiList, filters }: Props) {
    const [searchVal, setSearchVal] = useState(filters.search || '');
    const [posisiVal, setPosisiVal] = useState(filters.posisi || '');
    const [statusVal, setStatusVal] = useState(filters.status || '');
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [isDetailModalOpen, setIsDetailModalOpen] = useState(false);
    const [isImportModalOpen, setIsImportModalOpen] = useState(false);
    const [selectedKaryawan, setSelectedKaryawan] = useState<Karyawan | null>(null);

    // Filter submit handler
    const handleFilterSubmit = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        router.get('/karyawan', {
            search: searchVal,
            posisi: posisiVal,
            status: statusVal,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    // Trigger filters dynamically when selection inputs change
    useEffect(() => {
        handleFilterSubmit();
    }, [posisiVal, statusVal]);

    // Create Form Hook
    const createForm = useForm({
        nama: '',
        posisi: '',
        divisi: '',
        tanggal_lahir: '',
        no_hp: '',
        email: '',
        alamat: '',
        tanggal_bergabung: '',
        no_rekening: '',
        status: 'aktif',
        deskripsi: '',
        foto: null as File | null,
    });

    // Edit Form Hook
    const editForm = useForm({
        nama: '',
        posisi: '',
        divisi: '',
        tanggal_lahir: '',
        no_hp: '',
        email: '',
        alamat: '',
        tanggal_bergabung: '',
        no_rekening: '',
        status: 'aktif',
        deskripsi: '',
        foto: null as File | null,
        _method: 'PUT',
    });

    // Import Form Hook
    const importForm = useForm({
        file: null as File | null,
    });

    const [blurErrors, setBlurErrors] = useState<Record<string, string>>({});

    const validateField = (field: string, value: any) => {
        let err = '';
        if (field === 'nama' && !value) {
            err = 'Nama karyawan wajib diisi.';
        } else if (field === 'posisi' && !value) {
            err = 'Posisi wajib diisi.';
        }
        setBlurErrors(prev => ({ ...prev, [field]: err }));
    };

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post('/karyawan', {
            onSuccess: () => {
                setIsCreateModalOpen(false);
                createForm.reset();
                setBlurErrors({});
            },
        });
    };

    const handleEditClick = (k: Karyawan) => {
        setSelectedKaryawan(k);
        editForm.setData({
            nama: k.nama,
            posisi: k.posisi,
            divisi: k.divisi || '',
            tanggal_lahir: k.tanggal_lahir || '',
            no_hp: k.no_hp || '',
            email: k.email || '',
            alamat: k.alamat || '',
            tanggal_bergabung: k.tanggal_bergabung || '',
            no_rekening: k.no_rekening || '',
            status: k.status,
            deskripsi: k.deskripsi || '',
            foto: null,
            _method: 'PUT',
        });
        setIsEditModalOpen(true);
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedKaryawan) return;
        editForm.post(`/karyawan/${selectedKaryawan.id}`, {
            onSuccess: () => {
                setIsEditModalOpen(false);
                editForm.reset();
                setBlurErrors({});
            },
        });
    };

    const handleDelete = (k: Karyawan) => {
        if (confirm(`Apakah Anda yakin ingin menghapus karyawan ${k.nama}?`)) {
            router.delete(`/karyawan/${k.id}`);
        }
    };

    const handleImportSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        importForm.post('/karyawan/import', {
            onSuccess: () => {
                setIsImportModalOpen(false);
                importForm.reset();
            },
        });
    };

    return (
        <AuthenticatedLayout
            pageTitle="Data Master Karyawan"
            breadcrumb="Kelola profil, jabatan, dan detail kontak staf Provillo"
            headerActions={
                <div className="flex flex-wrap items-center gap-2">
                    <a
                        href="/karyawan/export/excel"
                        className="flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm hover:shadow"
                    >
                        <i className="ri-file-excel-2-line text-sm"></i> Export Excel
                    </a>
                    <a
                        href="/karyawan/export/pdf"
                        className="flex items-center gap-1.5 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm hover:shadow"
                    >
                        <i className="ri-file-pdf-line text-sm"></i> Export PDF
                    </a>
                    <button
                        onClick={() => setIsImportModalOpen(true)}
                        className="flex items-center gap-1.5 px-3 py-2 bg-slate-700 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition-all shadow-sm hover:shadow cursor-pointer"
                    >
                        <i className="ri-file-upload-line text-sm"></i> Import Excel
                    </button>
                    <button
                        onClick={() => setIsCreateModalOpen(true)}
                        className="flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm hover:shadow-blue-500/20 shadow-lg cursor-pointer"
                    >
                        <i className="ri-add-line text-sm"></i> Tambah Karyawan
                    </button>
                </div>
            }
        >
            <Head title="Data Karyawan" />

            {/* Filter Section */}
            <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <form onSubmit={handleFilterSubmit} className="flex-1 flex gap-2">
                    <div className="relative flex-1 max-w-sm">
                        <i className="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            placeholder="Cari kode, nama atau posisi..."
                            value={searchVal}
                            onChange={(e) => setSearchVal(e.target.value)}
                            className="w-full bg-slate-50 border-0 rounded-lg pl-10 pr-4 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:bg-white border-1.5 border-gray-100 focus:border-blue-500 transition-all"
                        />
                    </div>
                    <button
                        type="submit"
                        className="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg transition-all"
                    >
                        Cari
                    </button>
                </form>

                <div className="flex flex-wrap items-center gap-4">
                    <div className="flex items-center gap-2">
                        <span className="text-xs text-gray-400 font-bold uppercase">Jabatan:</span>
                        <select
                            value={posisiVal}
                            onChange={(e) => setPosisiVal(e.target.value)}
                            className="bg-slate-50 border-1.5 border-gray-100 rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:border-blue-500"
                        >
                            <option value="">Semua Posisi</option>
                            {posisiList.map((pos) => (
                                <option key={pos} value={pos}>{pos}</option>
                            ))}
                        </select>
                    </div>

                    <div className="flex items-center gap-2">
                        <span className="text-xs text-gray-400 font-bold uppercase">Status:</span>
                        <select
                            value={statusVal}
                            onChange={(e) => setStatusVal(e.target.value)}
                            className="bg-slate-50 border-1.5 border-gray-100 rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:border-blue-500"
                        >
                            <option value="">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>

            {/* Table / List View */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="bg-slate-50 text-slate-400 text-[10px] font-bold uppercase tracking-wider border-b border-gray-100">
                                <th className="px-6 py-4">Foto</th>
                                <th className="px-6 py-4">Kode Karyawan</th>
                                <th className="px-6 py-4">Nama</th>
                                <th className="px-6 py-4">Posisi / Divisi</th>
                                <th className="px-6 py-4">No HP / Email</th>
                                <th className="px-6 py-4">Status</th>
                                <th className="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-50">
                            {karyawan.data.length > 0 ? (
                                karyawan.data.map((k) => (
                                    <tr key={k.id} className="hover:bg-slate-50/50 transition-colors text-sm">
                                        <td className="px-6 py-3">
                                            {k.foto_url ? (
                                                <img
                                                    src={k.foto_url}
                                                    alt={k.nama}
                                                    className="w-10 h-10 rounded-full object-cover border border-gray-100 ring-2 ring-blue-500/20"
                                                />
                                            ) : (
                                                <div className="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 text-sm font-bold uppercase">
                                                    {k.nama.charAt(0)}
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-6 py-3 font-mono text-xs font-bold text-gray-500">
                                            {k.kode_karyawan}
                                        </td>
                                        <td className="px-6 py-3 font-bold text-slate-900">
                                            {k.nama}
                                        </td>
                                        <td className="px-6 py-3">
                                            <p className="font-semibold text-slate-800">{k.posisi}</p>
                                            <p className="text-xs text-gray-400">{k.divisi || '-'}</p>
                                        </td>
                                        <td className="px-6 py-3">
                                            <p className="text-slate-800 font-semibold">{k.no_hp || '-'}</p>
                                            <p className="text-xs text-gray-400">{k.email || '-'}</p>
                                        </td>
                                        <td className="px-6 py-3">
                                            <span className={`inline-block text-xs font-bold px-2.5 py-0.5 rounded-full ${
                                                k.status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800'
                                            }`}>
                                                {k.status}
                                            </span>
                                        </td>
                                        <td className="px-6 py-3 text-right">
                                            <div className="flex items-center justify-end gap-1.5">
                                                <button
                                                    onClick={() => {
                                                        setSelectedKaryawan(k);
                                                        setIsDetailModalOpen(true);
                                                    }}
                                                    className="p-1.5 text-slate-500 hover:text-blue-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                    title="Detail"
                                                >
                                                    <i className="ri-eye-line text-base"></i>
                                                </button>
                                                <button
                                                    onClick={() => handleEditClick(k)}
                                                    className="p-1.5 text-slate-500 hover:text-amber-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                    title="Edit"
                                                >
                                                    <i className="ri-edit-line text-base"></i>
                                                </button>
                                                <button
                                                    onClick={() => handleDelete(k)}
                                                    className="p-1.5 text-slate-500 hover:text-rose-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                    title="Hapus"
                                                >
                                                    <i className="ri-delete-bin-line text-base"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={7} className="px-6 py-12 text-center text-gray-400">
                                        <i className="ri-inbox-line text-4xl block mb-2 opacity-50"></i>
                                        <p className="text-xs font-semibold">Belum ada data karyawan tersedia</p>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {karyawan.links && karyawan.links.length > 3 && (
                    <div className="flex justify-between items-center px-6 py-4 bg-slate-50 border-t border-gray-100">
                        <span className="text-xs text-gray-400 font-semibold">
                            Menampilkan {karyawan.from || 0} - {karyawan.to || 0} dari {karyawan.total || 0} data
                        </span>
                        <div className="flex gap-1">
                            {karyawan.links.map((link, idx) => {
                                if (link.url === null) return null;
                                return (
                                    <Link
                                        key={idx}
                                        href={link.url}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                        className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all ${
                                            link.active
                                                ? 'bg-blue-600 text-white shadow shadow-blue-500/20'
                                                : 'text-slate-500 hover:bg-slate-200'
                                        }`}
                                    />
                                );
                            })}
                        </div>
                    </div>
                )}
            </div>

            {/* CREATE MODAL */}
            {isCreateModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
                    <div className="bg-white w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100 my-8">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-2">
                                <i className="ri-add-circle-line text-blue-600 text-lg"></i> Tambah Karyawan Baru
                            </h3>
                            <button
                                onClick={() => {
                                    setIsCreateModalOpen(false);
                                    createForm.reset();
                                    setBlurErrors({});
                                }}
                                className="text-gray-400 hover:text-gray-600"
                            >
                                <i className="ri-close-line text-xl"></i>
                            </button>
                        </div>
                        <form onSubmit={handleCreateSubmit} className="p-5 space-y-4 max-h-[70vh] overflow-y-auto">
                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Nama Lengkap <span className="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={createForm.data.nama}
                                    onChange={(e) => createForm.setData('nama', e.target.value)}
                                    onBlur={(e) => validateField('nama', e.target.value)}
                                    placeholder="Masukkan nama karyawan..."
                                    className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                        createForm.errors.nama || blurErrors.nama ? 'border-rose-500 bg-rose-50/20' : ''
                                    }`}
                                />
                                {(createForm.errors.nama || blurErrors.nama) && (
                                    <p className="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                        <i className="ri-error-warning-line"></i> {createForm.errors.nama || blurErrors.nama}
                                    </p>
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Posisi / Jabatan <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={createForm.data.posisi}
                                        onChange={(e) => createForm.setData('posisi', e.target.value)}
                                        onBlur={(e) => validateField('posisi', e.target.value)}
                                        placeholder="misal: Staf Produksi, Kurir"
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            createForm.errors.posisi || blurErrors.posisi ? 'border-rose-500 bg-rose-50/20' : ''
                                        }`}
                                    />
                                    {(createForm.errors.posisi || blurErrors.posisi) && (
                                        <p className="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                            <i className="ri-error-warning-line"></i> {createForm.errors.posisi || blurErrors.posisi}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Divisi
                                    </label>
                                    <input
                                        type="text"
                                        value={createForm.data.divisi}
                                        onChange={(e) => createForm.setData('divisi', e.target.value)}
                                        placeholder="misal: Operasional, Gudang"
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        No HP
                                    </label>
                                    <input
                                        type="text"
                                        value={createForm.data.no_hp}
                                        onChange={(e) => createForm.setData('no_hp', e.target.value)}
                                        placeholder="misal: 0812xxxxxxxx"
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Email
                                    </label>
                                    <input
                                        type="email"
                                        value={createForm.data.email}
                                        onChange={(e) => createForm.setData('email', e.target.value)}
                                        placeholder="karyawan@provillo.com"
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Tanggal Lahir
                                    </label>
                                    <input
                                        type="date"
                                        value={createForm.data.tanggal_lahir}
                                        onChange={(e) => createForm.setData('tanggal_lahir', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Tanggal Bergabung
                                    </label>
                                    <input
                                        type="date"
                                        value={createForm.data.tanggal_bergabung}
                                        onChange={(e) => createForm.setData('tanggal_bergabung', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        No Rekening
                                    </label>
                                    <input
                                        type="text"
                                        value={createForm.data.no_rekening}
                                        onChange={(e) => createForm.setData('no_rekening', e.target.value)}
                                        placeholder="misal: BCA - 8023xxxxxx"
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Status Karyawan <span className="text-rose-500">*</span>
                                    </label>
                                    <select
                                        value={createForm.data.status}
                                        onChange={(e) => createForm.setData('status', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    >
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Alamat Lengkap
                                </label>
                                <textarea
                                    value={createForm.data.alamat}
                                    onChange={(e) => createForm.setData('alamat', e.target.value)}
                                    placeholder="Masukkan alamat tinggal karyawan..."
                                    rows={2}
                                    className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Foto Profil
                                </label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={(e) => createForm.setData('foto', e.target.files?.[0] || null)}
                                    className="w-full border-1.5 border-dashed border-gray-200 rounded-lg p-3 text-xs text-slate-500 focus:outline-none"
                                />
                                {createForm.errors.foto && (
                                    <p className="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                        <i className="ri-error-warning-line"></i> {createForm.errors.foto}
                                    </p>
                                )}
                            </div>

                            <div className="flex justify-end gap-2 pt-3 border-t border-gray-100">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setIsCreateModalOpen(false);
                                        createForm.reset();
                                        setBlurErrors({});
                                    }}
                                    className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-lg transition-all"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={createForm.processing}
                                    className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all"
                                >
                                    {createForm.processing ? 'Menyimpan...' : 'Simpan Karyawan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* EDIT MODAL */}
            {isEditModalOpen && selectedKaryawan && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
                    <div className="bg-white w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100 my-8">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-2">
                                <i className="ri-edit-circle-line text-amber-500 text-lg"></i> Edit Data Karyawan
                            </h3>
                            <button
                                onClick={() => {
                                    setIsEditModalOpen(false);
                                    editForm.reset();
                                    setBlurErrors({});
                                }}
                                className="text-gray-400 hover:text-gray-600"
                            >
                                <i className="ri-close-line text-xl"></i>
                            </button>
                        </div>
                        <form onSubmit={handleEditSubmit} className="p-5 space-y-4 max-h-[70vh] overflow-y-auto">
                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Nama Lengkap <span className="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={editForm.data.nama}
                                    onChange={(e) => editForm.setData('nama', e.target.value)}
                                    onBlur={(e) => validateField('nama', e.target.value)}
                                    className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                        editForm.errors.nama || blurErrors.nama ? 'border-rose-500 bg-rose-50/20' : ''
                                    }`}
                                />
                                {(editForm.errors.nama || blurErrors.nama) && (
                                    <p className="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                        <i className="ri-error-warning-line"></i> {editForm.errors.nama || blurErrors.nama}
                                    </p>
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Posisi / Jabatan <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={editForm.data.posisi}
                                        onChange={(e) => editForm.setData('posisi', e.target.value)}
                                        onBlur={(e) => validateField('posisi', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            editForm.errors.posisi || blurErrors.posisi ? 'border-rose-500 bg-rose-50/20' : ''
                                        }`}
                                    />
                                    {(editForm.errors.posisi || blurErrors.posisi) && (
                                        <p className="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                            <i className="ri-error-warning-line"></i> {editForm.errors.posisi || blurErrors.posisi}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Divisi
                                    </label>
                                    <input
                                        type="text"
                                        value={editForm.data.divisi}
                                        onChange={(e) => editForm.setData('divisi', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        No HP
                                    </label>
                                    <input
                                        type="text"
                                        value={editForm.data.no_hp}
                                        onChange={(e) => editForm.setData('no_hp', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Email
                                    </label>
                                    <input
                                        type="email"
                                        value={editForm.data.email}
                                        onChange={(e) => editForm.setData('email', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Tanggal Lahir
                                    </label>
                                    <input
                                        type="date"
                                        value={editForm.data.tanggal_lahir}
                                        onChange={(e) => editForm.setData('tanggal_lahir', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Tanggal Bergabung
                                    </label>
                                    <input
                                        type="date"
                                        value={editForm.data.tanggal_bergabung}
                                        onChange={(e) => editForm.setData('tanggal_bergabung', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        No Rekening
                                    </label>
                                    <input
                                        type="text"
                                        value={editForm.data.no_rekening}
                                        onChange={(e) => editForm.setData('no_rekening', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Status Karyawan <span className="text-rose-500">*</span>
                                    </label>
                                    <select
                                        value={editForm.data.status}
                                        onChange={(e) => editForm.setData('status', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    >
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Alamat Lengkap
                                </label>
                                <textarea
                                    value={editForm.data.alamat}
                                    onChange={(e) => editForm.setData('alamat', e.target.value)}
                                    rows={2}
                                    className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Ganti Foto Profil (Opsional)
                                </label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={(e) => editForm.setData('foto', e.target.files?.[0] || null)}
                                    className="w-full border-1.5 border-dashed border-gray-200 rounded-lg p-3 text-xs text-slate-500 focus:outline-none"
                                />
                                {editForm.errors.foto && (
                                    <p className="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                        <i className="ri-error-warning-line"></i> {editForm.errors.foto}
                                    </p>
                                )}
                            </div>

                            <div className="flex justify-end gap-2 pt-3 border-t border-gray-100">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setIsEditModalOpen(false);
                                        editForm.reset();
                                        setBlurErrors({});
                                    }}
                                    className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-lg transition-all"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={editForm.processing}
                                    className="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg transition-all"
                                >
                                    {editForm.processing ? 'Memperbarui...' : 'Simpan Perubahan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* DETAIL VIEW MODAL */}
            {isDetailModalOpen && selectedKaryawan && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
                    <div className="bg-white w-full max-w-md rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900">Detail Profil Karyawan</h3>
                            <button
                                onClick={() => setIsDetailModalOpen(false)}
                                className="text-gray-400 hover:text-gray-600"
                            >
                                <i className="ri-close-line text-xl"></i>
                            </button>
                        </div>
                        <div className="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                            {/* Profile Header */}
                            <div className="flex items-center gap-4">
                                {selectedKaryawan.foto_url ? (
                                    <img
                                        src={selectedKaryawan.foto_url}
                                        alt={selectedKaryawan.nama}
                                        className="w-20 h-20 rounded-full object-cover border border-gray-100 shadow-sm ring-2 ring-blue-500/20"
                                    />
                                ) : (
                                    <div className="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 text-2xl font-bold uppercase border border-gray-100">
                                        {selectedKaryawan.nama.charAt(0)}
                                    </div>
                                )}
                                <div>
                                    <h4 className="font-bold text-lg text-slate-900">{selectedKaryawan.nama}</h4>
                                    <p className="text-sm font-semibold text-slate-500">{selectedKaryawan.posisi}</p>
                                    <span className={`inline-block text-[9px] font-bold uppercase px-2 py-0.5 rounded-full mt-1 ${
                                        selectedKaryawan.status === 'aktif' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-600'
                                    }`}>
                                        {selectedKaryawan.status}
                                    </span>
                                </div>
                            </div>

                            <hr className="border-gray-100" />

                            {/* Details List */}
                            <div className="space-y-3 text-sm">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Kode Karyawan</p>
                                        <p className="font-mono font-bold text-slate-800">{selectedKaryawan.kode_karyawan}</p>
                                    </div>
                                    <div>
                                        <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Divisi</p>
                                        <p className="font-semibold text-slate-800">{selectedKaryawan.divisi || '-'}</p>
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">No Handphone</p>
                                        <p className="font-bold text-slate-800">{selectedKaryawan.no_hp || '-'}</p>
                                    </div>
                                    <div>
                                        <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Email</p>
                                        <p className="font-bold text-slate-800 truncate">{selectedKaryawan.email || '-'}</p>
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Tanggal Lahir</p>
                                        <p className="font-bold text-slate-800">{selectedKaryawan.tanggal_lahir || '-'}</p>
                                    </div>
                                    <div>
                                        <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Bergabung Sejak</p>
                                        <p className="font-bold text-slate-800">{selectedKaryawan.tanggal_bergabung || '-'}</p>
                                    </div>
                                </div>

                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">No Rekening Gaji</p>
                                    <p className="font-bold text-slate-800">{selectedKaryawan.no_rekening || '-'}</p>
                                </div>

                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Alamat Tempat Tinggal</p>
                                    <p className="font-medium text-slate-700 bg-slate-50 p-2.5 rounded-lg text-xs leading-relaxed">
                                        {selectedKaryawan.alamat || <span className="text-gray-400 italic">Tidak ada alamat.</span>}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* IMPORT EXCEL MODAL */}
            {isImportModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
                    <div className="bg-white w-full max-w-md rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-2">
                                <i className="ri-file-excel-2-line text-emerald-600 text-lg"></i> Import Data Excel
                            </h3>
                            <button
                                onClick={() => {
                                    setIsImportModalOpen(false);
                                    importForm.reset();
                                }}
                                className="text-gray-400 hover:text-gray-600"
                            >
                                <i className="ri-close-line text-xl"></i>
                            </button>
                        </div>
                        <form onSubmit={handleImportSubmit} className="p-5 space-y-4">
                            <div className="bg-emerald-50 text-emerald-800 text-xs p-4 rounded-xl border border-emerald-100 leading-relaxed flex gap-2.5">
                                <i className="ri-information-line text-base text-emerald-600 flex-shrink-0"></i>
                                <div>
                                    <span className="font-bold">Informasi Template:</span><br />
                                    Format kolom file Excel: (Kode Karyawan, Nama, Posisi, Divisi, No HP, Email, Alamat, Tanggal Bergabung, No Rekening, Status).
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Pilih File Excel (.xlsx / .xls)
                                </label>
                                <input
                                    type="file"
                                    accept=".xlsx, .xls"
                                    onChange={(e) => importForm.setData('file', e.target.files?.[0] || null)}
                                    className="w-full border-1.5 border-dashed border-gray-200 rounded-lg p-4 text-xs text-slate-500 focus:outline-none"
                                />
                                {importForm.errors.file && (
                                    <p className="mt-1.5 text-xs text-rose-500 flex items-center gap-1">
                                        <i className="ri-error-warning-line"></i> {importForm.errors.file}
                                    </p>
                                )}
                            </div>

                            <div className="flex justify-end gap-2 pt-3 border-t border-gray-50">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setIsImportModalOpen(false);
                                        importForm.reset();
                                    }}
                                    className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-lg transition-all"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={importForm.processing}
                                    className="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-all flex items-center gap-1"
                                >
                                    {importForm.processing ? 'Memproses...' : 'Mulai Import'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
