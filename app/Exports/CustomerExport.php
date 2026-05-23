<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CustomerExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection() { return Customer::all(); }
    public function headings(): array { return ['Kode', 'Nama', 'No HP', 'Email', 'Alamat', 'Kota', 'Provinsi', 'Kode Pos']; }
    public function map($c): array { return [$c->kode_customer, $c->nama, $c->no_hp, $c->email, $c->alamat, $c->kota, $c->provinsi, $c->kode_pos]; }
}
