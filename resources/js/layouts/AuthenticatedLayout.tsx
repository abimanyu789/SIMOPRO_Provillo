import React, { useState, useEffect } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Toast } from '@/components/Toast';

type Props = {
    children: React.ReactNode;
    title?: string;
    pageTitle?: string;
    breadcrumb?: string;
    headerActions?: React.ReactNode;
};

export const AuthenticatedLayout: React.FC<Props> = ({
    children,
    title,
    pageTitle,
    breadcrumb,
    headerActions,
}) => {
    const { props } = usePage<PageProps>();
    const user = props.auth.user;
    const flash = props.flash;
    const settings = props.pengaturanUsaha;

    const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);
    const [sidebarOpen, setSidebarOpen] = useState(false);

    // Collapsible states
    const [dataMasterOpen, setDataMasterOpen] = useState(() => {
        return ['/produk', '/bahan-baku', '/karyawan', '/customer'].some((path) =>
            window.location.pathname.startsWith(path)
        );
    });
    const [stokOpen, setStokOpen] = useState(() => {
        return ['/stok-produk', '/stok-bahan-baku'].some((path) =>
            window.location.pathname.startsWith(path)
        );
    });

    useEffect(() => {
        if (flash?.success) {
            setToast({ message: flash.success, type: 'success' });
        } else if (flash?.error) {
            setToast({ message: flash.error, type: 'error' });
        }
    }, [flash]);

    const handleLogout = (e: React.FormEvent) => {
        e.preventDefault();
        router.post('/logout');
    };

    const isUrl = (url: string) => {
        return window.location.pathname.startsWith(url);
    };

    return (
        <div className="bg-gray-50 font-sans antialiased min-h-screen text-slate-800">
            {toast && (
                <Toast
                    message={toast.message}
                    type={toast.type}
                    onClose={() => setToast(null)}
                />
            )}

            {/* Sidebar Desktop */}
            <aside
                className={`fixed top-0 left-0 h-full w-64 flex flex-col z-40 overflow-hidden transition-transform duration-300 bg-slate-900 border-r border-slate-800 lg:translate-x-0 ${
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                {/* Brand Header */}
                <div className="flex items-center gap-3 px-6 py-5 border-b border-slate-800">
                    {settings?.logo_url ? (
                        <img
                            src={settings.logo_url}
                            alt="Logo"
                            className="h-9 w-9 rounded-lg object-cover"
                        />
                    ) : (
                        <div className="h-9 w-9 bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg flex items-center justify-center">
                            <i className="ri-shoe-line text-white text-lg"></i>
                        </div>
                    )}
                    <div>
                        <h1 className="text-white font-bold text-sm leading-tight">
                            {settings?.nama_usaha || 'Provillo Admin'}
                        </h1>
                        <p className="text-blue-400 text-xs font-semibold">SIMOPRO System</p>
                    </div>
                </div>

                {/* Sidebar Navigation */}
                <nav className="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                    {/* Dashboard */}
                    <Link
                        href="/dashboard"
                        className={`flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all ${
                            window.location.pathname === '/dashboard'
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                        }`}
                    >
                        <i className="ri-dashboard-3-line text-lg"></i>
                        <span>Dashboard</span>
                    </Link>

                    {/* MAIN MENU Label */}
                    <p className="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-4 pt-4 pb-2">
                        MAIN MENU
                    </p>

                    {/* Collapsible: Data Master */}
                    <div>
                        <button
                            onClick={() => setDataMasterOpen(!dataMasterOpen)}
                            className={`w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-semibold transition-all ${
                                ['/produk', '/bahan-baku', '/karyawan', '/customer'].some((path) =>
                                    isUrl(path)
                                )
                                    ? 'text-white'
                                    : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                            }`}
                        >
                            <div className="flex items-center gap-3">
                                <i className="ri-folder-open-line text-lg"></i>
                                <span>Data Master</span>
                            </div>
                            <i
                                className={`ri-arrow-down-s-line transition-transform duration-200 ${
                                    dataMasterOpen ? 'rotate-180' : ''
                                }`}
                            ></i>
                        </button>

                        <div
                            className={`mt-1 pl-6 space-y-1 overflow-hidden transition-all duration-300 ${
                                dataMasterOpen ? 'max-h-52 opacity-100' : 'max-h-0 opacity-0'
                            }`}
                        >
                            <Link
                                href="/produk"
                                className={`flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-medium transition-all ${
                                    isUrl('/produk')
                                        ? 'bg-blue-600/10 text-blue-400 font-semibold'
                                        : 'text-slate-400 hover:text-white'
                                }`}
                            >
                                <i className="ri-shoe-line"></i>
                                <span>Produk</span>
                            </Link>
                            <Link
                                href="/bahan-baku"
                                className={`flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-medium transition-all ${
                                    isUrl('/bahan-baku')
                                        ? 'bg-blue-600/10 text-blue-400 font-semibold'
                                        : 'text-slate-400 hover:text-white'
                                }`}
                            >
                                <i className="ri-box-3-line"></i>
                                <span>Bahan Baku</span>
                            </Link>
                            <Link
                                href="/karyawan"
                                className={`flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-medium transition-all ${
                                    isUrl('/karyawan')
                                        ? 'bg-blue-600/10 text-blue-400 font-semibold'
                                        : 'text-slate-400 hover:text-white'
                                }`}
                            >
                                <i className="ri-team-line"></i>
                                <span>Karyawan</span>
                            </Link>
                            <Link
                                href="/customer"
                                className={`flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-medium transition-all ${
                                    isUrl('/customer')
                                        ? 'bg-blue-600/10 text-blue-400 font-semibold'
                                        : 'text-slate-400 hover:text-white'
                                }`}
                            >
                                <i className="ri-user-star-line"></i>
                                <span>Customer</span>
                            </Link>
                        </div>
                    </div>

                    {/* Operational */}
                    <Link
                        href="/pesanan"
                        className={`flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all ${
                            isUrl('/pesanan')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                        }`}
                    >
                        <i className="ri-shopping-bag-3-line text-lg"></i>
                        <span>Pesanan</span>
                    </Link>

                    <Link
                        href="/produksi"
                        className={`flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all ${
                            isUrl('/produksi')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                        }`}
                    >
                        <i className="ri-tools-line text-lg"></i>
                        <span>Produksi</span>
                    </Link>

                    {/* Collapsible: Stok */}
                    <div>
                        <button
                            onClick={() => setStokOpen(!stokOpen)}
                            className={`w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-semibold transition-all ${
                                ['/stok-produk', '/stok-bahan-baku'].some((path) => isUrl(path))
                                    ? 'text-white'
                                    : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                            }`}
                        >
                            <div className="flex items-center gap-3">
                                <i className="ri-archive-drawer-line text-lg"></i>
                                <span>Stok</span>
                            </div>
                            <i
                                className={`ri-arrow-down-s-line transition-transform duration-200 ${
                                    stokOpen ? 'rotate-180' : ''
                                }`}
                            ></i>
                        </button>

                        <div
                            className={`mt-1 pl-6 space-y-1 overflow-hidden transition-all duration-300 ${
                                stokOpen ? 'max-h-24 opacity-100' : 'max-h-0 opacity-0'
                            }`}
                        >
                            <Link
                                href="/stok-bahan-baku"
                                className={`flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-medium transition-all ${
                                    isUrl('/stok-bahan-baku')
                                        ? 'bg-blue-600/10 text-blue-400 font-semibold'
                                        : 'text-slate-400 hover:text-white'
                                }`}
                            >
                                <i className="ri-stack-line"></i>
                                <span>Bahan Baku</span>
                            </Link>
                            <Link
                                href="/stok-produk"
                                className={`flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-medium transition-all ${
                                    isUrl('/stok-produk')
                                        ? 'bg-blue-600/10 text-blue-400 font-semibold'
                                        : 'text-slate-400 hover:text-white'
                                }`}
                            >
                                <i className="ri-archive-drawer-line"></i>
                                <span>Produk Jadi</span>
                            </Link>
                        </div>
                    </div>

                    {/* Keuangan */}
                    <Link
                        href="/arus-kas"
                        className={`flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all ${
                            isUrl('/arus-kas')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                        }`}
                    >
                        <i className="ri-money-dollar-circle-line text-lg"></i>
                        <span>Arus Kas</span>
                    </Link>

                    {/* Pengaturan */}
                    <Link
                        href="/pengaturan"
                        className={`flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all ${
                            isUrl('/pengaturan')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                        }`}
                    >
                        <i className="ri-settings-3-line text-lg"></i>
                        <span>Pengaturan</span>
                    </Link>
                </nav>

                {/* User Profile Card */}
                <div className="border-t border-slate-800 p-4">
                    <div className="flex items-center gap-3">
                        {user.foto_url ? (
                            <img
                                src={user.foto_url}
                                className="w-9 h-9 rounded-full object-cover ring-2 ring-blue-500/40"
                                alt=""
                            />
                        ) : (
                            <div className="w-9 h-9 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                {user.name ? user.name.charAt(0).toUpperCase() : 'A'}
                            </div>
                        )}
                        <div className="flex-1 min-w-0">
                            <p className="text-white text-sm font-semibold truncate">
                                {user.name || 'Admin User'}
                            </p>
                            <p className="text-slate-400 text-xs truncate">
                                {user.jabatan || 'Super Admin'}
                            </p>
                        </div>
                        <form onSubmit={handleLogout}>
                            <button
                                type="submit"
                                className="text-slate-400 hover:text-red-400 transition-colors cursor-pointer"
                                title="Logout"
                            >
                                <i className="ri-logout-box-r-line text-lg"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            {/* Mobile Sidebar Overlay */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 bg-black/50 z-30 lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                ></div>
            )}

            {/* Main Content Area */}
            <main className="lg:ml-64 min-h-screen flex flex-col transition-all duration-300">
                {/* Header */}
                <header className="sticky top-0 z-20 bg-white/80 backdrop-blur-md border-b border-gray-100 px-6 py-4">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex items-center gap-3">
                            <button
                                onClick={() => setSidebarOpen(true)}
                                className="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                <i className="ri-menu-line text-xl text-slate-600"></i>
                            </button>
                            <div>
                                <h2 className="text-lg font-bold text-gray-900">
                                    {pageTitle || 'Dashboard'}
                                </h2>
                                {breadcrumb && (
                                    <p className="text-xs text-gray-400 mt-0.5">{breadcrumb}</p>
                                )}
                            </div>
                        </div>
                        <div className="flex items-center gap-3">{headerActions}</div>
                    </div>
                </header>

                {/* Main Component children */}
                <div className="flex-1 p-6">{children}</div>
            </main>
        </div>
    );
};
