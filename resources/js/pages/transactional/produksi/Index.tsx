import React, { useState, useEffect } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { AuthenticatedLayout } from '@/layouts/AuthenticatedLayout';
import { Produksi, Karyawan, Pesanan, PaginatedData } from '@/types';

type Props = {
    produksi: PaginatedData<Produksi & { karyawan?: Karyawan; pesanan?: Pesanan & { customer?: any } }>;
    karyawanList: Karyawan[];
    pesananList: Pesanan[];
    totalUpahBulanIni: number;
    filters: {
        search?: string;
        karyawan_id?: string;
        tanggal?: string;
    };
};

export default function ProduksiIndex({ produksi, karyawanList, pesananList, totalUpahBulanIni, filters }: Props) {
    const [searchVal, setSearchVal] = useState(filters.search || '');
    const [karyawanVal, setKaryawanVal] = useState(filters.karyawan_id || '');
    const [tanggalVal, setTanggalVal] = useState(filters.tanggal || '');
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [isDetailModalOpen, setIsDetailModalOpen] = useState(false);
    const [selectedProd, setSelectedProd] = useState<any | null>(null);

    // Filter submit handler
    const handleFilterSubmit = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        router.get('/produksi', {
            search: searchVal,
            karyawan_id: karyawanVal,
            tanggal: tanggalVal,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    // Trigger filter automatically when dropdown values change
    useEffect(() => {
        handleFilterSubmit();
    }, [karyawanVal, tanggalVal]);

    // Create Form Hook
    const createForm = useForm({
        karyawan_id: '',
        pesanan_id: '',
        tanggal_produksi: new Date().toISOString().split('T')[0],
        jumlah_produksi: '',
        upah_per_item: '5000', // Default upah rate
        catatan: '',
    });

    // Edit Form Hook
    const editForm = useForm({
        jumlah_produksi: '',
        upah_per_item: '',
        catatan: '',
    });

    const [blurErrors, setBlurErrors] = useState<Record<string, string>>({});

    const validateField = (field: string, value: any) => {
        let err = '';
        if (field === 'karyawan_id' && !value) {
            err = 'Karyawan wajib dipilih.';
        } else if (field === 'pesanan_id' && !value) {
            err = 'Pesanan terkait wajib dipilih.';
        } else if (field === 'tanggal_produksi' && !value) {
            err = 'Tanggal produksi wajib diisi.';
        } else if (field === 'jumlah_produksi') {
            if (!value) err = 'Jumlah produksi wajib diisi.';
            else if (isNaN(Number(value)) || Number(value) < 1) err = 'Jumlah produksi minimal 1 item.';
        } else if (field === 'upah_per_item') {
            if (!value) err = 'Upah per item wajib diisi.';
            else if (isNaN(Number(value)) || Number(value) < 0) err = 'Upah per item minimal Rp 0.';
        }
        setBlurErrors(prev => ({ ...prev, [field]: err }));
    };

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post('/produksi', {
            onSuccess: () => {
                setIsCreateModalOpen(false);
                createForm.reset();
                setBlurErrors({});
            },
        });
    };

    const handleEditClick = (p: any) => {
        setSelectedProd(p);
        editForm.setData({
            jumlah_produksi: String(p.jumlah_produksi),
            upah_per_item: String(p.upah_per_item),
            catatan: p.catatan || '',
        });
        setIsEditModalOpen(true);
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedProd) return;
        editForm.put(`/produksi/${selectedProd.id}`, {
            onSuccess: () => {
                setIsEditModalOpen(false);
                editForm.reset();
                setBlurErrors({});
            },
        });
    };

    const handleDelete = (id: number) => {
        if (confirm('Apakah Anda yakin ingin menghapus log produksi ini? Upah di arus kas juga akan ikut terhapus.')) {
            router.delete(`/produksi/${id}`);
        }
    };

    const fetchDetail = async (id: number) => {
        try {
            const res = await fetch(`/produksi/${id}`);
            const data = await res.json();
            setSelectedProd(data);
            setIsDetailModalOpen(true);
        } catch (err) {
            console.error('Gagal mengambil data detail log produksi.', err);
        }
    };

    const formatRupiah = (num: number) => {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    };

    // Calculate dynamic upah rates live previews inside create/edit forms
    const getLiveUpahPreview = () => {
        const qty = Number(createForm.data.jumlah_produksi) || 0;
        const rate = Number(createForm.data.upah_per_item) || 0;
        return qty * rate;
    };

    const getEditLiveUpahPreview = () => {
        const qty = Number(editForm.data.jumlah_produksi) || 0;
        const rate = Number(editForm.data.upah_per_item) || 0;
        return qty * rate;
    };

    return (
        <AuthenticatedLayout
            pageTitle="Log Produksi & Upah"
            breadcrumb="Catat progres harian sepatu, upah karyawan, dan integrasi pengeluaran kas"
            headerActions={
                <div className="flex flex-wrap items-center gap-2">
                    <a
                        href="/produksi/export/excel"
                        className="flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm hover:shadow"
                    >
                        <i className="ri-file-excel-2-line text-sm"></i> Export Excel
                    </a>
                    <a
                        href="/produksi/export/pdf"
                        className="flex items-center gap-1.5 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm hover:shadow"
                    >
                        <i className="ri-file-pdf-line text-sm"></i> Export PDF
                    </a>
                    <button
                        onClick={() => setIsCreateModalOpen(true)}
                        className="flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm hover:shadow-blue-500/20 shadow-lg cursor-pointer"
                    >
                        <i className="ri-add-line text-sm"></i> Catat Produksi
                    </button>
                </div>
            }
        >
            <Head title="Log Produksi & Upah" />

            {/* Quick Metrics Statistics Widget */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div className="bg-gradient-to-br from-blue-600 to-blue-700 text-white p-5 rounded-2xl shadow-md border border-blue-500/20 flex items-center justify-between">
                    <div>
                        <span className="text-[10px] font-extrabold uppercase tracking-wider opacity-75">Akumulasi Upah Bulan Ini</span>
                        <h2 className="text-3xl font-extrabold mt-1">{formatRupiah(totalUpahBulanIni)}</h2>
                        <span className="text-[10px] opacity-75 mt-1 block">Otomatis tercatat sebagai Pengeluaran Arus Kas</span>
                    </div>
                    <i className="ri-wallet-3-line text-4xl opacity-30"></i>
                </div>
                <div className="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <span className="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Log Produksi</span>
                        <h2 className="text-3xl font-extrabold text-slate-800 mt-1">{produksi.total || 0} entri</h2>
                    </div>
                    <i className="ri-list-check-2 text-4xl text-slate-100"></i>
                </div>
                <div className="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <span className="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Karyawan Aktif</span>
                        <h2 className="text-3xl font-extrabold text-slate-800 mt-1">{karyawanList.length} orang</h2>
                    </div>
                    <i className="ri-group-line text-4xl text-slate-100"></i>
                </div>
            </div>

            {/* Filter Section */}
            <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <form onSubmit={handleFilterSubmit} className="flex-1 flex gap-2">
                    <div className="relative flex-1 max-w-sm">
                        <i className="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            placeholder="Cari kode produksi atau nama..."
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
                        <span className="text-xs text-gray-400 font-bold uppercase">Karyawan:</span>
                        <select
                            value={karyawanVal}
                            onChange={(e) => setKaryawanVal(e.target.value)}
                            className="bg-slate-50 border-1.5 border-gray-100 rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:border-blue-500"
                        >
                            <option value="">Semua Karyawan</option>
                            {karyawanList.map((k) => (
                                <option key={k.id} value={k.id}>{k.nama}</option>
                            ))}
                        </select>
                    </div>

                    <div className="flex items-center gap-2">
                        <span className="text-xs text-gray-400 font-bold uppercase">Tanggal:</span>
                        <input
                            type="date"
                            value={tanggalVal}
                            onChange={(e) => setTanggalVal(e.target.value)}
                            className="bg-slate-50 border-1.5 border-gray-100 rounded-lg px-3 py-1.5 text-xs font-semibold focus:outline-none focus:border-blue-500"
                        />
                    </div>
                </div>
            </div>

            {/* Table / List View */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="bg-slate-50 text-slate-400 text-[10px] font-bold uppercase tracking-wider border-b border-gray-100">
                                <th className="px-6 py-4">Kode Log</th>
                                <th className="px-6 py-4">Karyawan</th>
                                <th className="px-6 py-4">Pesanan / PO</th>
                                <th className="px-6 py-4">Tanggal Prod</th>
                                <th className="px-6 py-4">Jumlah</th>
                                <th className="px-6 py-4">Total Upah</th>
                                <th className="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-50">
                            {produksi.data.length > 0 ? (
                                produksi.data.map((p) => (
                                    <tr key={p.id} className="hover:bg-slate-50/50 transition-colors text-sm">
                                        <td className="px-6 py-4 font-mono text-xs font-bold text-gray-500">
                                            {p.kode_produksi}
                                        </td>
                                        <td className="px-6 py-4 font-bold text-slate-900">
                                            {p.karyawan?.nama || <span className="text-gray-400 italic">Terhapus</span>}
                                        </td>
                                        <td className="px-6 py-4 font-semibold text-blue-600 font-mono text-xs">
                                            {p.pesanan?.kode_pesanan || <span className="text-gray-400 italic">-</span>}
                                        </td>
                                        <td className="px-6 py-4 text-slate-500 font-medium">
                                            {p.tanggal_produksi}
                                        </td>
                                        <td className="px-6 py-4 font-bold text-slate-800">
                                            {p.jumlah_produksi} pasang
                                        </td>
                                        <td className="px-6 py-4 font-extrabold text-slate-900">
                                            {formatRupiah(p.total_upah)} <span className="text-[10px] text-gray-400 font-medium">({formatRupiah(p.upah_per_item)}/item)</span>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-1.5">
                                                <button
                                                    onClick={() => fetchDetail(p.id)}
                                                    className="p-1.5 text-slate-500 hover:text-blue-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                    title="Detail"
                                                >
                                                    <i className="ri-eye-line text-base"></i>
                                                </button>
                                                <button
                                                    onClick={() => handleEditClick(p)}
                                                    className="p-1.5 text-slate-500 hover:text-amber-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                    title="Edit"
                                                >
                                                    <i className="ri-edit-line text-base"></i>
                                                </button>
                                                <button
                                                    onClick={() => handleDelete(p.id)}
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
                                        <p className="text-xs font-semibold">Belum ada data log produksi tersedia</p>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {produksi.links && produksi.links.length > 3 && (
                    <div className="flex justify-between items-center px-6 py-4 bg-slate-50 border-t border-gray-100">
                        <span className="text-xs text-gray-400 font-semibold">
                            Menampilkan {produksi.from || 0} - {produksi.to || 0} dari {produksi.total || 0} data
                        </span>
                        <div className="flex gap-1">
                            {produksi.links.map((link, idx) => {
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
                                <i className="ri-add-circle-line text-blue-600 text-lg"></i> Catat Log Produksi Harian
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
                                    Pilih Karyawan <span className="text-rose-500">*</span>
                                </label>
                                <select
                                    value={createForm.data.karyawan_id}
                                    onChange={(e) => createForm.setData('karyawan_id', e.target.value)}
                                    onBlur={(e) => validateField('karyawan_id', e.target.value)}
                                    className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 ${
                                        createForm.errors.karyawan_id || blurErrors.karyawan_id ? 'border-rose-500' : ''
                                    }`}
                                >
                                    <option value="">-- Pilih Karyawan --</option>
                                    {karyawanList.map((k) => (
                                        <option key={k.id} value={k.id}>{k.nama} ({k.jabatan})</option>
                                    ))}
                                </select>
                                {(createForm.errors.karyawan_id || blurErrors.karyawan_id) && (
                                    <p className="mt-1 text-xs text-rose-500">{createForm.errors.karyawan_id || blurErrors.karyawan_id}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Hubungkan Ke Pesanan / PO <span className="text-rose-500">*</span>
                                </label>
                                <select
                                    value={createForm.data.pesanan_id}
                                    onChange={(e) => createForm.setData('pesanan_id', e.target.value)}
                                    onBlur={(e) => validateField('pesanan_id', e.target.value)}
                                    className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 ${
                                        createForm.errors.pesanan_id || blurErrors.pesanan_id ? 'border-rose-500' : ''
                                    }`}
                                >
                                    <option value="">-- Pilih Pesanan Aktif --</option>
                                    {pesananList.map((p) => (
                                        <option key={p.id} value={p.id}>
                                            {p.kode_pesanan} - {p.customer?.nama || 'Umum'} (Status: {p.status})
                                        </option>
                                    ))}
                                </select>
                                {(createForm.errors.pesanan_id || blurErrors.pesanan_id) && (
                                    <p className="mt-1 text-xs text-rose-500">{createForm.errors.pesanan_id || blurErrors.pesanan_id}</p>
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Tanggal Produksi <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        value={createForm.data.tanggal_produksi}
                                        onChange={(e) => createForm.setData('tanggal_produksi', e.target.value)}
                                        onBlur={(e) => validateField('tanggal_produksi', e.target.value)}
                                        max={new Date().toISOString().split('T')[0]}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 ${
                                            createForm.errors.tanggal_produksi || blurErrors.tanggal_produksi ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(createForm.errors.tanggal_produksi || blurErrors.tanggal_produksi) && (
                                        <p className="mt-1 text-xs text-rose-500">{createForm.errors.tanggal_produksi || blurErrors.tanggal_produksi}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Jumlah Hasil Produksi <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        min={1}
                                        value={createForm.data.jumlah_produksi}
                                        onChange={(e) => createForm.setData('jumlah_produksi', e.target.value)}
                                        onBlur={(e) => validateField('jumlah_produksi', e.target.value)}
                                        placeholder="pasang..."
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 ${
                                            createForm.errors.jumlah_produksi || blurErrors.jumlah_produksi ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(createForm.errors.jumlah_produksi || blurErrors.jumlah_produksi) && (
                                        <p className="mt-1 text-xs text-rose-500">{createForm.errors.jumlah_produksi || blurErrors.jumlah_produksi}</p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Upah per Item (Rp) <span className="text-rose-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    min={0}
                                    value={createForm.data.upah_per_item}
                                    onChange={(e) => createForm.setData('upah_per_item', e.target.value)}
                                    onBlur={(e) => validateField('upah_per_item', e.target.value)}
                                    className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 ${
                                        createForm.errors.upah_per_item || blurErrors.upah_per_item ? 'border-rose-500' : ''
                                    }`}
                                />
                                {(createForm.errors.upah_per_item || blurErrors.upah_per_item) && (
                                    <p className="mt-1 text-xs text-rose-500">{createForm.errors.upah_per_item || blurErrors.upah_per_item}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Keterangan / Catatan
                                </label>
                                <textarea
                                    value={createForm.data.catatan}
                                    onChange={(e) => createForm.setData('catatan', e.target.value)}
                                    placeholder="Masukkan detail tambahan tentang pengerjaan..."
                                    rows={2}
                                    className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500"
                                />
                            </div>

                            {/* Upah Preview Widget */}
                            <div className="bg-slate-50 p-4 rounded-xl flex items-center justify-between border border-gray-100">
                                <span className="text-xs font-bold text-slate-600 uppercase tracking-wider">Kalkulasi Upah Karyawan:</span>
                                <span className="text-lg font-extrabold text-blue-600">{formatRupiah(getLiveUpahPreview())}</span>
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
                                    className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all flex items-center gap-1 cursor-pointer"
                                >
                                    {createForm.processing ? 'Menyimpan...' : 'Simpan & Catat Upah'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* EDIT MODAL */}
            {isEditModalOpen && selectedProd && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
                    <div className="bg-white w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100 my-8">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-2">
                                <i className="ri-edit-circle-line text-amber-500 text-lg"></i> Edit Log Produksi
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
                            <div className="bg-amber-50 text-amber-800 text-xs p-4 rounded-xl border border-amber-100 leading-relaxed">
                                <span className="font-bold">Informasi:</span> Karyawan dan Hubungan Pesanan tidak dapat dirubah setelah log produksi dicatat untuk menjaga keutuhan struktur arus kas.
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Karyawan</label>
                                    <p className="font-bold text-slate-800">{selectedProd.karyawan?.nama}</p>
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Kode Pesanan</label>
                                    <p className="font-mono font-bold text-slate-800">{selectedProd.pesanan?.kode_pesanan}</p>
                                </div>
                            </div>

                            <hr className="border-gray-100" />

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Jumlah Hasil Produksi <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        min={1}
                                        value={editForm.data.jumlah_produksi}
                                        onChange={(e) => editForm.setData('jumlah_produksi', e.target.value)}
                                        onBlur={(e) => validateField('jumlah_produksi', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 ${
                                            editForm.errors.jumlah_produksi || blurErrors.jumlah_produksi ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(editForm.errors.jumlah_produksi || blurErrors.jumlah_produksi) && (
                                        <p className="mt-1 text-xs text-rose-500">{editForm.errors.jumlah_produksi || blurErrors.jumlah_produksi}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Upah per Item (Rp) <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        min={0}
                                        value={editForm.data.upah_per_item}
                                        onChange={(e) => editForm.setData('upah_per_item', e.target.value)}
                                        onBlur={(e) => validateField('upah_per_item', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 ${
                                            editForm.errors.upah_per_item || blurErrors.upah_per_item ? 'border-rose-500' : ''
                                        }`}
                                    />
                                    {(editForm.errors.upah_per_item || blurErrors.upah_per_item) && (
                                        <p className="mt-1 text-xs text-rose-500">{editForm.errors.upah_per_item || blurErrors.upah_per_item}</p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Keterangan / Catatan
                                </label>
                                <textarea
                                    value={editForm.data.catatan}
                                    onChange={(e) => editForm.setData('catatan', e.target.value)}
                                    rows={2}
                                    className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500"
                                />
                            </div>

                            {/* Upah Preview Widget */}
                            <div className="bg-slate-50 p-4 rounded-xl flex items-center justify-between border border-gray-100">
                                <span className="text-xs font-bold text-slate-600 uppercase tracking-wider">Kalkulasi Upah Karyawan:</span>
                                <span className="text-lg font-extrabold text-blue-600">{formatRupiah(getEditLiveUpahPreview())}</span>
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
            {isDetailModalOpen && selectedProd && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
                    <div className="bg-white w-full max-w-md rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900">Detail Hasil Produksi</h3>
                            <button
                                onClick={() => setIsDetailModalOpen(false)}
                                className="text-gray-400 hover:text-gray-600"
                            >
                                <i className="ri-close-line text-xl"></i>
                            </button>
                        </div>
                        <div className="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                            <div>
                                <h4 className="font-bold text-lg text-slate-900">{selectedProd.karyawan?.nama}</h4>
                                <span className="inline-block text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full uppercase mt-1">
                                    {selectedProd.karyawan?.jabatan}
                                </span>
                            </div>

                            <hr className="border-gray-100" />

                            <div className="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Kode Log Produksi</p>
                                    <p className="font-mono font-bold text-slate-800">{selectedProd.kode_produksi}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Tanggal Produksi</p>
                                    <p className="font-bold text-slate-850">{selectedProd.tanggal_produksi}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Hasil Produksi</p>
                                    <p className="font-bold text-slate-850">{selectedProd.jumlah_produksi} pasang</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Tarif per Item</p>
                                    <p className="font-bold text-slate-850">{formatRupiah(selectedProd.upah_per_item)}</p>
                                </div>
                            </div>

                            {/* Total Upah Card */}
                            <div className="bg-slate-50 p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between text-sm">
                                <span className="text-xs font-bold text-slate-600 uppercase tracking-wider">Total Upah Gaji:</span>
                                <span className="text-base font-extrabold text-blue-600">{formatRupiah(selectedProd.total_upah)}</span>
                            </div>

                            {/* Associated PO items overview */}
                            {selectedProd.pesanan && (
                                <div className="border border-gray-100 p-4 rounded-xl space-y-2">
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pesanan Terkait</p>
                                    <p className="text-xs font-bold text-blue-600 font-mono">{selectedProd.pesanan.kode_pesanan}</p>
                                    <p className="text-xs font-semibold text-slate-800">Customer: {selectedProd.pesanan.customer?.nama || 'Umum'}</p>

                                    <div className="bg-slate-50 p-2.5 rounded-lg text-[11px] text-slate-600 leading-relaxed max-h-24 overflow-y-auto">
                                        <p className="font-bold border-b border-gray-200 pb-1 mb-1">Rincian Order PO:</p>
                                        {selectedProd.pesanan.detail_pesanan?.map((detail: any) => (
                                            <div key={detail.id} className="flex justify-between py-0.5">
                                                <span>• {detail.nama_produk_snapshot} ({detail.ukuran || '-'}, {detail.warna || '-'})</span>
                                                <span className="font-bold">{detail.jumlah} pasang</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {selectedProd.catatan && (
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Catatan Pengerjaan</p>
                                    <div className="bg-slate-50 p-3 rounded-lg text-slate-600 text-xs leading-relaxed max-h-24 overflow-y-auto">
                                        {selectedProd.catatan}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
