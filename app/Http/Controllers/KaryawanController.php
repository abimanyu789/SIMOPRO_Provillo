<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KaryawanExport;
use App\Imports\KaryawanImport;

/**
 * KaryawanController - Data Master Karyawan Provillo.
 */
class KaryawanController extends Controller
{
    public function index(Request $request): View
    {
        $query = Karyawan::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_karyawan', 'like', '%' . $request->search . '%')
                    ->orWhere('posisi', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('posisi')) {
            $query->where('posisi', $request->posisi);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $karyawan = $query->latest()->paginate(10)->withQueryString();
        $posisiList = Karyawan::distinct()->pluck('posisi');

        return view('karyawan.index', compact('karyawan', 'posisiList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'             => ['required', 'string', 'max:255'],
            'posisi'           => ['required', 'string', 'max:100'],
            'divisi'           => ['nullable', 'string', 'max:100'],
            'tanggal_lahir'    => ['nullable', 'date'],
            'no_hp'            => ['nullable', 'string', 'max:20'],
            'email'            => ['nullable', 'email', 'max:255'],
            'alamat'           => ['nullable', 'string'],
            'tanggal_bergabung'=> ['nullable', 'date'],
            'no_rekening'      => ['nullable', 'string', 'max:50'],
            'status'           => ['required', 'in:aktif,nonaktif'],
            'deskripsi'        => ['nullable', 'string'],
            'foto'             => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'nama.required'   => 'Nama karyawan wajib diisi.',
            'posisi.required' => 'Posisi wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        $validated['kode_karyawan'] = $this->generateKodeKaryawan();

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('karyawan', 'public');
        }

        Karyawan::create($validated);

        return redirect()->route('karyawan.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function show(Karyawan $karyawan): View
    {
        return view('karyawan.show', compact('karyawan'));
    }

    public function update(Request $request, Karyawan $karyawan): RedirectResponse
    {
        $validated = $request->validate([
            'nama'             => ['required', 'string', 'max:255'],
            'posisi'           => ['required', 'string', 'max:100'],
            'divisi'           => ['nullable', 'string', 'max:100'],
            'tanggal_lahir'    => ['nullable', 'date'],
            'no_hp'            => ['nullable', 'string', 'max:20'],
            'email'            => ['nullable', 'email'],
            'alamat'           => ['nullable', 'string'],
            'tanggal_bergabung'=> ['nullable', 'date'],
            'no_rekening'      => ['nullable', 'string', 'max:50'],
            'status'           => ['required', 'in:aktif,nonaktif'],
            'deskripsi'        => ['nullable', 'string'],
            'foto'             => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            if ($karyawan->foto) Storage::disk('public')->delete($karyawan->foto);
            $validated['foto'] = $request->file('foto')->store('karyawan', 'public');
        }

        $karyawan->update($validated);

        return redirect()->route('karyawan.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan): RedirectResponse
    {
        if ($karyawan->foto) Storage::disk('public')->delete($karyawan->foto);
        $karyawan->delete();
        return redirect()->route('karyawan.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new KaryawanExport, 'data-karyawan-' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $karyawan = Karyawan::all();
        $pdf = Pdf::loadView('exports.karyawan-pdf', compact('karyawan'))->setPaper('a4', 'landscape');
        return $pdf->download('data-karyawan-' . date('Y-m-d') . '.pdf');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'mimes:xlsx,xls']]);
        Excel::import(new KaryawanImport, $request->file('file'));
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil diimport.');
    }

    private function generateKodeKaryawan(): string
    {
        $prefix = 'KRY-' . date('Ym') . '-';
        $last = Karyawan::withTrashed()->where('kode_karyawan', 'like', $prefix . '%')
            ->orderByDesc('kode_karyawan')->first();
        $number = $last ? (int) substr($last->kode_karyawan, -4) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
