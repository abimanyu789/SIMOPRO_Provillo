import React, { useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { AuthenticatedLayout } from '@/layouts/AuthenticatedLayout';
import { Pesanan, Customer, Produk, PaginatedData } from '@/types';

type Props = {
    pesanan: PaginatedData<Pesanan & { customer?: Customer }>;
    customers: Customer[];
    produkList: Produk[];
    statusCount: Record<string, number>;
    filters: {
        search?: string;
        status?: string;
    };
};

type OrderItemInput = {
    produk_id: string;
    jumlah: number;
    ukuran: string;
    warna: string;
};

export default function PesananIndex({ pesanan, customers, produkList, statusCount, filters }: Props) {
    const [searchVal, setSearchVal] = useState(filters.search || '');
    const [statusVal, setStatusVal] = useState(filters.status || '');
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [isDetailModalOpen, setIsDetailModalOpen] = useState(false);
    const [selectedPesanan, setSelectedPesanan] = useState<any | null>(null);

    // Filter submit handler
    const handleFilterSubmit = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        router.get('/pesanan', {
            search: searchVal,
            status: statusVal,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    // Create Form Hook
    const createForm = useForm({
        customer_id: '',
        tanggal_pesanan: new Date().toISOString().split('T')[0],
        tanggal_kirim: '',
        catatan: '',
        items: [] as OrderItemInput[],
    });

    const addOrderItem = () => {
        createForm.setData('items', [
            ...createForm.data.items,
            { produk_id: '', jumlah: 1, ukuran: '', warna: '' }
        ]);
    };

    const removeOrderItem = (idx: number) => {
        const newItems = [...createForm.data.items];
        newItems.splice(idx, 1);
        createForm.setData('items', newItems);
    };

    const updateOrderItem = (idx: number, key: keyof OrderItemInput, val: any) => {
        const newItems = [...createForm.data.items];
        newItems[idx] = { ...newItems[idx], [key]: val };
        createForm.setData('items', newItems);
    };

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post('/pesanan', {
            onSuccess: () => {
                setIsCreateModalOpen(false);
                createForm.reset();
            },
        });
    };

    const handleStatusUpdate = (id: number, nextStatus: string) => {
        router.patch(`/pesanan/${id}/status`, { status: nextStatus }, {
            onSuccess: () => {
                // If detail modal is open, refresh detail information
                if (isDetailModalOpen && selectedPesanan?.id === id) {
                    fetchDetail(id);
                }
            }
        });
    };

    const handleDelete = (id: number) => {
        if (confirm('Apakah Anda yakin ingin menghapus pesanan ini?')) {
            router.delete(`/pesanan/${id}`);
        }
    };

    const fetchDetail = async (id: number) => {
        try {
            const res = await fetch(`/pesanan/${id}`);
            const data = await res.json();
            setSelectedPesanan(data);
            setIsDetailModalOpen(true);
        } catch (err) {
            console.error('Gagal mengambil detail pesanan.', err);
        }
    };

    const formatRupiah = (num: number) => {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    };

    // Helper to calculate grand total dynamically inside Create Form
    const calculateGrandTotal = () => {
        return createForm.data.items.reduce((sum, item) => {
            const prod = produkList.find(p => String(p.id) === String(item.produk_id));
            const harga = prod ? prod.harga_jual : 0;
            return sum + (harga * (item.jumlah || 0));
        }, 0);
    };

    const getStatusBadge = (status: string) => {
        const classes: Record<string, string> = {
            pending: 'bg-amber-100 text-amber-800 border-amber-200',
            diproses: 'bg-blue-100 text-blue-800 border-blue-200',
            produksi: 'bg-purple-100 text-purple-800 border-purple-200',
            selesai: 'bg-emerald-100 text-emerald-800 border-emerald-200',
            closed: 'bg-slate-100 text-slate-800 border-slate-200',
        };
        return (
            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border ${classes[status] || 'bg-gray-100 text-gray-800'}`}>
                {status.toUpperCase()}
            </span>
        );
    };

    return (
        <AuthenticatedLayout
            pageTitle="Manajemen Pesanan"
            breadcrumb="Kelola siklus pesanan masuk, status pengiriman, dan snapshot transaksi"
            headerActions={
                <button
                    onClick={() => {
                        setIsCreateModalOpen(true);
                        // Add an initial empty item line by default
                        createForm.setData('items', [{ produk_id: '', jumlah: 1, ukuran: '', warna: '' }]);
                    }}
                    className="flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm hover:shadow-blue-500/20 shadow-lg cursor-pointer"
                >
                    <i className="ri-add-line text-sm"></i> Tambah Pesanan
                </button>
            }
        >
            <Head title="Manajemen Pesanan" />

            {/* Quick Stats Quick-Cards */}
            <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                {['pending', 'diproses', 'produksi', 'selesai', 'closed'].map((status) => {
                    const count = statusCount[status] || 0;
                    const colors: Record<string, string> = {
                        pending: 'from-amber-500/10 to-amber-500/5 text-amber-600 border-amber-100',
                        diproses: 'from-blue-500/10 to-blue-500/5 text-blue-600 border-blue-100',
                        produksi: 'from-purple-500/10 to-purple-500/5 text-purple-600 border-purple-100',
                        selesai: 'from-emerald-500/10 to-emerald-500/5 text-emerald-600 border-emerald-100',
                        closed: 'from-slate-500/10 to-slate-500/5 text-slate-600 border-slate-100',
                    };
                    return (
                        <div
                            key={status}
                            onClick={() => {
                                setStatusVal(status);
                                router.get('/pesanan', { search: searchVal, status }, { preserveState: true });
                            }}
                            className={`bg-gradient-to-br ${colors[status]} border p-4 rounded-xl shadow-sm hover:scale-[1.02] cursor-pointer transition-all flex flex-col justify-between`}
                        >
                            <span className="text-[10px] font-bold uppercase tracking-wider block opacity-75">{status}</span>
                            <span className="text-2xl font-extrabold block mt-2">{count}</span>
                        </div>
                    );
                })}
            </div>

            {/* Filter Section */}
            <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <form onSubmit={handleFilterSubmit} className="flex-1 flex gap-2">
                    <div className="relative flex-1 max-w-sm">
                        <i className="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            placeholder="Cari kode pesanan atau nama customer..."
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
                    <span className="text-xs text-gray-400 font-bold uppercase">Status Filter:</span>
                    <select
                        value={statusVal}
                        onChange={(e) => {
                            setStatusVal(e.target.value);
                            router.get('/pesanan', { search: searchVal, status: e.target.value }, { preserveState: true });
                        }}
                        className="bg-slate-50 border-1.5 border-gray-100 rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:border-blue-500"
                    >
                        <option value="">Semua Status</option>
                        <option value="pending">PENDING</option>
                        <option value="diproses">DIPROSES</option>
                        <option value="produksi">PRODUKSI</option>
                        <option value="selesai">SELESAI</option>
                        <option value="closed">CLOSED</option>
                    </select>
                </div>
            </div>

            {/* Table / List View */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="bg-slate-50 text-slate-400 text-[10px] font-bold uppercase tracking-wider border-b border-gray-100">
                                <th className="px-6 py-4">Kode PO</th>
                                <th className="px-6 py-4">Customer</th>
                                <th className="px-6 py-4">Tanggal PO</th>
                                <th className="px-6 py-4">Estimasi Kirim</th>
                                <th className="px-6 py-4">Total Harga</th>
                                <th className="px-6 py-4">Status</th>
                                <th className="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-50">
                            {pesanan.data.length > 0 ? (
                                pesanan.data.map((p) => (
                                    <tr key={p.id} className="hover:bg-slate-50/50 transition-colors text-sm">
                                        <td className="px-6 py-4 font-mono text-xs font-bold text-blue-600">
                                            {p.kode_pesanan}
                                        </td>
                                        <td className="px-6 py-4 font-bold text-slate-900">
                                            {p.customer?.nama || <span className="text-gray-400 italic">Umum</span>}
                                        </td>
                                        <td className="px-6 py-4 text-slate-500 font-medium">
                                            {p.tanggal_pesanan}
                                        </td>
                                        <td className="px-6 py-4 text-slate-500 font-medium">
                                            {p.tanggal_kirim || <span className="text-gray-400">-</span>}
                                        </td>
                                        <td className="px-6 py-4 font-extrabold text-slate-900">
                                            {formatRupiah(p.total_harga)}
                                        </td>
                                        <td className="px-6 py-4">
                                            {getStatusBadge(p.status)}
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
                                                {p.status === 'pending' && (
                                                    <button
                                                        onClick={() => handleDelete(p.id)}
                                                        className="p-1.5 text-slate-500 hover:text-rose-600 rounded hover:bg-slate-100 transition-all cursor-pointer"
                                                        title="Hapus"
                                                    >
                                                        <i className="ri-delete-bin-line text-base"></i>
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={7} className="px-6 py-12 text-center text-gray-400">
                                        <i className="ri-inbox-line text-4xl block mb-2 opacity-50"></i>
                                        <p className="text-xs font-semibold">Belum ada data pesanan masuk</p>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {pesanan.links && pesanan.links.length > 3 && (
                    <div className="flex justify-between items-center px-6 py-4 bg-slate-50 border-t border-gray-100">
                        <span className="text-xs text-gray-400 font-semibold">
                            Menampilkan {pesanan.from || 0} - {pesanan.to || 0} dari {pesanan.total || 0} data
                        </span>
                        <div className="flex gap-1">
                            {pesanan.links.map((link, idx) => {
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
                    <div className="bg-white w-full max-w-3xl rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100 my-8">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-2">
                                <i className="ri-add-circle-line text-blue-600 text-lg"></i> Buat Pesanan Baru
                            </h3>
                            <button
                                onClick={() => {
                                    setIsCreateModalOpen(false);
                                    createForm.reset();
                                }}
                                className="text-gray-400 hover:text-gray-600"
                            >
                                <i className="ri-close-line text-xl"></i>
                            </button>
                        </div>
                        <form onSubmit={handleCreateSubmit} className="p-5 space-y-4 max-h-[75vh] overflow-y-auto">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Pilih Customer <span className="text-rose-500">*</span>
                                    </label>
                                    <select
                                        value={createForm.data.customer_id}
                                        onChange={(e) => createForm.setData('customer_id', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            createForm.errors.customer_id ? 'border-rose-500' : ''
                                        }`}
                                    >
                                        <option value="">-- Pilih Customer --</option>
                                        {customers.map((c) => (
                                            <option key={c.id} value={c.id}>{c.nama} ({c.kode_customer})</option>
                                        ))}
                                    </select>
                                    {createForm.errors.customer_id && (
                                        <p className="mt-1 text-xs text-rose-500">{createForm.errors.customer_id}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Tanggal Pesanan <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        value={createForm.data.tanggal_pesanan}
                                        onChange={(e) => createForm.setData('tanggal_pesanan', e.target.value)}
                                        className={`w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all ${
                                            createForm.errors.tanggal_pesanan ? 'border-rose-500' : ''
                                        }`}
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Estimasi Tanggal Kirim
                                    </label>
                                    <input
                                        type="date"
                                        value={createForm.data.tanggal_kirim}
                                        onChange={(e) => createForm.setData('tanggal_kirim', e.target.value)}
                                        className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                    />
                                </div>
                            </div>

                            {/* Item Builder */}
                            <div className="pt-2">
                                <div className="flex justify-between items-center mb-3">
                                    <span className="text-xs font-bold text-slate-800 uppercase tracking-wider">Item Detail Pesanan</span>
                                    <button
                                        type="button"
                                        onClick={addOrderItem}
                                        className="flex items-center gap-1 px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-[10px] font-bold transition-all cursor-pointer"
                                    >
                                        <i className="ri-add-line"></i> Tambah Item
                                    </button>
                                </div>

                                {createForm.data.items.length === 0 ? (
                                    <div className="bg-slate-50 text-center py-6 text-gray-400 rounded-xl border border-dashed border-gray-200">
                                        <p className="text-xs font-semibold">Minimal satu item produk wajib ditambahkan.</p>
                                    </div>
                                ) : (
                                    <div className="space-y-3">
                                        {createForm.data.items.map((item, idx) => {
                                            const selectedProduct = produkList.find(p => String(p.id) === String(item.produk_id));
                                            const subtotal = selectedProduct ? (selectedProduct.harga_jual * (item.jumlah || 0)) : 0;
                                            return (
                                                <div key={idx} className="bg-slate-50/50 p-4 rounded-xl border border-gray-100 flex flex-col md:flex-row items-center gap-3">
                                                    <div className="flex-1 w-full">
                                                        <select
                                                            value={item.produk_id}
                                                            onChange={(e) => updateOrderItem(idx, 'produk_id', e.target.value)}
                                                            className="w-full bg-white border-1.5 border-gray-200 rounded-lg p-2 text-xs focus:outline-none"
                                                        >
                                                            <option value="">-- Pilih Produk Sepatu --</option>
                                                            {produkList.map((p) => (
                                                                <option key={p.id} value={p.id}>
                                                                    {p.nama_produk} ({formatRupiah(p.harga_jual)}) - Stok: {p.stok?.jumlah_stok || 0} pasang
                                                                </option>
                                                            ))}
                                                        </select>
                                                    </div>

                                                    <div className="w-20">
                                                        <input
                                                            type="number"
                                                            placeholder="Qty"
                                                            min={1}
                                                            value={item.jumlah}
                                                            onChange={(e) => updateOrderItem(idx, 'jumlah', parseInt(e.target.value) || 0)}
                                                            className="w-full bg-white border-1.5 border-gray-200 rounded-lg p-2 text-xs text-center focus:outline-none"
                                                        />
                                                    </div>

                                                    <div className="w-20">
                                                        <input
                                                            type="text"
                                                            placeholder="Size"
                                                            value={item.ukuran}
                                                            onChange={(e) => updateOrderItem(idx, 'ukuran', e.target.value)}
                                                            className="w-full bg-white border-1.5 border-gray-200 rounded-lg p-2 text-xs text-center focus:outline-none"
                                                        />
                                                    </div>

                                                    <div className="w-24">
                                                        <input
                                                            type="text"
                                                            placeholder="Warna"
                                                            value={item.warna}
                                                            onChange={(e) => updateOrderItem(idx, 'warna', e.target.value)}
                                                            className="w-full bg-white border-1.5 border-gray-200 rounded-lg p-2 text-xs text-center focus:outline-none"
                                                        />
                                                    </div>

                                                    <div className="w-32 text-right">
                                                        <p className="text-[10px] font-bold text-gray-400 uppercase">Subtotal</p>
                                                        <p className="text-xs font-bold text-slate-800">{formatRupiah(subtotal)}</p>
                                                    </div>

                                                    <button
                                                        type="button"
                                                        onClick={() => removeOrderItem(idx)}
                                                        className="p-1.5 text-rose-500 hover:bg-rose-50 rounded transition-colors"
                                                    >
                                                        <i className="ri-delete-bin-line text-base"></i>
                                                    </button>
                                                </div>
                                            );
                                        })}
                                    </div>
                                )}
                                {createForm.errors.items && (
                                    <p className="mt-1.5 text-xs text-rose-500">{createForm.errors.items}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Catatan Tambahan
                                </label>
                                <textarea
                                    value={createForm.data.catatan}
                                    onChange={(e) => createForm.setData('catatan', e.target.value)}
                                    placeholder="Masukkan detail instruksi, keterangan pengiriman..."
                                    rows={2}
                                    className="w-full bg-slate-50 border-1.5 border-gray-100 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all"
                                />
                            </div>

                            {/* Total Calculator */}
                            <div className="bg-slate-50 p-4 rounded-xl flex items-center justify-between">
                                <span className="text-xs font-bold text-slate-600 uppercase tracking-wider">Total Nilai Pesanan:</span>
                                <span className="text-lg font-extrabold text-blue-600">{formatRupiah(calculateGrandTotal())}</span>
                            </div>

                            <div className="flex justify-end gap-2 pt-3 border-t border-gray-100">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setIsCreateModalOpen(false);
                                        createForm.reset();
                                    }}
                                    className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-lg transition-all animate-fade-in"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={createForm.processing}
                                    className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all flex items-center gap-1 cursor-pointer"
                                >
                                    {createForm.processing ? 'Memproses...' : 'Simpan & Catat Transaksi'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* DETAIL VIEW MODAL */}
            {isDetailModalOpen && selectedPesanan && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
                    <div className="bg-white w-full max-w-2xl rounded-2xl overflow-hidden shadow-2xl animate-scale-up border border-gray-100 my-8">
                        <div className="flex justify-between items-center p-5 border-b border-gray-50 bg-slate-50">
                            <h3 className="font-bold text-slate-900 flex items-center gap-1.5">
                                Detail Informasi Pesanan <span className="font-mono text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">{selectedPesanan.kode_pesanan}</span>
                            </h3>
                            <button
                                onClick={() => setIsDetailModalOpen(false)}
                                className="text-gray-400 hover:text-gray-600"
                            >
                                <i className="ri-close-line text-xl"></i>
                            </button>
                        </div>
                        <div className="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                            {/* Workflow Actions Controls */}
                            <div className="bg-slate-50 p-4 rounded-xl flex flex-wrap items-center justify-between gap-3 border border-gray-100 shadow-sm">
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Status Alur Kerja Saat Ini</p>
                                    {getStatusBadge(selectedPesanan.status)}
                                </div>
                                <div className="flex items-center gap-1.5">
                                    {selectedPesanan.status === 'pending' && (
                                        <button
                                            onClick={() => handleStatusUpdate(selectedPesanan.id, 'diproses')}
                                            className="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all cursor-pointer"
                                        >
                                            Proses Order <i className="ri-arrow-right-line"></i>
                                        </button>
                                    )}
                                    {selectedPesanan.status === 'diproses' && (
                                        <div className="flex gap-1.5">
                                            <button
                                                onClick={() => handleStatusUpdate(selectedPesanan.id, 'pending')}
                                                className="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition-all cursor-pointer"
                                            >
                                                <i className="ri-arrow-left-line"></i> Kembalikan ke Pending
                                            </button>
                                            <button
                                                onClick={() => handleStatusUpdate(selectedPesanan.id, 'produksi')}
                                                className="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-bold transition-all cursor-pointer"
                                            >
                                                Kirim ke Produksi <i className="ri-arrow-right-line"></i>
                                            </button>
                                        </div>
                                    )}
                                    {selectedPesanan.status === 'produksi' && (
                                        <div className="flex gap-1.5">
                                            <button
                                                onClick={() => handleStatusUpdate(selectedPesanan.id, 'diproses')}
                                                className="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition-all cursor-pointer"
                                            >
                                                <i className="ri-arrow-left-line"></i> Kembalikan ke Diproses
                                            </button>
                                            <button
                                                onClick={() => handleStatusUpdate(selectedPesanan.id, 'selesai')}
                                                className="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all cursor-pointer"
                                                title="Selesaikan pesanan dan kurangi stok otomatis"
                                            >
                                                Selesaikan Pesanan <i className="ri-check-line"></i>
                                            </button>
                                        </div>
                                    )}
                                    {selectedPesanan.status === 'selesai' && (
                                        <div className="flex gap-1.5">
                                            <button
                                                onClick={() => handleStatusUpdate(selectedPesanan.id, 'produksi')}
                                                className="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition-all cursor-pointer"
                                            >
                                                <i className="ri-arrow-left-line"></i> Kembalikan ke Produksi
                                            </button>
                                            <button
                                                onClick={() => handleStatusUpdate(selectedPesanan.id, 'closed')}
                                                className="px-3 py-1.5 bg-slate-750 hover:bg-slate-850 text-white rounded-lg text-xs font-bold transition-all cursor-pointer"
                                            >
                                                Tutup Pesanan (Closed) <i className="ri-lock-line"></i>
                                            </button>
                                        </div>
                                    )}
                                    {selectedPesanan.status === 'closed' && (
                                        <span className="text-xs text-slate-500 font-semibold flex items-center gap-1 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
                                            <i className="ri-lock-line"></i> Pesanan ini telah ditutup & terkunci
                                        </span>
                                    )}
                                </div>
                            </div>

                            {/* Customer Profile & Info Header */}
                            <div className="grid grid-cols-2 gap-4 text-sm bg-slate-50/50 p-4 rounded-xl border border-gray-50">
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Nama Customer</p>
                                    <p className="font-bold text-slate-900">{selectedPesanan.customer?.nama || 'Umum'}</p>
                                    <p className="text-xs text-gray-500 mt-0.5">{selectedPesanan.customer?.no_hp || '-'}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Pengiriman</p>
                                    <p className="font-semibold text-slate-800">
                                        Tanggal PO: <span className="font-bold text-slate-900">{selectedPesanan.tanggal_pesanan}</span>
                                    </p>
                                    <p className="text-xs text-gray-500 mt-0.5">
                                        Estimasi Kirim: <span className="font-semibold text-slate-800">{selectedPesanan.tanggal_kirim || '-'}</span>
                                    </p>
                                </div>
                            </div>

                            {/* Detail Items Listing */}
                            <div>
                                <p className="text-xs font-bold text-slate-800 mb-3">Item Pembelian (Snapshot)</p>
                                <div className="border border-gray-100 rounded-xl overflow-hidden shadow-sm">
                                    <table className="w-full text-left text-xs border-collapse">
                                        <thead>
                                            <tr className="bg-slate-50 text-slate-400 font-bold uppercase border-b border-gray-100">
                                                <th className="px-4 py-2.5">Nama Produk</th>
                                                <th className="px-4 py-2.5 text-center">Size</th>
                                                <th className="px-4 py-2.5 text-center">Warna</th>
                                                <th className="px-4 py-2.5 text-right">Harga</th>
                                                <th className="px-4 py-2.5 text-center">Qty</th>
                                                <th className="px-4 py-2.5 text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-50 text-slate-700">
                                            {selectedPesanan.detail_pesanan?.map((detail: any) => (
                                                <tr key={detail.id} className="hover:bg-slate-50/50">
                                                    <td className="px-4 py-3 font-semibold text-slate-900">
                                                        {detail.nama_produk_snapshot}
                                                    </td>
                                                    <td className="px-4 py-3 text-center font-bold text-slate-600">
                                                        {detail.ukuran || '-'}
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        {detail.warna || '-'}
                                                    </td>
                                                    <td className="px-4 py-3 text-right font-semibold">
                                                        {formatRupiah(detail.harga_satuan_snapshot)}
                                                    </td>
                                                    <td className="px-4 py-3 text-center font-bold">
                                                        {detail.jumlah} pasang
                                                    </td>
                                                    <td className="px-4 py-3 text-right font-extrabold text-slate-900">
                                                        {formatRupiah(detail.subtotal)}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {selectedPesanan.catatan && (
                                <div>
                                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Catatan Pesanan</p>
                                    <div className="bg-slate-50 p-3 rounded-lg text-slate-600 text-xs leading-relaxed max-h-24 overflow-y-auto">
                                        {selectedPesanan.catatan}
                                    </div>
                                </div>
                            )}

                            {/* Total Calculator */}
                            <div className="bg-slate-50 p-4 rounded-xl flex items-center justify-between border border-gray-100 shadow-sm">
                                <span className="text-xs font-bold text-slate-600 uppercase tracking-wider">Total Transaksi:</span>
                                <span className="text-lg font-extrabold text-blue-600">{formatRupiah(selectedPesanan.total_harga)}</span>
                            </div>

                            {/* Print / Export Invoice Section */}
                            <div className="flex justify-between items-center pt-3 border-t border-gray-100">
                                <div className="flex items-center gap-2">
                                    <a
                                        href={`/pesanan/${selectedPesanan.id}/invoice/print`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="flex items-center gap-1.5 px-3 py-2 bg-slate-700 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition-all shadow-sm cursor-pointer"
                                    >
                                        <i className="ri-printer-line"></i> Cetak Invoice
                                    </a>
                                    <a
                                        href={`/pesanan/${selectedPesanan.id}/invoice/pdf`}
                                        className="flex items-center gap-1.5 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm cursor-pointer"
                                    >
                                        <i className="ri-file-pdf-line"></i> Unduh PDF
                                    </a>
                                </div>
                                <button
                                    onClick={() => setIsDetailModalOpen(false)}
                                    className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-lg transition-all"
                                >
                                    Tutup Detail
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
