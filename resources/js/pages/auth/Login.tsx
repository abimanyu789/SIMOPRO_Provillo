import React, { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';

export default function Login() {
    const { pengaturanUsaha } = usePage<any>().props;

    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const [showPassword, setShowPassword] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 flex items-center justify-center p-4 font-sans relative overflow-hidden">
            <Head title="Login" />

            {/* Background Decorative Circles */}
            <div className="absolute inset-0 overflow-hidden pointer-events-none">
                <div className="absolute top-20 left-10 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl"></div>
                <div className="absolute bottom-20 right-10 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl"></div>
                <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-blue-900/20 rounded-full blur-3xl"></div>
            </div>

            <div className="relative w-full max-w-4xl grid lg:grid-cols-2 gap-0 rounded-2xl overflow-hidden shadow-2xl">
                {/* Left Panel - Branding */}
                <div className="hidden lg:flex flex-col items-center justify-center p-12 text-white bg-gradient-to-br from-blue-700 via-blue-800 to-blue-600">
                    <div className="mb-8 animate-bounce duration-[3000ms]">
                        <div className="w-24 h-24 bg-white/10 rounded-2xl flex items-center justify-center border border-white/20 overflow-hidden">
                            {pengaturanUsaha?.logo ? (
                                <img src={`/storage/${pengaturanUsaha.logo}`} alt="Logo" className="w-full h-full object-contain p-2" />
                            ) : (
                                <i className="ri-shoe-line text-5xl text-blue-200"></i>
                            )}
                        </div>
                    </div>
                    <h1 className="text-3xl font-bold mb-3">SIMOPRO</h1>
                    <p className="text-blue-200 text-center text-sm leading-relaxed mb-8">
                        Sistem Informasi Manajemen Operasional<br />
                        <span className="font-semibold text-white">Provillo</span> — Produsen Sepatu Berkualitas
                    </p>
                    <div className="space-y-3 w-full max-w-xs">
                        {[
                            { icon: 'ri-bar-chart-box-line', text: 'Dashboard Operasional Real-time' },
                            { icon: 'ri-shopping-bag-3-line', text: 'Manajemen Pesanan & Produksi' },
                            { icon: 'ri-money-dollar-circle-line', text: 'Arus Kas & Laporan Keuangan' },
                            { icon: 'ri-archive-drawer-line', text: 'Manajemen Stok Otomatis' },
                        ].map((item, idx) => (
                            <div key={idx} className="flex items-center gap-3 text-sm text-blue-100">
                                <div className="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i className={`${item.icon} text-blue-300`}></i>
                                </div>
                                {item.text}
                            </div>
                        ))}
                    </div>
                </div>

                {/* Right Panel - Login Form */}
                <div className="bg-white/95 backdrop-blur-md p-10 flex flex-col justify-center">
                    {/* Logo Mobile */}
                    <div className="flex items-center gap-3 mb-8 lg:mb-10">
                        <div className="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center overflow-hidden">
                            {pengaturanUsaha?.logo ? (
                                <img src={`/storage/${pengaturanUsaha.logo}`} alt="Logo" className="w-full h-full object-contain p-1.5" />
                            ) : (
                                <i className="ri-shoe-line text-white text-xl"></i>
                            )}
                        </div>
                        <div>
                            <h2 className="font-bold text-gray-900 text-lg">SIMOPRO</h2>
                            <p className="text-blue-600 text-xs font-semibold">Provillo Management System</p>
                        </div>
                    </div>

                    <h3 className="text-2xl font-bold text-gray-900 mb-1">Selamat Datang!</h3>
                    <p className="text-gray-500 text-sm mb-8">Masuk ke akun Anda untuk melanjutkan.</p>

                    <form onSubmit={handleSubmit} noValidate>
                        {/* Email Field */}
                        <div className="mb-5">
                            <label className="block text-sm font-semibold text-gray-700 mb-2" htmlFor="email">
                                Email
                            </label>
                            <div className="relative">
                                <i className="ri-mail-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="masukkan email anda"
                                    className={`w-full border-1.5 border-gray-200 rounded-xl py-3 pl-12 pr-4 text-sm text-gray-800 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all ${errors.email ? 'border-rose-500 ring-rose-500/10' : ''
                                        }`}
                                />
                            </div>
                            {errors.email && (
                                <p className="mt-1.5 text-xs text-rose-500 flex items-center gap-1">
                                    <i className="ri-error-warning-line"></i> {errors.email}
                                </p>
                            )}
                        </div>

                        {/* Password Field */}
                        <div className="mb-6">
                            <label className="block text-sm font-semibold text-gray-700 mb-2" htmlFor="password">
                                Password
                            </label>
                            <div className="relative">
                                <i className="ri-lock-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                                <input
                                    type={showPassword ? 'text' : 'password'}
                                    id="password"
                                    name="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="Masukkan password"
                                    className={`w-full border-1.5 border-gray-200 rounded-xl py-3 pl-12 pr-12 text-sm text-gray-800 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all ${errors.password ? 'border-rose-500 ring-rose-500/10' : ''
                                        }`}
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword(!showPassword)}
                                    className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <i className={showPassword ? 'ri-eye-off-line' : 'ri-eye-line'}></i>
                                </button>
                            </div>
                            {errors.password && (
                                <p className="mt-1.5 text-xs text-rose-500 flex items-center gap-1">
                                    <i className="ri-error-warning-line"></i> {errors.password}
                                </p>
                            )}
                        </div>

                        {/* Remember Me */}
                        <div className="flex items-center justify-between mb-6">
                            <label className="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                    className="rounded border-gray-300 text-blue-500 focus:ring-blue-500"
                                />
                                Ingat saya
                            </label>
                        </div>

                        {/* Submit Button */}
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-3 px-6 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl transition-all duration-200 hover:shadow-lg hover:shadow-blue-500/30 flex items-center justify-center gap-2 disabled:opacity-50"
                        >
                            <span>{processing ? 'Memproses...' : 'Masuk'}</span>
                            <i className="ri-arrow-right-line"></i>
                        </button>
                    </form>

                    <p className="mt-8 text-center text-xs text-gray-400">
                        &copy; {new Date().getFullYear()} SIMOPRO Provillo. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    );
}
