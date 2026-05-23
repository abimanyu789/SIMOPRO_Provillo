<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomerExport;
use App\Imports\CustomerImport;

/**
 * CustomerController - Data Master Pelanggan.
 */
class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::withCount('pesanan');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_customer', 'like', '%' . $request->search . '%')
                    ->orWhere('no_hp', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('kota')) {
            $query->where('kota', $request->kota);
        }

        $customer = $query->latest()->paginate(10)->withQueryString();
        $kotaList = Customer::distinct()->whereNotNull('kota')->pluck('kota');

        return view('customer.index', compact('customer', 'kotaList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'      => ['required', 'string', 'max:255'],
            'no_hp'     => ['nullable', 'string', 'max:20'],
            'email'     => ['nullable', 'email'],
            'alamat'    => ['nullable', 'string'],
            'kota'      => ['nullable', 'string', 'max:100'],
            'provinsi'  => ['nullable', 'string', 'max:100'],
            'kode_pos'  => ['nullable', 'string', 'max:10'],
            'deskripsi' => ['nullable', 'string'],
        ], ['nama.required' => 'Nama pelanggan wajib diisi.']);

        $validated['kode_customer'] = $this->generateKodeCustomer();
        Customer::create($validated);

        return redirect()->route('customer.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Customer $customer): View
    {
        $customer->load(['pesanan' => fn ($q) => $q->latest()->take(5)]);
        return view('customer.show', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'nama'      => ['required', 'string', 'max:255'],
            'no_hp'     => ['nullable', 'string', 'max:20'],
            'email'     => ['nullable', 'email'],
            'alamat'    => ['nullable', 'string'],
            'kota'      => ['nullable', 'string', 'max:100'],
            'provinsi'  => ['nullable', 'string', 'max:100'],
            'kode_pos'  => ['nullable', 'string', 'max:10'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $customer->update($validated);
        return redirect()->route('customer.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->pesanan()->whereNotIn('status', ['closed'])->exists()) {
            return back()->with('error', 'Pelanggan tidak dapat dihapus karena memiliki pesanan aktif.');
        }
        $customer->delete();
        return redirect()->route('customer.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new CustomerExport, 'data-customer-' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $customer = Customer::all();
        $pdf = Pdf::loadView('exports.customer-pdf', compact('customer'))->setPaper('a4', 'landscape');
        return $pdf->download('data-customer-' . date('Y-m-d') . '.pdf');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'mimes:xlsx,xls']]);
        Excel::import(new CustomerImport, $request->file('file'));
        return redirect()->route('customer.index')->with('success', 'Data customer berhasil diimport.');
    }

    private function generateKodeCustomer(): string
    {
        $prefix = 'CST-' . date('Ym') . '-';
        $last = Customer::withTrashed()->where('kode_customer', 'like', $prefix . '%')
            ->orderByDesc('kode_customer')->first();
        $number = $last ? (int) substr($last->kode_customer, -4) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
