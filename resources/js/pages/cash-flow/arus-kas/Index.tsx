import React, { useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { AuthenticatedLayout } from '@/layouts/AuthenticatedLayout';
import { PaginatedData } from '@/types';

type ArusKasItem = {
    id: number;
    kode_transaksi: string;
    jenis: 'pemasukan' | 'pengeluaran';
    kategori: string;
    deskripsi: string;
    jumlah: number;
    tanggal: string;
    created_at?: string;
    updated_at?: string;
};

type Props = {
    arusKas: PaginatedData<ArusKasItem>;
    totalPemasukan: number;
    totalPengeluaran: number;
    saldo: number;
    pemasukanBulan: number;
    pengeluaranBulan: number;
    kategoriList: string[];
    filters: {
        search?: string;
        jenis?: string;
        kategori?: string;
        tanggal_dari?: string;
        tanggal_sampai?: string;
    };
};

export default function ArusKasIndex({
    arusKas,
    totalPemasukan,
    totalPengeluaran,
    saldo,
    pemasukanBulan,
    pengeluaranBulan,
    kategoriList,
    filters
}: Props) {
    const [searchVal, setSearchVal] = useState(filters.search || '');
    const [jenisVal, setJenisVal] = useState(filters.jenis || '');
    const [kategoriVal, setKategoriVal] = useState(filters.kategori || '');
    const [dariVal, setDariVal] = useState(filters.tanggal_dari || '');
    const [sampaiVal, setSampaiVal] = useState(filters.tanggal_sampai || '');

    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [isDetailModalOpen, setIsDetailModalOpen] = useState(false);
    const [selectedTrans, setSelectedTrans] = useState<ArusKasItem | null>(null);

    const handleFilterSubmit = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        router.get('/arus-kas', {
            search: searchVal,
            jenis: jenisVal,
            kategori: kategoriVal,
            tanggal_dari: dariVal,
            tanggal_sampai: sampaiVal,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleResetFilters = () => {
        setSearchVal('');
        setJenisVal('');
        setKategoriVal('');
        setDariVal('');
        setSampaiVal('');
        router.get('/arus-kas', {}, { replace: true });
    };

    // Forms
    const createForm = useForm({
        jenis: 'pemasukan',
        kategori: '',
        deskripsi: '',
        jumlah: '',
        tanggal: new Date().toISOString().split('T')[0],
    });

    const editForm = useForm({
        jenis: 'pemasukan',
        kategori: '',
        deskripsi: '',
        jumlah: '',
        tanggal: '',
    });

    const [blurErrors, setBlurErrors] = useState<Record<string, string>>({});

    const validateField = (field: string, val: any) => {
        let err = '';
        if (field === 'kategori' && !val) {
            err = 'Kategori wajib diisi.';
        } else if (field === 'deskripsi' && !val) {
            err = 'Deskripsi wajib diisi.';
        } else if (field === 'jumlah') {
            if (val === '') err = 'Jumlah transaksi wajib diisi.';
            else if (isNaN(Number(val)) || Number(val) <= 0) err = 'Jumlah transaksi harus lebih besar dari 0.';
        } else if (field === 'tanggal' && !val) {
            err = 'Tanggal transaksi wajib diisi.';
        }
        setBlurErrors(prev => ({ ...prev, [field]: err }));
    };

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post('/arus-kas', {
            onSuccess: () => {
                setIsCreateModalOpen(false);
                createForm.reset();
                setBlurErrors({});
            },
        });
    };

    const handleEditClick = (item: ArusKasItem) => {
        setSelectedTrans(item);
        editForm.setData({
            jenis: item.jenis,
            kategori: item.kategori,
            deskripsi: item.deskripsi,
            jumlah: String(item.jumlah),
            tanggal: item.tanggal,
        });
        setIsEditModalOpen(true);
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedTrans) return;
        editForm.put(`/arus-kas/${selectedTrans.id}`, {
            onSuccess: () => {
                setIsEditModalOpen(false);
                editForm.reset();
                setBlurErrors({});
            },
        });
    };

    const handleDetailClick = (item: ArusKasItem) => {
        setSelectedTrans(item);
        setIsDetailModalOpen(true);
    };

    const handleDelete = (id: number) => {
        if (confirm('Apakah Anda yakin ingin menghapus catatan transaksi keuangan ini?')) {
            router.delete(`/arus-kas/${id}`);
        }
    };

    const formatRupiah = (num: number) => {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    };

    const formatDate = (dateStr: string) => {
        return new Date(dateStr).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    };

    return (
        <AuthenticatedLayout
            pageTitle="Arus Kas (Cash Flow)"
            breadcrumb="Catat pemasukan pesanan, pengeluaran logistik, upah karyawan, dan pantau saldo bersih"
            headerActions={
                <div className="flex flex-wrap items-center gap-2">
                    <a
                        href="/arus-kas/export/excel"
                        className="flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm"
                    >
                        <i className="ri-file-excel-2-line text-sm"></i> Excel
                    </a>
                    <a
                        href="/arus-kas/export/pdf"
                        className="flex items-center gap-1.5 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm"
                    >
                        <i className="ri-file-pdf-line text-sm"></i> PDF
                    </a>
                    <button
                        onClick={() => setIsCreateModalOpen(true)}
                        className="flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all shadow-lg hover:shadow-blue-500/20 cursor-pointer"
                    >
                        <i className="ri-add-line text-sm"></i> Catat Transaksi
                    </button>
                </div>
            }
        >
            <Head title="Arus Kas (Cash Flow)" />

            {/* Quick KPI Summary Cards */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                {/* Total Balance Card */}
                <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden flex flex-col justify-between min-h-[140px]">
                    <div>
                        <span className="text-xs text-gray-400 font-bold uppercase tracking-wider block mb-1">
                            Saldo Kas Keseluruhan
                        </span>
                        <h2 className={`text-2xl font-black ${saldo >= 0 ? 'text-emerald-600' : 'text-rose-600'}`}>
                            {formatRupiah(saldo)}
                        </h2>
                    </div>
                    <div className="flex items-center gap-1.5 text-xs font-bold mt-2">
                        {saldo >= 0 ? (
                            <span className="text-emerald-600 flex items-center gap-0.5">
                                <i className="ri-arrow-up-circle-fill text-base"></i> SURPLUS
                            </span>
                        ) : (
                            <span className="text-rose-600 flex items-center gap-0.5">
                                <i className="ri-arrow-down-circle-fill text-base"></i> DEFISIT
                            </span>
                        )}
                        <span className="text-gray-400 font-semibold">dari akumulasi total transaksi</span>
                    </div>
                    <div className="absolute right-4 bottom-4 w-12 h-12 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400">
                        <i className="ri-wallet-3-line text-2xl"></i>
                    </div>
                </div>

                {/* Monthly Income Card */}
                <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden flex flex-col justify-between min-h-[140px]">
                    <div>
                        <span className="text-xs text-gray-400 font-bold uppercase tracking-wider block mb-1">
                            Pemasukan Kas Bulan Ini
                        </span>
                        <h2 className="text-2xl font-black text-slate-900">
                            {formatRupiah(pemasukanBulan)}
                        </h2>
                    </div>
                    <div className="text-xs text-gray-400 font-semibold mt-2">
                        Total Pemasukan: <span className="font-bold text-emerald-600">{formatRupiah(totalPemasukan)}</span>
                    </div>
                    <div className="absolute right-4 bottom-4 w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500">
                        <i className="ri-arrow-left-down-line text-2xl"></i>
                    </div>
                </div>

                {/* Monthly Expense Card */}
                <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden flex flex-col justify-between min-h-[140px]">
                    <div>
                        <span className="text-xs text-gray-400 font-bold uppercase tracking-wider block mb-1">
                            Pengeluaran Kas Bulan Ini
                        </span>
                        <h2 className="text-2xl font-black text-slate-900">
                            {formatRupiah(pengeluaranBulan)}
                        </h2>
                    </div>
                    <div className="text-xs text-gray-400 font-semibold mt-2">
                        Total Pengeluaran: <span className="font-bold text-rose-600">{formatRupiah(totalPengeluaran)}</span>
                    </div>
                    <div className="absolute right-4 bottom-4 w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center text-rose-500">
                        <i className="ri-arrow-right-up-line text-2xl"></i>
                    </div>
                </div>
            </div>

            {/* Filter and Searching Panels */}
            <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6 space-y-4">
                <form onSubmit={handleFilterSubmit} className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    {/* Search Deskripsi */}
                    <div className="relative">
                        <i className="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            placeholder="Cari deskripsi, kode..."
                            value={searchVal}
                            onChange={(e) => setSearchVal(e.target.value)}
                            className="w-full bg-slate-50 border-0 rounded-lg pl-10 pr-4 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:bg-white border-1.5 border-gray-100 focus:border-blue-500 transition-all"
                        />
                    </div>

                    {/* Jenis Transaksi */}
                    <div>
                        <select
                            value={jenisVal}
                            onChange={(e) => setJenisVal(e.target.value)}
                            className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all font-medium text-slate-700"
                        >
                            <option value="">Semua Jenis</option>
                            <option value="pemasukan">Pemasukan (+)</option>
                            <option value="pengeluaran">Pengeluaran (-)</option>
                        </select>
                    </div>

                    {/* Kategori */}
                    <div>
                        <select
                            value={kategoriVal}
                            onChange={(e) => setKategoriVal(e.target.value)}
                            className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all font-medium text-slate-700"
                        >
                            <option value="">Semua Kategori</option>
                            {kategoriList.map((kat) => (
                                <option key={kat} value={kat}>{kat}</option>
                            ))}
                        </select>
                    </div>

                    {/* Tanggal Dari */}
                    <div>
                        <input
                            type="date"
                            value={dariVal}
                            onChange={(e) => setDariVal(e.target.value)}
                            className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all text-slate-600 font-medium"
                            title="Tanggal Mulai"
                        />
                    </div>

                    {/* Tanggal Sampai */}
                    <div>
                        <input
                            type="date"
                            value={sampaiVal}
                            onChange={(e) => setSampaiVal(e.target.value)}
                            className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all text-slate-600 font-medium"
                            title="Tanggal Selesai"
                        />
                    </div>
                </form>

                <div className="flex items-center justify-end gap-2 pt-2 border-t border-gray-50">
                    <button
                        onClick={handleResetFilters}
                        className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-lg transition-all cursor-pointer"
                    >
                        Reset Filter
                    </button>
                    <button
                        onClick={() => handleFilterSubmit()}
                        className="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg transition-all cursor-pointer"
                    >
                        Terapkan Filter
                    </button>
                </div>
            </div>

            {/* Table Transactions View */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="bg-slate-50 text-slate-400 text-[10px] font-bold uppercase tracking-wider border-b border-gray-100">
                                <th className="px-6 py-4">Kode Transaksi</th>
                                <th className="px-6 py-4">Tanggal</th>
                                <th className="px-6 py-4">Kategori</th>
                                <th className="px-6 py-4">Deskripsi</th>
                                <th className="px-6 py-4">Jenis</th>
                                <th className="px-6 py-4">Jumlah</th>
                                <th className="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-50">
                            {arusKas.data.length > 0 ? (
                                arusKas.data.map((item) => (
                                    <tr key={item.id} className="hover:bg-slate-50/50 transition-colors text-sm">
                                        <td className="px-6 py-4 font-mono text-xs font-bold text-gray-500">
                                            {item.kode_transaksi}
                                        </td>
                                        <td className="px-6 py-4 font-medium text-slate-700">
                                            {formatDate(item.tanggal)}
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                {item.kategori}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 max-w-xs truncate font-medium text-slate-900" title={item.deskripsi}>
                                            {item.deskripsi}
                                        </td>
                                        <td className="px-6 py-4">
                                            {item.jenis === 'pemasukan' ? (
                                                <span className="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                    PEMASUKAN
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700 border border-rose-100">
                                                    PENGELUARAN
                                                </span>
                                            )}
                                        </td>
                                        <td className={`px-6 py-4 font-black ${
                                            item.jenis === 'pemasukan' ? 'text-emerald-600' : 'text-rose-600'
                                        }`}>
                                            {item.jenis === 'pemasukan' ? '+' : '-'} {formatRupiah(item.jumlah)}
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-1.5">
                                                <button
                                                    onClick={() => handleDetailClick(item)}
                                                    className="p-1.5 text-slate-500 hover:text-blue-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                    title="Detail Transaksi"
                                                >
                                                    <i className="ri-eye-line text-base"></i>
                                                </button>
                                                <button
                                                    onClick={() => handleEditClick(item)}
                                                    className="p-1.5 text-slate-500 hover:text-amber-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                    title="Edit Transaksi"
                                                >
                                                    <i className="ri-edit-line text-base"></i>
                                                </button>
                                                <button
                                                    onClick={() => handleDelete(item.id)}
                                                    className="p-1.5 text-slate-500 hover:text-rose-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                    title="Hapus Transaksi"
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
                                        <p className="text-xs font-semibold">Belum ada data transaksi keuangan tercatat</p>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {arusKas.links && arusKas.links.length > 3 && (
                    <div className="flex justify-between items-center px-6 py-4 bg-slate-50 border-t border-gray-100">
                        <span className="text-xs text-gray-400 font-semibold">
                            Menampilkan {arusKas.from || 0} - {arusKas.to || 0} dari {arusKas.total || 0} data
                        </span>
                        <div className="flex gap-1">
                            {arusKas.links.map((link, idx) => {
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

            {/* CREATE TRANSACTION MODAL */}
            {isCreateModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
                    <div className="bg-white w-full max-w-md rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100 my-8">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-2">
                                <i className="ri-add-circle-line text-blue-600 text-lg"></i> Catat Transaksi Baru
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
                                    Jenis Transaksi <span className="text-rose-500">*</span>
                                </label>
                                <div className="grid grid-cols-2 gap-2">
                                    <button
                                        type="button"
                                        onClick={() => createForm.setData('jenis', 'pemasukan')}
                                        className={`py-2 px-3 rounded-lg text-xs font-bold border transition-all cursor-pointer ${
                                            createForm.data.jenis === 'pemasukan'
                                                ? 'bg-emerald-50 text-emerald-800 border-emerald-300 shadow-sm'
                                                : 'bg-slate-50 text-slate-600 border-gray-200 hover:bg-slate-100'
                                        }`}
                                    >
                                        Pemasukan (+)
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => createForm.setData('jenis', 'pengeluaran')}
                                        className={`py-2 px-3 rounded-lg text-xs font-bold border transition-all cursor-pointer ${
                                            createForm.data.jenis === 'pengeluaran'
                                                ? 'bg-rose-50 text-rose-800 border-rose-300 shadow-sm'
                                                : 'bg-slate-50 text-slate-600 border-gray-200 hover:bg-slate-100'
                                        }`}
                                    >
                                        Pengeluaran (-)
                                    </button>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Tanggal Transaksi <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        value={createForm.data.tanggal}
                                        onChange={(e) => createForm.setData('tanggal', e.target.value)}
                                        onBlur={(e) => validateField('tanggal', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            createForm.errors.tanggal || blurErrors.tanggal ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(createForm.errors.tanggal || blurErrors.tanggal) && (
                                        <p className="mt-1 text-xs text-rose-500">{createForm.errors.tanggal || blurErrors.tanggal}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Kategori Transaksi <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        placeholder="cth: Gaji, Bahan Baku, Penjualan..."
                                        list="kategori-list-create"
                                        value={createForm.data.kategori}
                                        onChange={(e) => createForm.setData('kategori', e.target.value)}
                                        onBlur={(e) => validateField('kategori', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            createForm.errors.kategori || blurErrors.kategori ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    <datalist id="kategori-list-create">
                                        {kategoriList.map(kat => <option key={kat} value={kat} />)}
                                    </datalist>
                                    {(createForm.errors.kategori || blurErrors.kategori) && (
                                        <p className="mt-1 text-xs text-rose-500">{createForm.errors.kategori || blurErrors.kategori}</p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Jumlah Nominal (Rp) <span className="text-rose-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    min={1}
                                    placeholder="cth: 500000"
                                    value={createForm.data.jumlah}
                                    onChange={(e) => createForm.setData('jumlah', e.target.value)}
                                    onBlur={(e) => validateField('jumlah', e.target.value)}
                                    className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                        createForm.errors.jumlah || blurErrors.jumlah ? 'border-rose-500' : ''
                                    }`}
                                />
                                {(createForm.errors.jumlah || blurErrors.jumlah) && (
                                    <p className="mt-1 text-xs text-rose-500">{createForm.errors.jumlah || blurErrors.jumlah}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Deskripsi Transaksi <span className="text-rose-500">*</span>
                                </label>
                                <textarea
                                    rows={3}
                                    placeholder="Tuliskan keterangan detail transaksi..."
                                    value={createForm.data.deskripsi}
                                    onChange={(e) => createForm.setData('deskripsi', e.target.value)}
                                    onBlur={(e) => validateField('deskripsi', e.target.value)}
                                    className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                        createForm.errors.deskripsi || blurErrors.deskripsi ? 'border-rose-500' : ''
                                    }`}
                                />
                                {(createForm.errors.deskripsi || blurErrors.deskripsi) && (
                                    <p className="mt-1 text-xs text-rose-500">{createForm.errors.deskripsi || blurErrors.deskripsi}</p>
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
                                    className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all cursor-pointer"
                                >
                                    {createForm.processing ? 'Menyimpan...' : 'Simpan Transaksi'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* EDIT TRANSACTION MODAL */}
            {isEditModalOpen && selectedTrans && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
                    <div className="bg-white w-full max-w-md rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100 my-8">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-2">
                                <i className="ri-edit-circle-line text-amber-500 text-lg"></i> Edit Catatan Transaksi
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
                                    Jenis Transaksi <span className="text-rose-500">*</span>
                                </label>
                                <div className="grid grid-cols-2 gap-2">
                                    <button
                                        type="button"
                                        onClick={() => editForm.setData('jenis', 'pemasukan')}
                                        className={`py-2 px-3 rounded-lg text-xs font-bold border transition-all cursor-pointer ${
                                            editForm.data.jenis === 'pemasukan'
                                                ? 'bg-emerald-50 text-emerald-800 border-emerald-300 shadow-sm'
                                                : 'bg-slate-50 text-slate-600 border-gray-200 hover:bg-slate-100'
                                        }`}
                                    >
                                        Pemasukan (+)
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => editForm.setData('jenis', 'pengeluaran')}
                                        className={`py-2 px-3 rounded-lg text-xs font-bold border transition-all cursor-pointer ${
                                            editForm.data.jenis === 'pengeluaran'
                                                ? 'bg-rose-50 text-rose-800 border-rose-300 shadow-sm'
                                                : 'bg-slate-50 text-slate-600 border-gray-200 hover:bg-slate-100'
                                        }`}
                                    >
                                        Pengeluaran (-)
                                    </button>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Tanggal Transaksi <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        value={editForm.data.tanggal}
                                        onChange={(e) => editForm.setData('tanggal', e.target.value)}
                                        onBlur={(e) => validateField('tanggal', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            editForm.errors.tanggal || blurErrors.tanggal ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(editForm.errors.tanggal || blurErrors.tanggal) && (
                                        <p className="mt-1 text-xs text-rose-500">{editForm.errors.tanggal || blurErrors.tanggal}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Kategori Transaksi <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        list="kategori-list-edit"
                                        value={editForm.data.kategori}
                                        onChange={(e) => editForm.setData('kategori', e.target.value)}
                                        onBlur={(e) => validateField('kategori', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            editForm.errors.kategori || blurErrors.kategori ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    <datalist id="kategori-list-edit">
                                        {kategoriList.map(kat => <option key={kat} value={kat} />)}
                                    </datalist>
                                    {(editForm.errors.kategori || blurErrors.kategori) && (
                                        <p className="mt-1 text-xs text-rose-500">{editForm.errors.kategori || blurErrors.kategori}</p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Jumlah Nominal (Rp) <span className="text-rose-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    min={1}
                                    value={editForm.data.jumlah}
                                    onChange={(e) => editForm.setData('jumlah', e.target.value)}
                                    onBlur={(e) => validateField('jumlah', e.target.value)}
                                    className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                        editForm.errors.jumlah || blurErrors.jumlah ? 'border-rose-500' : ''
                                    }`}
                                />
                                {(editForm.errors.jumlah || blurErrors.jumlah) && (
                                    <p className="mt-1 text-xs text-rose-500">{editForm.errors.jumlah || blurErrors.jumlah}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Deskripsi Transaksi <span className="text-rose-500">*</span>
                                </label>
                                <textarea
                                    rows={3}
                                    value={editForm.data.deskripsi}
                                    onChange={(e) => editForm.setData('deskripsi', e.target.value)}
                                    onBlur={(e) => validateField('deskripsi', e.target.value)}
                                    className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                        editForm.errors.deskripsi || blurErrors.deskripsi ? 'border-rose-500' : ''
                                    }`}
                                />
                                {(editForm.errors.deskripsi || blurErrors.deskripsi) && (
                                    <p className="mt-1 text-xs text-rose-500">{editForm.errors.deskripsi || blurErrors.deskripsi}</p>
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
                                    className="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg transition-all cursor-pointer"
                                >
                                    {editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* DETAIL TRANSACTION MODAL */}
            {isDetailModalOpen && selectedTrans && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
                    <div className="bg-white w-full max-w-md rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-2">
                                <i className="ri-information-line text-blue-600 text-lg"></i> Detail Transaksi Keuangan
                            </h3>
                            <button
                                onClick={() => setIsDetailModalOpen(false)}
                                className="text-gray-400 hover:text-gray-600"
                            >
                                <i className="ri-close-line text-xl"></i>
                            </button>
                        </div>
                        <div className="p-6 space-y-4">
                            <div className="flex justify-between items-start">
                                <div>
                                    <span className="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Kode Transaksi</span>
                                    <span className="font-mono text-sm font-bold text-slate-900">{selectedTrans.kode_transaksi}</span>
                                </div>
                                <div className="text-right">
                                    <span className="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Tanggal</span>
                                    <span className="text-sm font-bold text-slate-900">{formatDate(selectedTrans.tanggal)}</span>
                                </div>
                            </div>

                            <hr className="border-gray-50" />

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <span className="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Jenis Kas</span>
                                    <span className={`inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[10px] font-extrabold mt-1 border ${
                                        selectedTrans.jenis === 'pemasukan'
                                            ? 'bg-emerald-50 text-emerald-800 border-emerald-100'
                                            : 'bg-rose-50 text-rose-800 border-rose-100'
                                    }`}>
                                        {selectedTrans.jenis.toUpperCase()}
                                    </span>
                                </div>
                                <div>
                                    <span className="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Kategori</span>
                                    <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold mt-1 bg-slate-100 text-slate-700 border border-slate-200">
                                        {selectedTrans.kategori}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <span className="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Nominal Transaksi</span>
                                <span className={`text-2xl font-black block mt-1 ${
                                    selectedTrans.jenis === 'pemasukan' ? 'text-emerald-600' : 'text-rose-600'
                                }`}>
                                    {selectedTrans.jenis === 'pemasukan' ? '+' : '-'} {formatRupiah(selectedTrans.jumlah)}
                                </span>
                            </div>

                            <div className="bg-slate-50 p-4 rounded-xl border border-gray-100">
                                <span className="text-[10px] text-gray-400 font-bold uppercase tracking-wider block mb-1">Keterangan / Deskripsi</span>
                                <p className="text-xs font-semibold text-slate-800 leading-relaxed whitespace-pre-wrap">
                                    {selectedTrans.deskripsi}
                                </p>
                            </div>

                            <div className="flex justify-end pt-3 border-t border-gray-50">
                                <button
                                    onClick={() => setIsDetailModalOpen(false)}
                                    className="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg transition-all"
                                >
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
