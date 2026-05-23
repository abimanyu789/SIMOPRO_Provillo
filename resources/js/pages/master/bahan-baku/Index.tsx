import React, { useState, useEffect } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { AuthenticatedLayout } from '@/layouts/AuthenticatedLayout';
import { BahanBaku, PaginatedData } from '@/types';

type Props = {
    bahanBaku: PaginatedData<BahanBaku>;
    kategoriList: string[];
    filters: {
        search?: string;
        kategori?: string;
    };
};

export default function BahanBakuIndex({ bahanBaku, kategoriList, filters }: Props) {
    const [searchVal, setSearchVal] = useState(filters.search || '');
    const [kategoriVal, setKategoriVal] = useState(filters.kategori || '');
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [isDetailModalOpen, setIsDetailModalOpen] = useState(false);
    const [isImportModalOpen, setIsImportModalOpen] = useState(false);
    const [selectedBahan, setSelectedBahan] = useState<BahanBaku | null>(null);

    // Filter submit handler
    const handleFilterSubmit = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        router.get('/bahan-baku', {
            search: searchVal,
            kategori: kategoriVal,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    // Trigger filter when category dropdown changes
    useEffect(() => {
        handleFilterSubmit();
    }, [kategoriVal]);

    // Create Form Hook
    const createForm = useForm({
        nama_bahan: '',
        kategori: '',
        satuan: '',
        harga_beli: '',
        deskripsi: '',
    });

    // Edit Form Hook
    const editForm = useForm({
        nama_bahan: '',
        kategori: '',
        satuan: '',
        harga_beli: '',
        deskripsi: '',
    });

    // Import Form Hook
    const importForm = useForm({
        file: null as File | null,
    });

    const [blurErrors, setBlurErrors] = useState<Record<string, string>>({});

    const validateField = (field: string, value: any) => {
        let err = '';
        if (field === 'nama_bahan' && !value) {
            err = 'Nama bahan baku wajib diisi.';
        } else if (field === 'kategori' && !value) {
            err = 'Kategori wajib diisi.';
        } else if (field === 'satuan' && !value) {
            err = 'Satuan wajib diisi.';
        } else if (field === 'harga_beli') {
            if (!value) err = 'Harga beli wajib diisi.';
            else if (isNaN(Number(value)) || Number(value) < 0) err = 'Harga beli harus berupa angka positif.';
        }
        setBlurErrors(prev => ({ ...prev, [field]: err }));
    };

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post('/bahan-baku', {
            onSuccess: () => {
                setIsCreateModalOpen(false);
                createForm.reset();
                setBlurErrors({});
            },
        });
    };

    const handleEditClick = (b: BahanBaku) => {
        setSelectedBahan(b);
        editForm.setData({
            nama_bahan: b.nama_bahan,
            kategori: b.kategori,
            satuan: b.satuan,
            harga_beli: String(b.harga_beli),
            deskripsi: b.deskripsi || '',
        });
        setIsEditModalOpen(true);
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedBahan) return;
        editForm.put(`/bahan-baku/${selectedBahan.id}`, {
            onSuccess: () => {
                setIsEditModalOpen(false);
                editForm.reset();
                setBlurErrors({});
            },
        });
    };

    const handleDelete = (b: BahanBaku) => {
        if (confirm(`Apakah Anda yakin ingin menghapus bahan baku ${b.nama_bahan}?`)) {
            router.delete(`/bahan-baku/${b.id}`);
        }
    };

    const handleImportSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        importForm.post('/bahan-baku/import', {
            onSuccess: () => {
                setIsImportModalOpen(false);
                importForm.reset();
            },
        });
    };

    const formatRupiah = (num: number) => {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    };

    return (
        <AuthenticatedLayout
            pageTitle="Data Master Bahan Baku"
            breadcrumb="Kelola pasokan dan inventaris bahan baku produksi Provillo"
            headerActions={
                <div className="flex flex-wrap items-center gap-2">
                    <a
                        href="/bahan-baku/export/excel"
                        className="flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm hover:shadow"
                    >
                        <i className="ri-file-excel-2-line text-sm"></i> Export Excel
                    </a>
                    <a
                        href="/bahan-baku/export/pdf"
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
                        <i className="ri-add-line text-sm"></i> Tambah Bahan
                    </button>
                </div>
            }
        >
            <Head title="Data Bahan Baku" />

            {/* Filter Section */}
            <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <form onSubmit={handleFilterSubmit} className="flex-1 flex gap-2">
                    <div className="relative flex-1 max-w-sm">
                        <i className="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            placeholder="Cari kode atau nama bahan baku..."
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

                <div className="flex items-center gap-2">
                    <span className="text-xs text-gray-400 font-bold uppercase">Kategori:</span>
                    <select
                        value={kategoriVal}
                        onChange={(e) => setKategoriVal(e.target.value)}
                        className="bg-slate-50 border-1.5 border-gray-100 rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:border-blue-500"
                    >
                        <option value="">Semua Kategori</option>
                        {kategoriList.map((kat) => (
                            <option key={kat} value={kat}>{kat}</option>
                        ))}
                    </select>
                </div>
            </div>

            {/* Table / List View */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="bg-slate-50 text-slate-400 text-[10px] font-bold uppercase tracking-wider border-b border-gray-100">
                                <th className="px-6 py-4">Kode Bahan</th>
                                <th className="px-6 py-4">Nama Bahan Baku</th>
                                <th className="px-6 py-4">Kategori</th>
                                <th className="px-6 py-4">Harga Beli</th>
                                <th className="px-6 py-4">Stok</th>
                                <th className="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-50">
                            {bahanBaku.data.length > 0 ? (
                                bahanBaku.data.map((b) => (
                                    <tr key={b.id} className="hover:bg-slate-50/50 transition-colors text-sm">
                                        <td className="px-6 py-4 font-mono text-xs font-bold text-gray-500">
                                            {b.kode_bahan}
                                        </td>
                                        <td className="px-6 py-4 font-bold text-slate-900">
                                            {b.nama_bahan}
                                        </td>
                                        <td className="px-6 py-4 text-slate-500 font-medium">
                                            {b.kategori}
                                        </td>
                                        <td className="px-6 py-4 font-extrabold text-slate-900">
                                            {formatRupiah(b.harga_beli)} <span className="text-xs text-gray-400 font-medium">/ {b.satuan}</span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className={`inline-block text-xs font-bold px-2.5 py-0.5 rounded-full ${
                                                (b.stok?.jumlah_stok || 0) <= 0 ? 'bg-rose-100 text-rose-800' :
                                                (b.stok?.jumlah_stok || 0) < (b.stok?.stok_minimum || 10) ? 'bg-amber-100 text-amber-800' :
                                                'bg-emerald-100 text-emerald-800'
                                            }`}>
                                                {b.stok?.jumlah_stok || 0} {b.satuan}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-1.5">
                                                <button
                                                    onClick={() => {
                                                        setSelectedBahan(b);
                                                        setIsDetailModalOpen(true);
                                                    }}
                                                    className="p-1.5 text-slate-500 hover:text-blue-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                    title="Detail"
                                                >
                                                    <i className="ri-eye-line text-base"></i>
                                                </button>
                                                <button
                                                    onClick={() => handleEditClick(b)}
                                                    className="p-1.5 text-slate-500 hover:text-amber-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                    title="Edit"
                                                >
                                                    <i className="ri-edit-line text-base"></i>
                                                </button>
                                                <button
                                                    onClick={() => handleDelete(b)}
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
                                    <td colSpan={6} className="px-6 py-12 text-center text-gray-400">
                                        <i className="ri-inbox-line text-4xl block mb-2 opacity-50"></i>
                                        <p className="text-xs font-semibold">Belum ada data bahan baku tersedia</p>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {bahanBaku.links && bahanBaku.links.length > 3 && (
                    <div className="flex justify-between items-center px-6 py-4 bg-slate-50 border-t border-gray-100">
                        <span className="text-xs text-gray-400 font-semibold">
                            Menampilkan {bahanBaku.from || 0} - {bahanBaku.to || 0} dari {bahanBaku.total || 0} data
                        </span>
                        <div className="flex gap-1">
                            {bahanBaku.links.map((link, idx) => {
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
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
                    <div className="bg-white w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-2">
                                <i className="ri-add-circle-line text-blue-600 text-lg"></i> Tambah Bahan Baku Baru
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
                        <form onSubmit={handleCreateSubmit} className="p-5 space-y-4">
                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Nama Bahan Baku <span className="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={createForm.data.nama_bahan}
                                    onChange={(e) => createForm.setData('nama_bahan', e.target.value)}
                                    onBlur={(e) => validateField('nama_bahan', e.target.value)}
                                    placeholder="Masukkan nama bahan baku..."
                                    className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                        createForm.errors.nama_bahan || blurErrors.nama_bahan ? 'border-rose-500 bg-rose-50/20' : ''
                                    }`}
                                />
                                {(createForm.errors.nama_bahan || blurErrors.nama_bahan) && (
                                    <p className="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                        <i className="ri-error-warning-line"></i> {createForm.errors.nama_bahan || blurErrors.nama_bahan}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Kategori <span className="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={createForm.data.kategori}
                                    onChange={(e) => createForm.setData('kategori', e.target.value)}
                                    onBlur={(e) => validateField('kategori', e.target.value)}
                                    placeholder="Masukkan kategori (misal: Kulit, Tali, Lem)..."
                                    className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                        createForm.errors.kategori || blurErrors.kategori ? 'border-rose-500 bg-rose-50/20' : ''
                                    }`}
                                />
                                {(createForm.errors.kategori || blurErrors.kategori) && (
                                    <p className="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                        <i className="ri-error-warning-line"></i> {createForm.errors.kategori || blurErrors.kategori}
                                    </p>
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Satuan <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={createForm.data.satuan}
                                        onChange={(e) => createForm.setData('satuan', e.target.value)}
                                        onBlur={(e) => validateField('satuan', e.target.value)}
                                        placeholder="misal: m, lembar, kg"
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            createForm.errors.satuan || blurErrors.satuan ? 'border-rose-500 bg-rose-50/20' : ''
                                        }`}
                                    />
                                    {(createForm.errors.satuan || blurErrors.satuan) && (
                                        <p className="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                            <i className="ri-error-warning-line"></i> {createForm.errors.satuan || blurErrors.satuan}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Harga Beli (Rp) <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        value={createForm.data.harga_beli}
                                        onChange={(e) => createForm.setData('harga_beli', e.target.value)}
                                        onBlur={(e) => validateField('harga_beli', e.target.value)}
                                        placeholder="Nominal harga..."
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            createForm.errors.harga_beli || blurErrors.harga_beli ? 'border-rose-500 bg-rose-50/20' : ''
                                        }`}
                                    />
                                    {(createForm.errors.harga_beli || blurErrors.harga_beli) && (
                                        <p className="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                            <i className="ri-error-warning-line"></i> {createForm.errors.harga_beli || blurErrors.harga_beli}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Deskripsi
                                </label>
                                <textarea
                                    value={createForm.data.deskripsi}
                                    onChange={(e) => createForm.setData('deskripsi', e.target.value)}
                                    placeholder="Masukkan detail atau deskripsi bahan..."
                                    rows={3}
                                    className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                />
                            </div>

                            <div className="flex justify-end gap-2 pt-3 border-t border-gray-50">
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
                                    className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all flex items-center gap-1"
                                >
                                    {createForm.processing ? 'Menyimpan...' : 'Simpan Bahan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* EDIT MODAL */}
            {isEditModalOpen && selectedBahan && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
                    <div className="bg-white w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-2">
                                <i className="ri-edit-circle-line text-amber-500 text-lg"></i> Edit Bahan Baku
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
                        <form onSubmit={handleEditSubmit} className="p-5 space-y-4">
                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Nama Bahan Baku <span className="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={editForm.data.nama_bahan}
                                    onChange={(e) => editForm.setData('nama_bahan', e.target.value)}
                                    onBlur={(e) => validateField('nama_bahan', e.target.value)}
                                    className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                        editForm.errors.nama_bahan || blurErrors.nama_bahan ? 'border-rose-500 bg-rose-50/20' : ''
                                    }`}
                                />
                                {(editForm.errors.nama_bahan || blurErrors.nama_bahan) && (
                                    <p className="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                        <i className="ri-error-warning-line"></i> {editForm.errors.nama_bahan || blurErrors.nama_bahan}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Kategori <span className="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={editForm.data.kategori}
                                    onChange={(e) => editForm.setData('kategori', e.target.value)}
                                    onBlur={(e) => validateField('kategori', e.target.value)}
                                    className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                        editForm.errors.kategori || blurErrors.kategori ? 'border-rose-500 bg-rose-50/20' : ''
                                    }`}
                                />
                                {(editForm.errors.kategori || blurErrors.kategori) && (
                                    <p className="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                        <i className="ri-error-warning-line"></i> {editForm.errors.kategori || blurErrors.kategori}
                                    </p>
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Satuan <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={editForm.data.satuan}
                                        onChange={(e) => editForm.setData('satuan', e.target.value)}
                                        onBlur={(e) => validateField('satuan', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            editForm.errors.satuan || blurErrors.satuan ? 'border-rose-500 bg-rose-50/20' : ''
                                        }`}
                                    />
                                    {(editForm.errors.satuan || blurErrors.satuan) && (
                                        <p className="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                            <i className="ri-error-warning-line"></i> {editForm.errors.satuan || blurErrors.satuan}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Harga Beli (Rp) <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        value={editForm.data.harga_beli}
                                        onChange={(e) => editForm.setData('harga_beli', e.target.value)}
                                        onBlur={(e) => validateField('harga_beli', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            editForm.errors.harga_beli || blurErrors.harga_beli ? 'border-rose-500 bg-rose-50/20' : ''
                                        }`}
                                    />
                                    {(editForm.errors.harga_beli || blurErrors.harga_beli) && (
                                        <p className="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                            <i className="ri-error-warning-line"></i> {editForm.errors.harga_beli || blurErrors.harga_beli}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Deskripsi
                                </label>
                                <textarea
                                    value={editForm.data.deskripsi}
                                    onChange={(e) => editForm.setData('deskripsi', e.target.value)}
                                    rows={3}
                                    className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                />
                            </div>

                            <div className="flex justify-end gap-2 pt-3 border-t border-gray-50">
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
                                    className="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg transition-all flex items-center gap-1"
                                >
                                    {editForm.processing ? 'Memperbarui...' : 'Simpan Perubahan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* DETAIL VIEW MODAL */}
            {isDetailModalOpen && selectedBahan && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
                    <div className="bg-white w-full max-w-md rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900">Detail Bahan Baku</h3>
                            <button
                                onClick={() => setIsDetailModalOpen(false)}
                                className="text-gray-400 hover:text-gray-600"
                            >
                                <i className="ri-close-line text-xl"></i>
                            </button>
                        </div>
                        <div className="p-6 space-y-4">
                            <div>
                                <h4 className="font-bold text-lg text-slate-900">{selectedBahan.nama_bahan}</h4>
                                <span className="inline-block text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full uppercase mt-1">
                                    {selectedBahan.kategori}
                                </span>
                            </div>

                            <hr className="border-gray-100" />

                            <div className="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Kode Bahan</p>
                                    <p className="font-mono font-bold text-slate-800">{selectedBahan.kode_bahan}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Harga Beli</p>
                                    <p className="font-extrabold text-slate-900">{formatRupiah(selectedBahan.harga_beli)}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Jumlah Stok</p>
                                    <p className="font-bold text-slate-800">{selectedBahan.stok?.jumlah_stok || 0} {selectedBahan.satuan}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Stok Minimum</p>
                                    <p className="font-bold text-slate-800">{selectedBahan.stok?.stok_minimum || 0} {selectedBahan.satuan}</p>
                                </div>
                            </div>

                            <div className="pt-2">
                                <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Deskripsi</p>
                                <div className="bg-slate-50 p-3 rounded-lg text-slate-600 text-xs leading-relaxed max-h-28 overflow-y-auto">
                                    {selectedBahan.deskripsi || <span className="text-gray-400 italic">Tidak ada deskripsi.</span>}
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
                                    Pastikan file Excel Anda menggunakan format kolom yang benar (Kode, Nama Bahan, Kategori, Satuan, Harga Beli, Deskripsi).
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
