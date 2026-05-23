import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import { AuthenticatedLayout } from '@/layouts/AuthenticatedLayout';
import { Pesanan, StokProduk, StokBahanBaku } from '@/types';

type DashboardProps = {
    totalPesananAktif: number;
    pemasukanBulanIni: number;
    pengeluaranBulanIni: number;
    saldoTotal: number;
    totalProduk: number;
    totalKaryawan: number;
    totalCustomer: number;
    produksiHariIni: number;
    stokProdukAlert: StokProduk[];
    stokBahanAlert: StokBahanBaku[];
    chartData: {
        labels: string[];
        pemasukan: number[];
        pengeluaran: number[];
    };
    pesananTerbaru: Pesanan[];
    statusPesanan: Record<string, number>;
};

export default function Dashboard({
    totalPesananAktif,
    pemasukanBulanIni,
    pengeluaranBulanIni,
    saldoTotal,
    totalProduk,
    totalKaryawan,
    totalCustomer,
    produksiHariIni,
    stokProdukAlert,
    stokBahanAlert,
    chartData,
    pesananTerbaru,
    statusPesanan,
}: DashboardProps) {
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const timer = setTimeout(() => setLoading(false), 800);
        return () => clearTimeout(timer);
    }, []);

    const formatRupiah = (num: number) => {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    };

    // Calculate chart dimensions
    const maxVal = Math.max(...chartData.pemasukan, ...chartData.pengeluaran, 1000000);
    const chartHeight = 150;
    const chartWidth = 500;
    const padding = 30;

    const pointsPemasukan = chartData.pemasukan.map((val, idx) => {
        const x = padding + (idx * (chartWidth - padding * 2)) / (chartData.labels.length - 1);
        const y = chartHeight - padding - (val / maxVal) * (chartHeight - padding * 2);
        return `${x},${y}`;
    }).join(' ');

    const pointsPengeluaran = chartData.pengeluaran.map((val, idx) => {
        const x = padding + (idx * (chartWidth - padding * 2)) / (chartData.labels.length - 1);
        const y = chartHeight - padding - (val / maxVal) * (chartHeight - padding * 2);
        return `${x},${y}`;
    }).join(' ');

    if (loading) {
        return (
            <AuthenticatedLayout
                pageTitle="Dashboard"
                breadcrumb="Ringkasan data operasional Provillo"
            >
                <Head title="Dashboard" />
                <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
                    {[1, 2, 3, 4].map((i) => (
                        <div key={i} className="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm animate-pulse">
                            <div className="flex justify-between items-center mb-4">
                                <div className="w-10 h-10 bg-gray-200 rounded-xl"></div>
                                <div className="w-12 h-5 bg-gray-200 rounded-full"></div>
                            </div>
                            <div className="w-24 h-8 bg-gray-200 rounded mb-2"></div>
                            <div className="w-16 h-4 bg-gray-200 rounded"></div>
                        </div>
                    ))}
                </div>
                <div className="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-6">
                    <div className="xl:col-span-2 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm animate-pulse h-64"></div>
                    <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm animate-pulse h-64"></div>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout
            pageTitle="Dashboard"
            breadcrumb="Ringkasan data operasional Provillo"
        >
            <Head title="Dashboard" />

            {/* KPI Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
                {/* Total Pesanan Aktif */}
                <div className="bg-white p-5 rounded-2xl border border-blue-50 shadow-sm hover:shadow-md transition-all">
                    <div className="flex justify-between items-center mb-4">
                        <div className="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                            <i className="ri-shopping-bag-3-line text-xl"></i>
                        </div>
                        <span className="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full uppercase">
                            Aktif
                        </span>
                    </div>
                    <h3 className="text-3xl font-extrabold text-slate-900 mb-1">{totalPesananAktif}</h3>
                    <p className="text-xs text-slate-400 font-semibold">Total Pesanan Aktif</p>
                </div>

                {/* Pemasukan */}
                <div className="bg-white p-5 rounded-2xl border border-emerald-50 shadow-sm hover:shadow-md transition-all">
                    <div className="flex justify-between items-center mb-4">
                        <div className="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                            <i className="ri-arrow-up-circle-line text-xl"></i>
                        </div>
                        <span className="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full uppercase">
                            Bulan Ini
                        </span>
                    </div>
                    <h3 className="text-2xl font-extrabold text-slate-900 mb-1">{formatRupiah(pemasukanBulanIni)}</h3>
                    <p className="text-xs text-slate-400 font-semibold">Total Pemasukan</p>
                </div>

                {/* Pengeluaran */}
                <div className="bg-white p-5 rounded-2xl border border-rose-50 shadow-sm hover:shadow-md transition-all">
                    <div className="flex justify-between items-center mb-4">
                        <div className="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600">
                            <i className="ri-arrow-down-circle-line text-xl"></i>
                        </div>
                        <span className="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full uppercase">
                            Bulan Ini
                        </span>
                    </div>
                    <h3 className="text-2xl font-extrabold text-slate-900 mb-1">{formatRupiah(pengeluaranBulanIni)}</h3>
                    <p className="text-xs text-slate-400 font-semibold">Total Pengeluaran</p>
                </div>

                {/* Saldo Arus Kas */}
                <div className={`bg-white p-5 rounded-2xl border shadow-sm hover:shadow-md transition-all ${
                    saldoTotal >= 0 ? 'border-emerald-50' : 'border-rose-100'
                }`}>
                    <div className="flex justify-between items-center mb-4">
                        <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${
                            saldoTotal >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'
                        }`}>
                            <i className="ri-wallet-3-line text-xl"></i>
                        </div>
                        <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full uppercase ${
                            saldoTotal >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'
                        }`}>
                            {saldoTotal >= 0 ? 'Surplus' : 'Defisit'}
                        </span>
                    </div>
                    <h3 className={`text-2xl font-extrabold mb-1 ${
                        saldoTotal >= 0 ? 'text-slate-900' : 'text-rose-600'
                    }`}>
                        {formatRupiah(Math.abs(saldoTotal))}
                    </h3>
                    <p className="text-xs text-slate-400 font-semibold">Saldo Arus Kas</p>
                </div>
            </div>

            {/* Secondary stats */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                    <div className="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600 flex-shrink-0">
                        <i className="ri-shoe-line text-xl"></i>
                    </div>
                    <div>
                        <p className="text-[10px] text-gray-400 font-bold uppercase">Total Produk</p>
                        <p className="text-lg font-bold text-slate-900">{totalProduk}</p>
                    </div>
                </div>

                <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                    <div className="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600 flex-shrink-0">
                        <i className="ri-team-line text-xl"></i>
                    </div>
                    <div>
                        <p className="text-[10px] text-gray-400 font-bold uppercase">Karyawan</p>
                        <p className="text-lg font-bold text-slate-900">{totalKaryawan}</p>
                    </div>
                </div>

                <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                    <div className="w-10 h-10 bg-teal-50 rounded-xl flex items-center justify-center text-teal-600 flex-shrink-0">
                        <i className="ri-user-star-line text-xl"></i>
                    </div>
                    <div>
                        <p className="text-[10px] text-gray-400 font-bold uppercase">Customer</p>
                        <p className="text-lg font-bold text-slate-900">{totalCustomer}</p>
                    </div>
                </div>

                <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                    <div className="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 flex-shrink-0">
                        <i className="ri-tools-line text-xl"></i>
                    </div>
                    <div>
                        <p className="text-[10px] text-gray-400 font-bold uppercase">Produksi</p>
                        <p className="text-lg font-bold text-slate-900">{produksiHariIni} psg</p>
                    </div>
                </div>
            </div>

            {/* Graphs & Charts */}
            <div className="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-6">
                {/* Line Chart Overview */}
                <div className="xl:col-span-2 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                    <div className="flex justify-between items-center mb-5">
                        <div>
                            <h3 className="font-bold text-slate-950">Ikhtisar Keuangan Bulanan</h3>
                            <p className="text-xs text-gray-400">Pemasukan dan Pengeluaran tahun berjalan</p>
                        </div>
                        <div className="flex gap-4 text-xs font-semibold">
                            <div className="flex items-center gap-1.5">
                                <span className="w-3 h-3 rounded-full bg-blue-500 block"></span>
                                <span className="text-slate-500">Pemasukan</span>
                            </div>
                            <div className="flex items-center gap-1.5">
                                <span className="w-3 h-3 rounded-full bg-rose-400 block"></span>
                                <span className="text-slate-500">Pengeluaran</span>
                            </div>
                        </div>
                    </div>
                    
                    {/* SVG Line Graph */}
                    <div className="relative">
                        <svg viewBox={`0 0 ${chartWidth} ${chartHeight}`} className="w-full h-auto">
                            {/* Grid lines */}
                            {[0, 1, 2, 3].map((val) => {
                                const y = padding + (val * (chartHeight - padding * 2)) / 3;
                                return (
                                    <line
                                        key={val}
                                        x1={padding}
                                        y1={y}
                                        x2={chartWidth - padding}
                                        y2={y}
                                        stroke="#f1f5f9"
                                        strokeWidth="1.5"
                                    />
                                );
                            })}
                            
                            {/* Line Pemasukan */}
                            <polyline
                                fill="none"
                                stroke="#3b82f6"
                                strokeWidth="3"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                points={pointsPemasukan}
                            />
                            
                            {/* Line Pengeluaran */}
                            <polyline
                                fill="none"
                                stroke="#f87171"
                                strokeWidth="3"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                points={pointsPengeluaran}
                            />
                            
                            {/* X Axis Labels */}
                            {chartData.labels.map((label, idx) => {
                                const x = padding + (idx * (chartWidth - padding * 2)) / (chartData.labels.length - 1);
                                return (
                                    <text
                                        key={idx}
                                        x={x}
                                        y={chartHeight - 5}
                                        fill="#94a3b8"
                                        fontSize="9"
                                        fontWeight="600"
                                        textAnchor="middle"
                                    >
                                        {label}
                                    </text>
                                );
                            })}
                        </svg>
                    </div>
                </div>

                {/* Status Distribution */}
                <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 className="font-bold text-slate-950">Status Pesanan</h3>
                        <p className="text-xs text-gray-400 mb-4">Distribusi status pesanan saat ini</p>
                    </div>

                    {Object.keys(statusPesanan).length > 0 ? (
                        <div className="space-y-3">
                            {Object.entries(statusPesanan).map(([status, count]) => {
                                const total = Object.values(statusPesanan).reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? Math.round((count / total) * 100) : 0;
                                const colors: Record<string, string> = {
                                    pending: 'bg-amber-500',
                                    diproses: 'bg-blue-500',
                                    produksi: 'bg-purple-500',
                                    selesai: 'bg-emerald-500',
                                    closed: 'bg-slate-400',
                                };
                                return (
                                    <div key={status} className="text-sm">
                                        <div className="flex justify-between items-center font-semibold mb-1">
                                            <span className="capitalize text-slate-600 flex items-center gap-1.5">
                                                <span className={`w-2 h-2 rounded-full ${colors[status] || 'bg-slate-400'}`}></span>
                                                {status}
                                            </span>
                                            <span className="text-slate-900">{count} psg ({percentage}%)</span>
                                        </div>
                                        <div className="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                            <div
                                                className={`h-full ${colors[status] || 'bg-slate-400'}`}
                                                style={{ width: `${percentage}%` }}
                                            ></div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    ) : (
                        <div className="flex flex-col items-center justify-center py-6 text-gray-300">
                            <i className="ri-shopping-bag-3-line text-4xl mb-2 opacity-50"></i>
                            <p className="text-xs font-semibold">Belum ada data pesanan</p>
                        </div>
                    )}
                </div>
            </div>

            {/* Bottom Section: Recent Orders & Stock Alert */}
            <div className="grid grid-cols-1 xl:grid-cols-2 gap-5">
                {/* Recent Orders */}
                <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div className="flex justify-between items-center p-5 border-b border-gray-50">
                        <h3 className="font-bold text-slate-950">Pesanan Terbaru</h3>
                        <Link
                            href="/pesanan"
                            className="text-xs text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1"
                        >
                            Lihat Semua <i className="ri-arrow-right-line"></i>
                        </Link>
                    </div>

                    {pesananTerbaru.length > 0 ? (
                        <div className="divide-y divide-gray-50">
                            {pesananTerbaru.map((pesanan) => (
                                <div key={pesanan.id} className="flex justify-between items-center p-4 hover:bg-slate-50 transition-all">
                                    <div className="flex items-center gap-3">
                                        <div className="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                                            <i className="ri-shopping-bag-3-line"></i>
                                        </div>
                                        <div>
                                            <p className="text-sm font-bold text-slate-900">{pesanan.kode_pesanan}</p>
                                            <p className="text-xs text-gray-400">{pesanan.customer?.nama || '-'}</p>
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <p className="text-sm font-extrabold text-slate-900">
                                            {formatRupiah(Number(pesanan.total_harga))}
                                        </p>
                                        <span className={`inline-block text-[9px] font-bold uppercase px-2 py-0.5 rounded-full mt-1 ${
                                            pesanan.status === 'pending' ? 'bg-amber-100 text-amber-800' :
                                            pesanan.status === 'diproses' ? 'bg-blue-100 text-blue-800' :
                                            pesanan.status === 'produksi' ? 'bg-purple-100 text-purple-800' :
                                            pesanan.status === 'selesai' ? 'bg-emerald-100 text-emerald-800' :
                                            'bg-slate-100 text-slate-800'
                                        }`}>
                                            {pesanan.status}
                                        </span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="flex flex-col items-center justify-center py-10 text-gray-300">
                            <i className="ri-shopping-bag-3-line text-4xl mb-2 opacity-50"></i>
                            <p className="text-xs font-semibold">Belum ada pesanan masuk</p>
                        </div>
                    )}
                </div>

                {/* Stock Alert */}
                <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div className="flex justify-between items-center p-5 border-b border-gray-50">
                        <h3 className="font-bold text-slate-950 flex items-center gap-2">
                            <i className="ri-alert-line text-amber-500"></i> Alert Stok
                        </h3>
                        <Link
                            href="/stok-produk"
                            className="text-xs text-blue-600 hover:text-blue-700 font-bold"
                        >
                            Kelola Stok
                        </Link>
                    </div>

                    {stokProdukAlert.length > 0 || stokBahanAlert.length > 0 ? (
                        <div className="divide-y divide-gray-50 max-h-72 overflow-y-auto">
                            {stokProdukAlert.map((stok) => (
                                <div key={stok.id} className="flex justify-between items-center p-4 hover:bg-slate-50 transition-all">
                                    <div className="flex items-center gap-3">
                                        <div className={`w-8 h-8 rounded-lg flex items-center justify-center ${
                                            stok.jumlah_stok <= 0 ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600'
                                        }`}>
                                            <i className="ri-alert-line text-sm"></i>
                                        </div>
                                        <div>
                                            <p className="text-sm font-bold text-slate-900">{stok.produk?.nama_produk}</p>
                                            <p className="text-xs text-gray-400">
                                                Produk &middot; Stok: {stok.jumlah_stok} / Min: {stok.stok_minimum}
                                            </p>
                                        </div>
                                    </div>
                                    <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${
                                        stok.jumlah_stok <= 0 ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'
                                    }`}>
                                        {stok.jumlah_stok <= 0 ? 'Habis' : 'Menipis'}
                                    </span>
                                </div>
                            ))}

                            {stokBahanAlert.map((stok) => (
                                <div key={stok.id} className="flex justify-between items-center p-4 hover:bg-slate-50 transition-all">
                                    <div className="flex items-center gap-3">
                                        <div className={`w-8 h-8 rounded-lg flex items-center justify-center ${
                                            stok.jumlah_stok <= 0 ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600'
                                        }`}>
                                            <i className="ri-box-3-line text-sm"></i>
                                        </div>
                                        <div>
                                            <p className="text-sm font-bold text-slate-900">{stok.bahan_baku?.nama_bahan}</p>
                                            <p className="text-xs text-gray-400">
                                                Bahan Baku &middot; Stok: {stok.jumlah_stok} {stok.satuan} / Min: {stok.stok_minimum}
                                            </p>
                                        </div>
                                    </div>
                                    <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${
                                        stok.jumlah_stok <= 0 ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'
                                    }`}>
                                        {stok.jumlah_stok <= 0 ? 'Habis' : 'Menipis'}
                                    </span>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="flex flex-col items-center justify-center py-12 text-emerald-500">
                            <i className="ri-checkbox-circle-line text-5xl mb-2"></i>
                            <h4 className="font-bold text-sm">Stok Aman!</h4>
                            <p className="text-xs text-gray-400">Seluruh stok berada di atas batas minimum</p>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
