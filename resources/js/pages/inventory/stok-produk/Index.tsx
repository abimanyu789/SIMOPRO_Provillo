import React, { useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { AuthenticatedLayout } from '@/layouts/AuthenticatedLayout';
import { PaginatedData, Produk } from '@/types';

type StokProdukItem = {
    id: number;
    produk_id: number;
    jumlah_stok: number;
    stok_minimum: number;
    created_at?: string;
    updated_at?: string;
    produk?: Produk;
};

type Props = {
    stokProduk: PaginatedData<StokProdukItem>;
    kategoriList: string[];
    produkTanpaStok: Produk[];
    filters: {
        search?: string;
        status?: string;
        kategori?: string;
    };
};

export default function StokProdukIndex({ stokProduk, kategoriList, produkTanpaStok, filters }: Props) {
    const [searchVal, setSearchVal] = useState(filters.search || '');
    const [statusVal, setStatusVal] = useState(filters.status || '');
    const [kategoriVal, setKategoriVal] = useState(filters.kategori || '');

    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [isImportModalOpen, setIsImportModalOpen] = useState(false);
    const [selectedStok, setSelectedStok] = useState<StokProdukItem | null>(null);

    const handleFilterSubmit = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        router.get('/stok-produk', {
            search: searchVal,
            status: statusVal,
            kategori: kategoriVal,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    // Forms
    const createForm = useForm({
        produk_id: '',
        jumlah_stok: '0',
        stok_minimum: '10',
    });

    const editForm = useForm({
        jumlah_stok: '',
        stok_minimum: '',
    });

    const importForm = useForm({
        file: null as File | null,
    });

    const [blurErrors, setBlurErrors] = useState<Record<string, string>>({});

    const validateField = (field: string, val: any) => {
        let err = '';
        if (field === 'produk_id' && !val) {
            err = 'Produk wajib dipilih.';
        } else if (field === 'jumlah_stok') {
            if (val === '') err = 'Jumlah stok wajib diisi.';
            else if (isNaN(Number(val)) || Number(val) < 0) err = 'Jumlah stok minimal 0.';
        } else if (field === 'stok_minimum') {
            if (val === '') err = 'Stok minimum wajib diisi.';
            else if (isNaN(Number(val)) || Number(val) < 0) err = 'Stok minimum minimal 0.';
        }
        setBlurErrors(prev => ({ ...prev, [field]: err }));
    };

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post('/stok-produk', {
            onSuccess: () => {
                setIsCreateModalOpen(false);
                createForm.reset();
                setBlurErrors({});
            },
        });
    };

    const handleEditClick = (item: StokProdukItem) => {
        setSelectedStok(item);
        editForm.setData({
            jumlah_stok: String(item.jumlah_stok),
            stok_minimum: String(item.stok_minimum),
        });
        setIsEditModalOpen(true);
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedStok) return;
        editForm.put(`/stok-produk/${selectedStok.id}`, {
            onSuccess: () => {
                setIsEditModalOpen(false);
                editForm.reset();
                setBlurErrors({});
            },
        });
    };

    const handleImportSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!importForm.data.file) {
            alert('Silakan pilih file Excel terlebih dahulu.');
            return;
        }
        importForm.post('/stok-produk/import', {
            onSuccess: () => {
                setIsImportModalOpen(false);
                importForm.reset();
            },
        });
    };

    const handleDelete = (id: number) => {
        if (confirm('Apakah Anda yakin ingin menghapus konfigurasi stok produk ini?')) {
            router.delete(`/stok-produk/${id}`);
        }
    };

    const getStatusLabel = (stok: number, min: number) => {
        if (stok <= 0) {
            return (
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border bg-rose-100 text-rose-800 border-rose-200">
                    HABIS
                </span>
            );
        } else if (stok < min) {
            return (
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border bg-amber-100 text-amber-800 border-amber-200">
                    MENIPIS
                </span>
            );
        } else {
            return (
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border bg-emerald-100 text-emerald-800 border-emerald-200">
                    TERSEDIA
                </span>
            );
        }
    };

    const formatRupiah = (num: number) => {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    };

    return (
        <AuthenticatedLayout
            pageTitle="Stok Produk Jadi"
            breadcrumb="Kelola inventaris akhir sepatu, status ketersediaan stok minimum, dan impor data"
            headerActions={
                <div className="flex flex-wrap items-center gap-2">
                    <button
                        onClick={() => setIsImportModalOpen(true)}
                        className="flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition-all shadow-sm cursor-pointer"
                    >
                        <i className="ri-upload-2-line text-sm"></i> Import Excel
                    </button>
                    <a
                        href="/stok-produk/export/excel"
                        className="flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm"
                    >
                        <i className="ri-file-excel-2-line text-sm"></i> Excel
                    </a>
                    <a
                        href="/stok-produk/export/pdf"
                        className="flex items-center gap-1.5 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm"
                    >
                        <i className="ri-file-pdf-line text-sm"></i> PDF
                    </a>
                    {produkTanpaStok.length > 0 && (
                        <button
                            onClick={() => setIsCreateModalOpen(true)}
                            className="flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all shadow-lg hover:shadow-blue-500/20 cursor-pointer"
                        >
                            <i className="ri-add-line text-sm"></i> Set Stok Baru
                        </button>
                    )}
                </div>
            }
        >
            <Head title="Stok Produk Jadi" />

            {/* Filters */}
            <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <form onSubmit={handleFilterSubmit} className="flex-1 flex gap-2">
                    <div className="relative flex-1 max-w-sm">
                        <i className="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            placeholder="Cari kode atau nama produk sepatu..."
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

                <div className="flex flex-wrap items-center gap-3">
                    <div className="flex items-center gap-2">
                        <span className="text-xs text-gray-400 font-bold uppercase">Kategori:</span>
                        <select
                            value={kategoriVal}
                            onChange={(e) => {
                                setKategoriVal(e.target.value);
                                router.get('/stok-produk', { search: searchVal, status: statusVal, kategori: e.target.value }, { preserveState: true });
                            }}
                            className="bg-slate-50 border-1.5 border-gray-100 rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:border-blue-500"
                        >
                            <option value="">Semua Kategori</option>
                            {kategoriList.map((kat) => (
                                <option key={kat} value={kat}>{kat}</option>
                            ))}
                        </select>
                    </div>

                    <div className="flex items-center gap-2">
                        <span className="text-xs text-gray-400 font-bold uppercase">Status:</span>
                        <select
                            value={statusVal}
                            onChange={(e) => {
                                setStatusVal(e.target.value);
                                router.get('/stok-produk', { search: searchVal, status: e.target.value, kategori: kategoriVal }, { preserveState: true });
                            }}
                            className="bg-slate-50 border-1.5 border-gray-100 rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:border-blue-500"
                        >
                            <option value="">Semua Status</option>
                            <option value="tersedia">TERSEDIA</option>
                            <option value="menipis">MENIPIS</option>
                            <option value="habis">HABIS</option>
                        </select>
                    </div>
                </div>
            </div>

            {/* Table View */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="bg-slate-50 text-slate-400 text-[10px] font-bold uppercase tracking-wider border-b border-gray-100">
                                <th className="px-6 py-4">Kode Produk</th>
                                <th className="px-6 py-4">Nama Sepatu</th>
                                <th className="px-6 py-4">Kategori</th>
                                <th className="px-6 py-4">Harga Jual</th>
                                <th className="px-6 py-4">Stok Saat Ini</th>
                                <th className="px-6 py-4">Stok Minimum</th>
                                <th className="px-6 py-4">Status</th>
                                <th className="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-50">
                            {stokProduk.data.length > 0 ? (
                                stokProduk.data.map((item) => (
                                    <tr key={item.id} className="hover:bg-slate-50/50 transition-colors text-sm">
                                        <td className="px-6 py-4 font-mono text-xs font-bold text-gray-500">
                                            {item.produk?.kode_produk}
                                        </td>
                                        <td className="px-6 py-4 font-bold text-slate-900">
                                            {item.produk?.nama_produk}
                                        </td>
                                        <td className="px-6 py-4 text-slate-500 font-medium">
                                            {item.produk?.kategori}
                                        </td>
                                        <td className="px-6 py-4 font-semibold text-slate-800">
                                            {item.produk ? formatRupiah(item.produk.harga_jual) : '-'}
                                        </td>
                                        <td className="px-6 py-4 font-extrabold text-slate-900">
                                            {item.jumlah_stok} pasang
                                        </td>
                                        <td className="px-6 py-4 font-semibold text-slate-600">
                                            {item.stok_minimum} pasang
                                        </td>
                                        <td className="px-6 py-4">
                                            {getStatusLabel(item.jumlah_stok, item.stok_minimum)}
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-1.5">
                                                <button
                                                    onClick={() => handleEditClick(item)}
                                                    className="p-1.5 text-slate-500 hover:text-amber-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                    title="Edit Parameter Stok"
                                                >
                                                    <i className="ri-edit-line text-base"></i>
                                                </button>
                                                <button
                                                    onClick={() => handleDelete(item.id)}
                                                    className="p-1.5 text-slate-500 hover:text-rose-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                    title="Hapus Pengaturan Stok"
                                                >
                                                    <i className="ri-delete-bin-line text-base"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={8} className="px-6 py-12 text-center text-gray-400">
                                        <i className="ri-inbox-line text-4xl block mb-2 opacity-50"></i>
                                        <p className="text-xs font-semibold">Belum ada data stok produk terdaftar</p>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {stokProduk.links && stokProduk.links.length > 3 && (
                    <div className="flex justify-between items-center px-6 py-4 bg-slate-50 border-t border-gray-100">
                        <span className="text-xs text-gray-400 font-semibold">
                            Menampilkan {stokProduk.from || 0} - {stokProduk.to || 0} dari {stokProduk.total || 0} data
                        </span>
                        <div className="flex gap-1">
                            {stokProduk.links.map((link, idx) => {
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
                    <div className="bg-white w-full max-w-md rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100 my-8">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-2">
                                <i className="ri-add-circle-line text-blue-600 text-lg"></i> Set Stok Produk Baru
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
                                    Pilih Produk <span className="text-rose-500">*</span>
                                </label>
                                <select
                                    value={createForm.data.produk_id}
                                    onChange={(e) => createForm.setData('produk_id', e.target.value)}
                                    onBlur={(e) => validateField('produk_id', e.target.value)}
                                    className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                        createForm.errors.produk_id || blurErrors.produk_id ? 'border-rose-500' : ''
                                    }`}
                                >
                                    <option value="">-- Pilih Produk Sepatu --</option>
                                    {produkTanpaStok.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_produk} ({p.kode_produk})</option>
                                    ))}
                                </select>
                                {(createForm.errors.produk_id || blurErrors.produk_id) && (
                                    <p className="mt-1 text-xs text-rose-500">{createForm.errors.produk_id || blurErrors.produk_id}</p>
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Stok Awal <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        min={0}
                                        value={createForm.data.jumlah_stok}
                                        onChange={(e) => createForm.setData('jumlah_stok', e.target.value)}
                                        onBlur={(e) => validateField('jumlah_stok', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            createForm.errors.jumlah_stok || blurErrors.jumlah_stok ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(createForm.errors.jumlah_stok || blurErrors.jumlah_stok) && (
                                        <p className="mt-1 text-xs text-rose-500">{createForm.errors.jumlah_stok || blurErrors.jumlah_stok}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Batas Stok Minimum <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        min={0}
                                        value={createForm.data.stok_minimum}
                                        onChange={(e) => createForm.setData('stok_minimum', e.target.value)}
                                        onBlur={(e) => validateField('stok_minimum', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            createForm.errors.stok_minimum || blurErrors.stok_minimum ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(createForm.errors.stok_minimum || blurErrors.stok_minimum) && (
                                        <p className="mt-1 text-xs text-rose-500">{createForm.errors.stok_minimum || blurErrors.stok_minimum}</p>
                                    )}
                                </div>
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
                                    className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all cursor-pointer"
                                >
                                    {createForm.processing ? 'Menyimpan...' : 'Simpan Set Stok'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* EDIT PARAMETER MODAL */}
            {isEditModalOpen && selectedStok && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
                    <div className="bg-white w-full max-w-md rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100 my-8">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-2">
                                <i className="ri-edit-circle-line text-amber-505 text-lg"></i> Edit Parameter Stok
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
                                <label className="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nama Produk Sepatu</label>
                                <p className="font-bold text-slate-900 text-sm">{selectedStok.produk?.nama_produk}</p>
                                <p className="font-mono text-xs text-gray-400 mt-0.5">{selectedStok.produk?.kode_produk}</p>
                            </div>

                            <hr className="border-gray-100 animate-fade-in" />

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Stok Saat Ini <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        min={0}
                                        value={editForm.data.jumlah_stok}
                                        onChange={(e) => editForm.setData('jumlah_stok', e.target.value)}
                                        onBlur={(e) => validateField('jumlah_stok', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            editForm.errors.jumlah_stok || blurErrors.jumlah_stok ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(editForm.errors.jumlah_stok || blurErrors.jumlah_stok) && (
                                        <p className="mt-1 text-xs text-rose-500">{editForm.errors.jumlah_stok || blurErrors.jumlah_stok}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Batas Stok Minimum <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        min={0}
                                        value={editForm.data.stok_minimum}
                                        onChange={(e) => editForm.setData('stok_minimum', e.target.value)}
                                        onBlur={(e) => validateField('stok_minimum', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            editForm.errors.stok_minimum || blurErrors.stok_minimum ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(editForm.errors.stok_minimum || blurErrors.stok_minimum) && (
                                        <p className="mt-1 text-xs text-rose-500">{editForm.errors.stok_minimum || blurErrors.stok_minimum}</p>
                                    )}
                                </div>
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

            {/* IMPORT MODAL */}
            {isImportModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
                    <div className="bg-white w-full max-w-md rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100 my-8">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-2">
                                <i className="ri-file-upload-line text-blue-600 text-lg"></i> Impor Stok Produk via Excel
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
                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Pilih Berkas Excel (.xlsx, .xls) <span className="text-rose-500">*</span>
                                </label>
                                <input
                                    type="file"
                                    accept=".xlsx, .xls"
                                    onChange={(e) => importForm.setData('file', e.target.files ? e.target.files[0] : null)}
                                    className="w-full bg-slate-50 border-1.5 border-dashed border-gray-300 rounded-lg p-6 text-xs font-medium text-gray-600 focus:outline-none focus:border-blue-500 focus:bg-white transition-all text-center"
                                />
                            </div>

                            <div className="flex justify-end gap-2 pt-3 border-t border-gray-100">
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
                                    className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all flex items-center gap-1 cursor-pointer"
                                >
                                    {importForm.processing ? 'Mengimpor...' : 'Mulai Impor'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
