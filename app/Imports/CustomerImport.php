<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomerImport implements ToModel, WithHeadingRow
{
    public function model(array $row): ?Customer
    {
        $prefix = 'CST-' . date('Ym') . '-';
        $last = Customer::withTrashed()->where('kode_customer', 'like', $prefix . '%')->orderByDesc('kode_customer')->first();
        $number = $last ? (int) substr($last->kode_customer, -4) + 1 : 1;
        $kode = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);

        return new Customer([
            'kode_customer' => $kode,
            'nama'          => $row['nama'],
            'no_hp'         => $row['no_hp'] ?? null,
            'email'         => $row['email'] ?? null,
            'alamat'        => $row['alamat'] ?? null,
            'kota'          => $row['kota'] ?? null,
            'provinsi'      => $row['provinsi'] ?? null,
            'kode_pos'      => $row['kode_pos'] ?? null,
        ]);
    }
}
