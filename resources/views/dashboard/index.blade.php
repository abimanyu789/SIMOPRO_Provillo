@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Ringkasan data operasional Provillo')

@section('content')

{{-- ========================
     STAT CARDS UTAMA
     ======================== --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">

    {{-- Total Pesanan Aktif --}}
    <div class="stat-card border border-blue-50">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                <i class="ri-shopping-bag-3-line text-2xl text-blue-600"></i>
            </div>
            <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">Aktif</span>
        </div>
        <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ number_format($totalPesananAktif) }}</h3>
        <p class="text-sm text-gray-500">Total Pesanan Aktif</p>
    </div>

    {{-- Pemasukan Bulan Ini --}}
    <div class="stat-card border border-green-50">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                <i class="ri-arrow-up-circle-line text-2xl text-green-600"></i>
            </div>
            <span class="text-xs font-semibold text-green-600 bg-green-50 px-2.5 py-1 rounded-full">Bulan Ini</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ 'Rp ' . number_format($pemasukanBulanIni, 0, ',', '.') }}</h3>
        <p class="text-sm text-gray-500">Total Pemasukan</p>
    </div>

    {{-- Pengeluaran Bulan Ini --}}
    <div class="stat-card border border-red-50">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center">
                <i class="ri-arrow-down-circle-line text-2xl text-red-500"></i>
            </div>
            <span class="text-xs font-semibold text-red-500 bg-red-50 px-2.5 py-1 rounded-full">Bulan Ini</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ 'Rp ' . number_format($pengeluaranBulanIni, 0, ',', '.') }}</h3>
        <p class="text-sm text-gray-500">Total Pengeluaran</p>
    </div>

    {{-- Saldo Total --}}
    <div class="stat-card border {{ $saldoTotal >= 0 ? 'border-emerald-50' : 'border-red-100' }}">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 {{ $saldoTotal >= 0 ? 'bg-emerald-50' : 'bg-red-50' }} rounded-xl flex items-center justify-center">
                <i class="ri-wallet-3-line text-2xl {{ $saldoTotal >= 0 ? 'text-emerald-600' : 'text-red-500' }}"></i>
            </div>
            <span class="text-xs font-semibold {{ $saldoTotal >= 0 ? 'text-emerald-600 bg-emerald-50' : 'text-red-500 bg-red-50' }} px-2.5 py-1 rounded-full">
                {{ $saldoTotal >= 0 ? 'Surplus' : 'Defisit' }}
            </span>
        </div>
        <h3 class="text-2xl font-bold {{ $saldoTotal >= 0 ? 'text-gray-900' : 'text-red-600' }} mb-1">
            {{ 'Rp ' . number_format(abs($saldoTotal), 0, ',', '.') }}
        </h3>
        <p class="text-sm text-gray-500">Saldo Arus Kas</p>
    </div>
</div>

