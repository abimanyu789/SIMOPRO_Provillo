<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * PengaturanController - Pengaturan Aplikasi dan Profil Pengguna.
 */
class PengaturanController extends Controller
{
    public function index(): View
    {
        $pengaturan = Pengaturan::getSetting();
        $user = Auth::user();
        return view('pengaturan.index', compact('pengaturan', 'user'));
    }

    /**
     * Update profil pengguna (nama, jabatan, foto).
     */
    public function updateProfil(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'foto'    => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'name.required' => 'Nama wajib diisi.',
        ]);

        if ($request->hasFile('foto')) {
            if ($user->foto) Storage::disk('public')->delete($user->foto);
            $validated['foto'] = $request->file('foto')->store('profil', 'public');
        }

        $user->update($validated);
        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update password pengguna.
     * Validasi password lama sebelum mengubah ke baru.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password_lama' => ['required'],
            'password_baru' => ['required', 'min:8', 'confirmed'],
        ], [
            'password_lama.required'      => 'Password saat ini wajib diisi.',
            'password_baru.required'      => 'Password baru wajib diisi.',
            'password_baru.min'           => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed'     => 'Konfirmasi password tidak cocok.',
        ]);

        $user = Auth::user();

        // Verifikasi password lama
        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Password saat ini tidak sesuai.']);
        }

        $user->update(['password' => Hash::make($request->password_baru)]);
        return back()->with('success', 'Password berhasil diperbarui.');
    }

    /**
     * Update informasi usaha dan logo.
     */
    public function updateUsaha(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_usaha'       => ['required', 'string', 'max:255'],
            'deskripsi_usaha'  => ['nullable', 'string'],
            'alamat'           => ['nullable', 'string'],
            'no_hp'            => ['nullable', 'string', 'max:20'],
            'email'            => ['nullable', 'email'],
            'logo'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg', 'max:2048'],
        ], [
            'nama_usaha.required' => 'Nama usaha wajib diisi.',
        ]);

        $pengaturan = Pengaturan::getSetting();

        if ($request->hasFile('logo')) {
            if ($pengaturan->logo) Storage::disk('public')->delete($pengaturan->logo);
            $validated['logo'] = $request->file('logo')->store('logo', 'public');
        }

        $pengaturan->update($validated);
        return back()->with('success', 'Informasi usaha berhasil diperbarui.');
    }
}
