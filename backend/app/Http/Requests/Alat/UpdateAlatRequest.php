<?php

namespace App\Http\Requests\Alat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAlatRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna memiliki izin untuk membuat permintaan ini.
     * Mengembalikan 'true' berarti semua user terautentikasi diizinkan.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendapatkan aturan validasi yang berlaku untuk permintaan ini.
     */
    public function rules(): array
    {
        // Mengambil ID alat yang sedang di-update dari parameter URL (misal: /api/alat/{alat})
        $alatId = $this->route('alat');

        return [
            // Memastikan kategori_id wajib diisi, bertipe integer, dan ID-nya ada di tabel 'kategori'
            'kategori_id' => ['required', 'integer', Rule::exists('kategori', 'id')],

            // Nama alat wajib berupa teks dan maksimal 255 karakter
            'nama_alat' => ['required', 'string', 'max:255'],

            // Stok wajib bertipe angka bulat dan tidak boleh bernilai negatif (minimal 0)
            'stok' => ['required', 'integer', 'min:0'],

            // Status kondisi (misal: 'Baik', 'Rusak') wajib berupa teks maksimal 255 karakter
            'status_kondisi' => ['required', 'string', 'max:255'],

            // Deskripsi bersifat opsional (boleh kosong), jika diisi harus berupa teks
            'deskripsi' => ['nullable', 'string'],

            // Gambar opsional, namun jika diunggah harus berupa file gambar (jpeg, png, jpg) maks 2MB (2048 KB)
            'gambar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];
    }
}