{{-- ========================
     STAT CARDS SEKUNDER
     ======================== --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="card p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="ri-shoe-line text-xl text-purple-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total Produk</p>
            <p class="text-xl font-bold text-gray-900">{{ $totalProduk }}</p>
        </div>
    </div>
    <div class="card p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="ri-team-line text-xl text-orange-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Karyawan Aktif</p>
            <p class="text-xl font-bold text-gray-900">{{ $totalKaryawan }}</p>
        </div>
    </div>
    <div class="card p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-teal-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="ri-user-star-line text-xl text-teal-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total Customer</p>
            <p class="text-xl font-bold text-gray-900">{{ $totalCustomer }}</p>
        </div>
    </div>
    <div class="card p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="ri-tools-line text-xl text-indigo-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Produksi Hari Ini</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($produksiHariIni) }} <span class="text-sm font-normal text-gray-400">psg</span></p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-6">

    {{-- ========================
         GRAFIK ARUS KAS
         ======================== --}}
    <div class="xl:col-span-2 card p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-bold text-gray-900">Grafik Arus Kas</h3>
                <p class="text-xs text-gray-500 mt-0.5">Pemasukan & pengeluaran tahun {{ date('Y') }}</p>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-full bg-blue-500"></div><span class="text-gray-500">Pemasukan</span></div>
                <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-full bg-red-400"></div><span class="text-gray-500">Pengeluaran</span></div>
            </div>
        </div>
        <canvas id="chartArusKas" height="100"></canvas>
    </div>

    {{-- ========================
         DISTRIBUSI STATUS PESANAN
         ======================== --}}
    <div class="card p-6">
        <div class="mb-5">
            <h3 class="font-bold text-gray-900">Status Pesanan</h3>
            <p class="text-xs text-gray-500 mt-0.5">Distribusi status pesanan saat ini</p>
        </div>
        @if($statusPesanan->sum() > 0)
            <canvas id="chartStatusPesanan" height="180"></canvas>
            <div class="mt-4 space-y-2">
                @php
                    $statusColors = [
                        'pending'  => '#f59e0b',
                        'diproses' => '#3b82f6',
                        'produksi' => '#8b5cf6',
                        'selesai'  => '#22c55e',
                        'closed'   => '#94a3b8',
                    ];
                @endphp
                @foreach($statusPesanan as $status => $count)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full" style="background: {{ $statusColors[$status] ?? '#94a3b8' }}"></div>
                            <span class="text-gray-600 capitalize">{{ $status }}</span>
                        </div>
                        <span class="font-semibold text-gray-900">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state py-10">
                <i class="ri-shopping-bag-3-line"></i>
                <p>Belum ada data pesanan</p>
            </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

    {{-- ========================
         PESANAN TERBARU
         ======================== --}}
    <div class="card">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">Pesanan Terbaru</h3>
            <a href="{{ route('pesanan.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
                Lihat Semua <i class="ri-arrow-right-line"></i>
            </a>
        </div>
        @if($pesananTerbaru->count() > 0)
            <div class="divide-y divide-gray-50">
                @foreach($pesananTerbaru as $pesanan)
                    <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition-colors">
                        <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="ri-shopping-bag-3-line text-blue-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $pesanan->kode_pesanan }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $pesanan->customer?->nama ?? '-' }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-bold text-gray-900">{{ 'Rp ' . number_format($pesanan->total_harga, 0, ',', '.') }}</p>
                            @php
                                $badgeClass = match($pesanan->status) {
                                    'pending'  => 'badge-warning',
                                    'diproses' => 'badge-info',
                                    'produksi' => 'badge-primary',
                                    'selesai'  => 'badge-success',
                                    'closed'   => 'badge-secondary',
                                    default    => 'badge-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} mt-1">{{ ucfirst($pesanan->status) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="ri-shopping-bag-3-line"></i>
                <h3>Belum Ada Pesanan</h3>
                <p>Pesanan yang masuk akan tampil di sini.</p>
            </div>
        @endif
    </div>

    {{-- ========================
         ALERT STOK MENIPIS
         ======================== --}}
    <div class="card">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                <i class="ri-alert-line text-orange-500"></i>
                Alert Stok
            </h3>
            <a href="{{ route('stok-produk.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                Kelola Stok
            </a>
        </div>
        @if($stokProdukAlert->count() > 0 || $stokBahanAlert->count() > 0)
            <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
                @foreach($stokProdukAlert as $stok)
                    <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50">
                        <div class="w-8 h-8 {{ $stok->jumlah_stok <= 0 ? 'bg-red-50' : 'bg-yellow-50' }} rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="ri-alert-line {{ $stok->jumlah_stok <= 0 ? 'text-red-500' : 'text-yellow-500' }} text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $stok->produk?->nama_produk }}</p>
                            <p class="text-xs text-gray-500">Produk · Stok: {{ $stok->jumlah_stok }} / Min: {{ $stok->stok_minimum }}</p>
                        </div>
                        <span class="badge {{ $stok->jumlah_stok <= 0 ? 'badge-danger' : 'badge-warning' }} flex-shrink-0">
                            {{ $stok->jumlah_stok <= 0 ? 'Habis' : 'Menipis' }}
                        </span>
                    </div>
                @endforeach
                @foreach($stokBahanAlert as $stok)
                    <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50">
                        <div class="w-8 h-8 {{ $stok->jumlah_stok <= 0 ? 'bg-red-50' : 'bg-yellow-50' }} rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="ri-box-3-line {{ $stok->jumlah_stok <= 0 ? 'text-red-500' : 'text-yellow-500' }} text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $stok->bahanBaku?->nama_bahan }}</p>
                            <p class="text-xs text-gray-500">Bahan Baku · Stok: {{ $stok->jumlah_stok }} / Min: {{ $stok->stok_minimum }}</p>
                        </div>
                        <span class="badge {{ $stok->jumlah_stok <= 0 ? 'badge-danger' : 'badge-warning' }} flex-shrink-0">
                            {{ $stok->jumlah_stok <= 0 ? 'Habis' : 'Menipis' }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state py-10">
                <i class="ri-checkbox-circle-line text-green-400" style="opacity:1"></i>
                <h3 class="text-green-600">Stok Aman!</h3>
                <p>Semua stok berada di atas batas minimum.</p>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
/**
 * Inisialisasi Chart.js untuk grafik arus kas bulanan.
 * Data diambil dari controller DashboardController.
 */
const ctxArusKas = document.getElementById('chartArusKas');
if (ctxArusKas) {
    new Chart(ctxArusKas, {
        type: 'bar',
        data: {
            labels: @json($chartData['labels']),
            datasets: [
                {
                    label: 'Pemasukan',
                    data: @json($chartData['pemasukan']),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Pengeluaran',
                    data: @json($chartData['pengeluaran']),
                    backgroundColor: 'rgba(248, 113, 113, 0.7)',
                    borderRadius: 6,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, border: { display: false } },
                y: {
                    grid: { color: '#f1f5f9' },
                    border: { display: false },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                        }
                    }
                }
            }
        }
    });
}

/**
 * Inisialisasi Chart.js untuk distribusi status pesanan.
 */
const ctxStatus = document.getElementById('chartStatusPesanan');
if (ctxStatus) {
    const statusLabels = @json($statusPesanan->keys());
    const statusData = @json($statusPesanan->values());
    const statusColors = ['#f59e0b', '#3b82f6', '#8b5cf6', '#22c55e', '#94a3b8'];

    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: statusLabels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
            datasets: [{
                data: statusData,
                backgroundColor: statusColors.slice(0, statusData.length),
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.label + ': ' + context.parsed + ' pesanan';
                        }
                    }
                }
            }
        }
    });
}
</script>
@endpush
