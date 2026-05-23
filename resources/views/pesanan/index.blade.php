@extends('layouts.app')

@section('title', 'Manajemen Pesanan')
@section('page-title', 'Manajemen Pesanan')
@section('breadcrumb', 'Kelola pesanan pelanggan Provillo')

@section('header-actions')
    <button onclick="openModal('modal-tambah-pesanan')" class="btn btn-primary btn-sm">
        <i class="ri-add-line"></i> Buat Pesanan
    </button>
@endsection

@section('content')

{{-- Status Counter Tabs --}}
<div class="flex gap-2 mb-5 overflow-x-auto pb-1">
    @php
        $allStatuses = ['semua', 'pending', 'diproses', 'produksi', 'selesai', 'closed'];
        $statusColors = [
            'semua'    => 'bg-gray-100 text-gray-700',
            'pending'  => 'bg-yellow-100 text-yellow-700',
            'diproses' => 'bg-blue-100 text-blue-700',
            'produksi' => 'bg-purple-100 text-purple-700',
            'selesai'  => 'bg-green-100 text-green-700',
            'closed'   => 'bg-gray-100 text-gray-500',
        ];
    @endphp
    @foreach($allStatuses as $s)
        <a href="{{ route('pesanan.index', array_merge(request()->query(), ['status' => $s === 'semua' ? null : $s])) }}"
           class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-semibold transition-all
                  {{ (request('status') === $s || ($s === 'semua' && !request('status')))
                     ? 'bg-blue-600 text-white shadow-md shadow-blue-200'
                     : $statusColors[$s] }}">
            {{ ucfirst($s) }}
            @if($s !== 'semua' && isset($statusCount[$s]))
                <span class="ml-1 text-xs opacity-75">({{ $statusCount[$s] }})</span>
            @endif
        </a>
    @endforeach
</div>

{{-- Filter --}}
<div class="card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="form-label">Cari Pesanan</label>
            <div class="relative">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode pesanan atau nama customer..." class="form-input pl-9">
            </div>
        </div>
        <input type="hidden" name="status" value="{{ request('status') }}">
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line"></i> Cari</button>
            @if(request()->hasAny(['search']))
                <a href="{{ route('pesanan.index') }}" class="btn btn-outline btn-sm"><i class="ri-refresh-line"></i> Reset</a>
            @endif
        </div>
    </form>
</div>

