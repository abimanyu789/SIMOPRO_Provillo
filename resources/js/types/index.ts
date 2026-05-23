export type User = {
    id: number;
    name: string;
    email: string;
    foto?: string;
    foto_url?: string;
    jabatan?: string;
    created_at: string;
    updated_at: string;
};

export type Pengaturan = {
    id: number;
    nama_usaha: string;
    deskripsi_usaha?: string;
    alamat?: string;
    no_hp?: string;
    email?: string;
    logo?: string;
    logo_url?: string;
    created_at?: string;
    updated_at?: string;
};

export type Produk = {
    id: number;
    kode_produk: string;
    nama_produk: string;
    kategori: string;
    deskripsi?: string;
    harga_jual: number;
    foto?: string;
    foto_url?: string;
    stok?: StokProduk;
    created_at?: string;
    updated_at?: string;
};

export type BahanBaku = {
    id: number;
    kode_bahan: string;
    nama_bahan: string;
    kategori: string;
    satuan: string;
    harga_beli: number;
    deskripsi?: string;
    stok?: StokBahanBaku;
    created_at?: string;
    updated_at?: string;
};

export type Karyawan = {
    id: number;
    kode_karyawan: string;
    nama: string;
    jabatan: string;
    no_hp?: string;
    alamat?: string;
    status: 'aktif' | 'nonaktif';
    foto?: string;
    foto_url?: string;
    created_at?: string;
    updated_at?: string;
};

export type Customer = {
    id: number;
    kode_customer: string;
    nama: string;
    no_hp?: string;
    email?: string;
    alamat?: string;
    kota?: string;
    provinsi?: string;
    kode_pos?: string;
    deskripsi?: string;
    pesanan_count?: number;
    created_at?: string;
    updated_at?: string;
};

export type Pesanan = {
    id: number;
    kode_pesanan: string;
    customer_id: number;
    tanggal_pesanan: string;
    tanggal_kirim?: string;
    status: 'pending' | 'diproses' | 'produksi' | 'selesai' | 'closed';
    status_label: string;
    status_color: string;
    total_harga: number;
    total_format: string;
    catatan?: string;
    customer?: Customer;
    detail_pesanan?: DetailPesanan[];
    created_at?: string;
    updated_at?: string;
};

export type DetailPesanan = {
    id: number;
    pesanan_id: number;
    produk_id: number;
    jumlah: number;
    harga_satuan: number;
    subtotal: number;
    produk?: Produk;
};

export type Produksi = {
    id: number;
    kode_produksi: string;
    karyawan_id: number;
    pesanan_id?: number;
    tanggal_produksi: string;
    jumlah_produksi: number;
    upah_per_item: number;
    total_upah: number;
    total_upah_format: string;
    catatan?: string;
    karyawan?: Karyawan;
    pesanan?: Pesanan;
    created_at?: string;
    updated_at?: string;
};

export type StokProduk = {
    id: number;
    produk_id: number;
    jumlah_stok: number;
    stok_minimum: number;
    status_stok: 'tersedia' | 'menipis' | 'habis';
    status_color: string;
    produk?: Produk;
    created_at?: string;
    updated_at?: string;
};

export type StokBahanBaku = {
    id: number;
    bahan_baku_id: number;
    jumlah_stok: number;
    stok_minimum: number;
    satuan: string;
    status_stok: 'tersedia' | 'menipis' | 'habis';
    status_color: string;
    bahan_baku?: BahanBaku;
    created_at?: string;
    updated_at?: string;
};

export type ArusKas = {
    id: number;
    kode_transaksi: string;
    tipe: 'pemasukan' | 'pengeluaran';
    jumlah: number;
    jumlah_format: string;
    tanggal: string;
    kategori: string;
    keterangan?: string;
    created_at?: string;
    updated_at?: string;
};

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
    errors: Record<string, string>;
    flash: {
        success?: string;
        error?: string;
    };
    pengaturanUsaha?: Pengaturan;
};

export type PaginatedData<T> = {
    data: T[];
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
};
