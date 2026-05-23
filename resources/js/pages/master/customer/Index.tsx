import React, { useState, useEffect } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { AuthenticatedLayout } from '@/layouts/AuthenticatedLayout';
import { Customer, PaginatedData } from '@/types';

type Props = {
    customer: PaginatedData<Customer & { pesanan_count?: number }>;
    kotaList: string[];
    filters: {
        search?: string;
        kota?: string;
    };
};

export default function CustomerIndex({ customer, kotaList, filters }: Props) {
    const [searchVal, setSearchVal] = useState(filters.search || '');
    const [kotaVal, setKotaVal] = useState(filters.kota || '');
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [isDetailModalOpen, setIsDetailModalOpen] = useState(false);
    const [isImportModalOpen, setIsImportModalOpen] = useState(false);
    const [selectedCustomer, setSelectedCustomer] = useState<Customer | null>(null);

    // Filter submit handler
    const handleFilterSubmit = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        router.get('/customer', {
            search: searchVal,
            kota: kotaVal,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    // Trigger filter when city changes
    useEffect(() => {
        handleFilterSubmit();
    }, [kotaVal]);

    // Create Form Hook
    const createForm = useForm({
        nama: '',
        no_hp: '',
        email: '',
        alamat: '',
        kota: '',
        provinsi: '',
        kode_pos: '',
        deskripsi: '',
    });

    // Edit Form Hook
    const editForm = useForm({
        nama: '',
        no_hp: '',
        email: '',
        alamat: '',
        kota: '',
        provinsi: '',
        kode_pos: '',
        deskripsi: '',
    });

    // Import Form Hook
    const importForm = useForm({
        file: null as File | null,
    });

    const [blurErrors, setBlurErrors] = useState<Record<string, string>>({});

    const validateField = (field: string, value: any) => {
        let err = '';
        if (field === 'nama' && !value) {
            err = 'Nama pelanggan wajib diisi.';
        }
        setBlurErrors(prev => ({ ...prev, [field]: err }));
    };

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post('/customer', {
            onSuccess: () => {
                setIsCreateModalOpen(false);
                createForm.reset();
                setBlurErrors({});
            },
        });
    };

    const handleEditClick = (c: Customer) => {
        setSelectedCustomer(c);
        editForm.setData({
            nama: c.nama,
            no_hp: c.no_hp || '',
            email: c.email || '',
            alamat: c.alamat || '',
            kota: c.kota || '',
            provinsi: c.provinsi || '',
            kode_pos: c.kode_pos || '',
            deskripsi: c.deskripsi || '',
        });
        setIsEditModalOpen(true);
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedCustomer) return;
        editForm.put(`/customer/${selectedCustomer.id}`, {
            onSuccess: () => {
                setIsEditModalOpen(false);
                editForm.reset();
                setBlurErrors({});
            },
        });
    };

    const handleDelete = (c: Customer) => {
        if (confirm(`Apakah Anda yakin ingin menghapus pelanggan ${c.nama}?`)) {
            router.delete(`/customer/${c.id}`);
        }
    };

    const handleImportSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        importForm.post('/customer/import', {
            onSuccess: () => {
                setIsImportModalOpen(false);
                importForm.reset();
            },
        });
    };

    const fetchDetail = async (c: Customer) => {
        try {
            const res = await fetch(`/customer/${c.id}`);
            const data = await res.json();
            setSelectedCustomer(data);
            setIsDetailModalOpen(true);
        } catch (err) {
            console.error('Gagal mengambil data detail customer.', err);
        }
    };

    return (
        <AuthenticatedLayout
            pageTitle="Data Master Customer"
            breadcrumb="Kelola detail pelanggan, alamat pengiriman, dan riwayat pesanan Provillo"
            headerActions={
                <div className="flex flex-wrap items-center gap-2">
                    <a
                        href="/customer/export/excel"
                        className="flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm hover:shadow"
                    >
                        <i className="ri-file-excel-2-line text-sm"></i> Export Excel
                    </a>
                    <a
                        href="/customer/export/pdf"
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
                        <i className="ri-add-line text-sm"></i> Tambah Customer
                    </button>
                </div>
            }
        >
            <Head title="Data Customer" />

            {/* Filter Section */}
            <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <form onSubmit={handleFilterSubmit} className="flex-1 flex gap-2">
                    <div className="relative flex-1 max-w-sm">
                        <i className="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            placeholder="Cari kode, nama atau no handphone..."
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
                    <span className="text-xs text-gray-400 font-bold uppercase">Kota:</span>
                    <select
                        value={kotaVal}
                        onChange={(e) => setKotaVal(e.target.value)}
                        className="bg-slate-50 border-1.5 border-gray-100 rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:border-blue-500"
                    >
                        <option value="">Semua Kota</option>
                        {kotaList.map((kota) => (
                            <option key={kota} value={kota}>{kota}</option>
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
                                <th className="px-6 py-4">Kode Customer</th>
                                <th className="px-6 py-4">Nama Pelanggan</th>
                                <th className="px-6 py-4">Kontak</th>
                                <th className="px-6 py-4">Kota / Provinsi</th>
                                <th className="px-6 py-4">Jumlah Pesanan</th>
                                <th className="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-50">
                            {customer.data.length > 0 ? (
                                customer.data.map((c) => (
                                    <tr key={c.id} className="hover:bg-slate-50/50 transition-colors text-sm">
                                        <td className="px-6 py-4 font-mono text-xs font-bold text-gray-500">
                                            {c.kode_customer}
                                        </td>
                                        <td className="px-6 py-4 font-bold text-slate-900">
                                            {c.nama}
                                        </td>
                                        <td className="px-6 py-4">
                                            <p className="font-semibold text-slate-800">{c.no_hp || '-'}</p>
                                            <p className="text-xs text-gray-400">{c.email || '-'}</p>
                                        </td>
                                        <td className="px-6 py-4">
                                            <p className="font-semibold text-slate-800">{c.kota || '-'}</p>
                                            <p className="text-xs text-gray-400">{c.provinsi || '-'}</p>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="inline-block text-xs font-bold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700">
                                                {c.pesanan_count || 0} order
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-1.5">
                                                <button
                                                    onClick={() => fetchDetail(c)}
                                                    className="p-1.5 text-slate-500 hover:text-blue-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                    title="Detail"
                                                >
                                                    <i className="ri-eye-line text-base"></i>
                                                </button>
                                                <button
                                                    onClick={() => handleEditClick(c)}
                                                    className="p-1.5 text-slate-500 hover:text-amber-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                    title="Edit"
                                                >
                                                    <i className="ri-edit-line text-base"></i>
                                                </button>
                                                <button
                                                    onClick={() => handleDelete(c)}
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
                                        <p className="text-xs font-semibold">Belum ada data pelanggan tersedia</p>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {customer.links && customer.links.length > 3 && (
                    <div className="flex justify-between items-center px-6 py-4 bg-slate-50 border-t border-gray-100">
                        <span className="text-xs text-gray-400 font-semibold">
                            Menampilkan {customer.from || 0} - {customer.to || 0} dari {customer.total || 0} data
                        </span>
                        <div className="flex gap-1">
                            {customer.links.map((link, idx) => {
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
                                <i className="ri-add-circle-line text-blue-600 text-lg"></i> Tambah Pelanggan Baru
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
                                    Nama Lengkap / Instansi <span className="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={createForm.data.nama}
                                    onChange={(e) => createForm.setData('nama', e.target.value)}
                                    onBlur={(e) => validateField('nama', e.target.value)}
                                    placeholder="Masukkan nama pelanggan..."
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
                                        No Handphone
                                    </label>
                                    <input
                                        type="text"
                                        value={createForm.data.no_hp}
                                        onChange={(e) => createForm.setData('no_hp', e.target.value)}
                                        placeholder="08xxxxxxxxxx"
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
                                        placeholder="customer@example.com"
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-3 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Kota
                                    </label>
                                    <input
                                        type="text"
                                        value={createForm.data.kota}
                                        onChange={(e) => createForm.setData('kota', e.target.value)}
                                        placeholder="Kota"
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Provinsi
                                    </label>
                                    <input
                                        type="text"
                                        value={createForm.data.provinsi}
                                        onChange={(e) => createForm.setData('provinsi', e.target.value)}
                                        placeholder="Provinsi"
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Kode Pos
                                    </label>
                                    <input
                                        type="text"
                                        value={createForm.data.kode_pos}
                                        onChange={(e) => createForm.setData('kode_pos', e.target.value)}
                                        placeholder="Kode Pos"
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Alamat Pengiriman
                                </label>
                                <textarea
                                    value={createForm.data.alamat}
                                    onChange={(e) => createForm.setData('alamat', e.target.value)}
                                    placeholder="Masukkan detail alamat pengiriman pelanggan..."
                                    rows={2}
                                    className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Keterangan / Deskripsi
                                </label>
                                <textarea
                                    value={createForm.data.deskripsi}
                                    onChange={(e) => createForm.setData('deskripsi', e.target.value)}
                                    placeholder="Masukkan detail khusus atau catatan untuk customer ini..."
                                    rows={2}
                                    className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                />
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
                                    {createForm.processing ? 'Menyimpan...' : 'Simpan Customer'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* EDIT MODAL */}
            {isEditModalOpen && selectedCustomer && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
                    <div className="bg-white w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100 my-8">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-2">
                                <i className="ri-edit-circle-line text-amber-500 text-lg"></i> Edit Pelanggan
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
                                    Nama Lengkap / Instansi <span className="text-rose-500">*</span>
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
                                        No Handphone
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

                            <div className="grid grid-cols-3 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Kota
                                    </label>
                                    <input
                                        type="text"
                                        value={editForm.data.kota}
                                        onChange={(e) => editForm.setData('kota', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Provinsi
                                    </label>
                                    <input
                                        type="text"
                                        value={editForm.data.provinsi}
                                        onChange={(e) => editForm.setData('provinsi', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Kode Pos
                                    </label>
                                    <input
                                        type="text"
                                        value={editForm.data.kode_pos}
                                        onChange={(e) => editForm.setData('kode_pos', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Alamat Pengiriman
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
                                    Keterangan / Deskripsi
                                </label>
                                <textarea
                                    value={editForm.data.deskripsi}
                                    onChange={(e) => editForm.setData('deskripsi', e.target.value)}
                                    rows={2}
                                    className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                />
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

            {/* DETAIL VIEW MODAL (WITH ORDER HISTORY!) */}
            {isDetailModalOpen && selectedCustomer && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
                    <div className="bg-white w-full max-w-xl rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900">Detail Pelanggan</h3>
                            <button
                                onClick={() => setIsDetailModalOpen(false)}
                                className="text-gray-400 hover:text-gray-600"
                            >
                                <i className="ri-close-line text-xl"></i>
                            </button>
                        </div>
                        <div className="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                            <div>
                                <h4 className="font-bold text-lg text-slate-900">{selectedCustomer.nama}</h4>
                                <span className="inline-block text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full uppercase mt-1">
                                    {selectedCustomer.kode_customer}
                                </span>
                            </div>

                            <hr className="border-gray-100" />

                            <div className="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">No Handphone</p>
                                    <p className="font-bold text-slate-800">{selectedCustomer.no_hp || '-'}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Email</p>
                                    <p className="font-bold text-slate-800">{selectedCustomer.email || '-'}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Alamat Pengiriman</p>
                                    <p className="font-medium text-slate-700">{selectedCustomer.alamat || '-'}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Area</p>
                                    <p className="font-bold text-slate-800">
                                        {selectedCustomer.kota ? `${selectedCustomer.kota}, ` : ''}
                                        {selectedCustomer.provinsi || ''}
                                        {selectedCustomer.kode_pos ? ` (${selectedCustomer.kode_pos})` : ''}
                                        {!selectedCustomer.kota && !selectedCustomer.provinsi && '-'}
                                    </p>
                                </div>
                            </div>

                            {selectedCustomer.deskripsi && (
                                <div className="pt-2">
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Catatan</p>
                                    <div className="bg-slate-50 p-3 rounded-lg text-slate-600 text-xs leading-relaxed max-h-24 overflow-y-auto">
                                        {selectedCustomer.deskripsi}
                                    </div>
                                </div>
                            )}

                            {/* Order History (Latest 5 orders) */}
                            <div className="pt-2">
                                <p className="text-xs font-bold text-slate-800 mb-3 flex items-center gap-1">
                                    <i className="ri-history-line text-blue-600"></i> Riwayat Pesanan Terbaru
                                </p>
                                {selectedCustomer.pesanan && selectedCustomer.pesanan.length > 0 ? (
                                    <div className="border border-gray-100 rounded-xl overflow-hidden shadow-sm">
                                        <table className="w-full text-left text-xs border-collapse">
                                            <thead>
                                                <tr className="bg-slate-50 text-slate-400 font-bold uppercase border-b border-gray-50">
                                                    <th className="px-4 py-2">No. Pesanan</th>
                                                    <th className="px-4 py-2">Tanggal</th>
                                                    <th className="px-4 py-2">Total Harga</th>
                                                    <th className="px-4 py-2">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-gray-50 text-slate-700">
                                                {selectedCustomer.pesanan.map((o: any) => (
                                                    <tr key={o.id} className="hover:bg-slate-50/50">
                                                        <td className="px-4 py-2 font-mono font-bold text-blue-600">{o.nomor_pesanan}</td>
                                                        <td className="px-4 py-2">{o.tanggal_pesanan}</td>
                                                        <td className="px-4 py-2 font-extrabold">
                                                            {'Rp ' + new Intl.NumberFormat('id-ID').format(o.total_harga)}
                                                        </td>
                                                        <td className="px-4 py-2">
                                                            <span className={`inline-block text-[10px] font-bold px-2 py-0.5 rounded-full capitalize ${
                                                                o.status === 'pending' ? 'bg-amber-100 text-amber-800' :
                                                                o.status === 'diproses' ? 'bg-blue-100 text-blue-800' :
                                                                o.status === 'produksi' ? 'bg-purple-100 text-purple-800' :
                                                                'bg-emerald-100 text-emerald-800'
                                                            }`}>
                                                                {o.status}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <div className="bg-slate-50 text-center py-6 text-gray-400 rounded-xl border border-dashed border-gray-200">
                                        <p className="text-xs font-semibold">Customer ini belum memiliki riwayat pesanan</p>
                                    </div>
                                )}
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
                                    Format kolom file Excel: (Kode Customer, Nama Pelanggan, No HP, Email, Alamat, Kota, Provinsi, Kode Pos, Deskripsi).
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
