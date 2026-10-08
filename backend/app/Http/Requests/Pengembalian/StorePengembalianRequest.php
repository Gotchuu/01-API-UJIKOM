<?php

namespace App\Http\Requests\Pengembalian;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePengembalianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'peminjaman_id' => ['required', 'integer', Rule::exists('peminjaman', 'id')],
            'tgl_kembali' => ['required', 'date', 'after_or_equal:tgl_pinjam'],
            'kondisi_kembali' => ['required', 'string', 'max:255'],
            'deskripsi_kondisi' => ['nullable', 'string', 'max:1000', 'required_unless:kondisi_kembali,Baik & Lengkap,Baik / Lengkap', 'nullable'],
            'denda_kondisi' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'peminjaman_id' => 'ID Peminjaman',
            'tgl_kembali' => 'Tanggal Pengembalian',
            'kondisi_kembali' => 'Kondisi barang kembali',
            'denda_kondisi' => 'Denda kondisi alat',
        ];
    }
}