{{-- Tabel Pesanan --}}
<div class="card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-900">Daftar Pesanan <span class="text-sm font-normal text-gray-500">({{ $pesanan->total() }} data)</span></h3>
    </div>

    @if($pesanan->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">No</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Kode Pesanan</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Customer</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Tgl Pesanan</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Tgl Kirim</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Total</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Status</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($pesanan as $idx => $order)
                        <tr class="table-row">
                            <td class="px-5 py-4 text-gray-500">{{ $pesanan->firstItem() + $idx }}</td>
                            <td class="px-5 py-4">
                                <code class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded font-mono">{{ $order->kode_pesanan }}</code>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-gray-900">{{ $order->customer?->nama ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ $order->customer?->kota }}</p>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $order->tanggal_pesanan?->format('d M Y') }}</td>
                            <td class="px-5 py-4">
                                @if($order->tanggal_kirim)
                                    <span class="{{ $order->tanggal_kirim->isPast() && $order->status !== 'closed' ? 'text-red-500 font-semibold' : 'text-gray-600' }}">
                                        {{ $order->tanggal_kirim->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-bold text-gray-900">{{ $order->total_format }}</td>
                            <td class="px-5 py-4">
                                @php
                                    $badgeClass = match($order->status) {
                                        'pending'  => 'badge-warning',
                                        'diproses' => 'badge-info',
                                        'produksi' => 'badge-primary',
                                        'selesai'  => 'badge-success',
                                        'closed'   => 'badge-secondary',
                                        default    => 'badge-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $order->status_label }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('pesanan.show', $order) }}" class="btn btn-outline btn-icon btn-sm" title="Detail">
                                        <i class="ri-eye-line text-blue-500"></i>
                                    </a>
                                    <a href="{{ route('pesanan.invoice', $order) }}" class="btn btn-outline btn-icon btn-sm" title="Invoice">
                                        <i class="ri-file-text-line text-purple-500"></i>
                                    </a>
                                    <button onclick="openModal('modal-hapus-pesanan-{{ $order->id }}')" class="btn btn-outline btn-icon btn-sm" title="Hapus">
                                        <i class="ri-delete-bin-line text-red-500"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Modal Hapus --}}
                        <div id="modal-hapus-pesanan-{{ $order->id }}" class="modal-overlay">
                            <div class="modal-box max-w-md mx-4">
                                <div class="p-6 text-center">
                                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="ri-delete-bin-line text-3xl text-red-500"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Pesanan?</h3>
                                    <p class="text-gray-500 text-sm mb-1">Pesanan: <strong>{{ $order->kode_pesanan }}</strong></p>
                                    @if($order->status !== 'pending')
                                        <p class="text-xs text-red-500 mt-3 p-3 bg-red-50 rounded-lg">
                                            <i class="ri-error-warning-line"></i>
                                            Pesanan dengan status "{{ $order->status }}" tidak dapat dihapus.
                                        </p>
                                    @endif
                                </div>
                                <div class="flex gap-3 px-6 py-4 border-t border-gray-100">
                                    @if($order->status === 'pending')
                                        <form method="POST" action="{{ route('pesanan.destroy', $order) }}" class="flex-1">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-full">
                                                <i class="ri-delete-bin-line"></i> Hapus
                                            </button>
                                        </form>
                                    @endif
                                    <button onclick="closeModal('modal-hapus-pesanan-{{ $order->id }}')" class="btn btn-outline flex-1">Tutup</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">{{ $pesanan->links() }}</div>
    @else
        <div class="empty-state">
            <i class="ri-shopping-bag-3-line"></i>
            <h3>Belum Ada Pesanan</h3>
            <p>Pesanan baru akan muncul di sini.</p>
        </div>
    @endif
</div>

{{-- ========================
     MODAL TAMBAH PESANAN
     ======================== --}}
<div id="modal-tambah-pesanan" class="modal-overlay">
    <div class="modal-box max-w-2xl mx-4">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">Buat Pesanan Baru</h3>
            <button onclick="closeModal('modal-tambah-pesanan')" class="text-gray-400 hover:text-gray-600">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('pesanan.store') }}" id="formPesanan">
            @csrf
            <div class="p-5 space-y-5">
                {{-- Info Pesanan --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Customer <span class="text-red-500">*</span></label>
                        <select name="customer_id" class="form-input" required id="customerSelect">
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}">{{ $cust->nama }} ({{ $cust->kota }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tanggal Pesanan <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_pesanan" class="form-input" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="form-label">Target Pengiriman</label>
                        <input type="date" name="tanggal_kirim" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Catatan</label>
                        <input type="text" name="catatan" class="form-input" placeholder="Catatan opsional...">
                    </div>
                </div>

                {{-- Item Pesanan --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="form-label mb-0">Item Produk <span class="text-red-500">*</span></label>
                        <button type="button" onclick="tambahItemPesanan()" class="btn btn-outline btn-sm">
                            <i class="ri-add-line"></i> Tambah Item
                        </button>
                    </div>

                    {{-- Daftar produk dalam JSON untuk JS --}}
                    @php
                        $produkList = \App\Models\Produk::select('id', 'nama_produk', 'harga_jual', 'kode_produk')->get();
                    @endphp
                    <script>
                        const produkData = @json($produkList);
                    </script>

                    <div id="items-container" class="space-y-3">
                        {{-- Item akan ditambahkan via JS --}}
                    </div>

                    {{-- Total Preview --}}
                    <div class="mt-4 p-3 bg-blue-50 rounded-xl flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-700">Total Pesanan:</span>
                        <span id="total-preview" class="text-lg font-bold text-blue-700">Rp 0</span>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-5 py-4 border-t border-gray-100">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="ri-save-line"></i> Simpan Pesanan
                </button>
                <button type="button" onclick="closeModal('modal-tambah-pesanan')" class="btn btn-outline btn-sm">Batal</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
let itemCount = 0;

/**
 * Tambahkan baris item produk pada form pesanan.
 * Setiap item punya dropdown produk, jumlah, ukuran, dan warna.
 */
function tambahItemPesanan() {
    itemCount++;
    const container = document.getElementById('items-container');
    const div = document.createElement('div');
    div.id = `item-${itemCount}`;
    div.className = 'p-4 bg-gray-50 rounded-xl border border-gray-200';
    div.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-semibold text-gray-700">Item #${itemCount}</span>
            <button type="button" onclick="hapusItem('item-${itemCount}')" class="text-red-400 hover:text-red-600">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="col-span-2">
                <label class="form-label text-xs">Produk *</label>
                <select name="items[${itemCount}][produk_id]" class="form-input text-sm" required
                        onchange="updateSubtotal(${itemCount})">
                    <option value="">-- Pilih Produk --</option>
                    ${produkData.map(p => `<option value="${p.id}" data-harga="${p.harga_jual}">${p.nama_produk} (Rp ${Number(p.harga_jual).toLocaleString('id-ID')})</option>`).join('')}
                </select>
            </div>
            <div>
                <label class="form-label text-xs">Jumlah *</label>
                <input type="number" name="items[${itemCount}][jumlah]" class="form-input text-sm" min="1" value="1" required
                       onchange="updateSubtotal(${itemCount})">
            </div>
            <div>
                <label class="form-label text-xs">Subtotal</label>
                <div id="subtotal-${itemCount}" class="form-input text-sm bg-gray-50 font-semibold text-blue-700">Rp 0</div>
            </div>
            <div>
                <label class="form-label text-xs">Ukuran</label>
                <input type="text" name="items[${itemCount}][ukuran]" class="form-input text-sm" placeholder="38, 39, 40...">
            </div>
            <div>
                <label class="form-label text-xs">Warna</label>
                <input type="text" name="items[${itemCount}][warna]" class="form-input text-sm" placeholder="Hitam, Coklat...">
            </div>
        </div>
    `;
    container.appendChild(div);
    updateTotal();
}

/**
 * Hapus baris item pesanan.
 * @param {string} itemId - ID elemen item yang akan dihapus
 */
function hapusItem(itemId) {
    const el = document.getElementById(itemId);
    if (el) {
        el.remove();
        updateTotal();
    }
}

/**
 * Update subtotal untuk item tertentu dan recalculate grand total.
 * @param {number} index - Nomor urut item
 */
function updateSubtotal(index) {
    const select = document.querySelector(`[name="items[${index}][produk_id]"]`);
    const jumlahInput = document.querySelector(`[name="items[${index}][jumlah]"]`);
    const subtotalEl = document.getElementById(`subtotal-${index}`);

    if (!select || !jumlahInput || !subtotalEl) return;

    const selectedOption = select.options[select.selectedIndex];
    const harga = parseFloat(selectedOption?.dataset?.harga || 0);
    const jumlah = parseInt(jumlahInput.value || 0);
    const subtotal = harga * jumlah;

    subtotalEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    updateTotal();
}

/**
 * Hitung dan tampilkan grand total pesanan.
 */
function updateTotal() {
    let total = 0;
    document.querySelectorAll('[id^="subtotal-"]').forEach(el => {
        const text = el.textContent.replace('Rp ', '').replace(/\./g, '').trim();
        total += parseInt(text || 0);
    });
    document.getElementById('total-preview').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

// Tambah item pertama saat modal dibuka
document.getElementById('modal-tambah-pesanan').addEventListener('click', function(e) {
    if (e.target === this && document.getElementById('items-container').children.length === 0) {
        return;
    }
});

// Auto-buka modal jika ada errors
@if($errors->any())
    openModal('modal-tambah-pesanan');
    tambahItemPesanan(); // Tambah item default
@else
    // Tambah item default saat halaman dimuat
    setTimeout(() => {
        const btn = document.querySelector('[onclick="openModal(\'modal-tambah-pesanan\')"]');
        document.getElementById('modal-tambah-pesanan').addEventListener('transitionend', function() {
            if (document.getElementById('items-container').children.length === 0) {
                tambahItemPesanan();
            }
        }, { once: true });
    }, 100);
@endif
</script>
@endpush
