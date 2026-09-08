<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Alat\StoreAlatRequest;
use App\Http\Requests\Alat\UpdateAlatRequest;
use App\Http\Resources\AlatResource;
use App\Models\Alat;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlatController extends Controller
{
    /**
     * Menampilkan daftar seluruh alat.
     */
    public function index(): JsonResponse
    {
        // Eager loading ('kategori') digunakan untuk mencegah masalah N+1 Query saat mengambil data relasi
        $alat = Alat::with('kategori')->latest()->get();

        return response()->json([
            'message' => 'Daftar alat berhasil diambil.',
            'data' => AlatResource::collection($alat),
        ]);
    }

    /**
     * Menyimpan data alat baru ke database.
     */
    public function store(StoreAlatRequest $request): JsonResponse
    {
        // Ambil data yang sudah lolos validasi dari FormRequest
        $data = $request->validated();

        // DB::transaction memastikan semua proses (upload file & simpan DB) berhasil/gagal secara bersamaan (atomic)
        $alat = DB::transaction(function () use ($request, $data) {
            if ($request->hasFile('gambar')) {
                // Simpan gambar ke direktori storage/app/public/alat
                $data['gambar'] = $request->file('gambar')->store('alat', 'public');
            }

            return Alat::create($data);
        });

        return response()->json([
            'message' => 'Alat berhasil ditambahkan.',
            'data' => new AlatResource($alat->load('kategori')),
        ], 201); // Status code 201 Created
    }

    /**
     * Menampilkan detail satu data alat berdasarkan ID.
     */
    public function show(Alat $alat): JsonResponse
    {
        // Menggunakan Route Model Binding ($alat) dan memuat relasi kategori
        return response()->json([
            'data' => new AlatResource($alat->load('kategori')),
        ]);
    }

    /**
     * Memperbarui data alat yang sudah ada.
     */
    public function update(UpdateAlatRequest $request, Alat $alat): JsonResponse
    {
        $data = $request->validated();
        $oldGambar = $alat->gambar; // Simpan path gambar lama untuk dihapus nanti

        DB::transaction(function () use ($request, &$data, $alat, $oldGambar) {
            if ($request->hasFile('gambar')) {
                // Upload gambar baru terlebih dahulu
                $data['gambar'] = $request->file('gambar')->store('alat', 'public');

                // Hapus gambar lama dari server jika upload gambar baru berhasil
                if ($oldGambar) {
                    Storage::disk('public')->delete($oldGambar);
                }
            }
            $alat->update($data);
        });

        return response()->json([
            'message' => 'Alat berhasil diperbarui.',
            'data' => new AlatResource($alat->load('kategori')),
        ]);
    }

    /**
     * Menghapus data alat beserta berkas gambarnya.
     */
    public function destroy(Alat $alat): JsonResponse
    {
        DB::transaction(function () use ($alat) {
            // Hapus berkas gambar di storage sebelum menghapus record dari database
            if ($alat->gambar) {
                Storage::disk('public')->delete($alat->gambar);
            }
            $alat->delete();
        });

        return response()->json([
            'message' => 'Alat berhasil dihapus.',
        ]);
    }

    /**
     * Menampilkan katalog khusus untuk alat yang berstatus tersedia.
     */
    public function katalog(): JsonResponse
    {
        // Memanggil local scope tersedia() dari Model Alat untuk menyaring alat yang siap dipinjam
        $alat = Alat::with('kategori')->tersedia()->latest()->get();

        return response()->json([
            'message' => 'Katalog alat tersedia.',
            'data' => AlatResource::collection($alat),
        ]);
    }
}
