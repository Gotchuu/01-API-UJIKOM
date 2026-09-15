<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori; // Tambahkan Import Kategori
use App\Models\DetailPinjam;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PeminjamController extends Controller
{
    // Melihat daftar/katalog alat yang tersedia (LENGKAP DENGAN FILTER & SEARCH)
    public function katalogAlat(Request $request)
    {
        $search = $request->input('search');
        $kategoriId = $request->input('kategori_id');

        // Ambil semua data kategori untuk dropdown filter
        $kategoris = Kategori::all();

        $alats = Alat::with('kategori')
            ->where('stok', '>', 0)
            ->when($search, function ($query, $search) {
                return $query->where('nama_alat', 'like', "%{$search}%");
            })
            ->when($kategoriId, function ($query, $kategoriId) {
                return $query->where('kategori_id', $kategoriId);
            })
            ->latest()
            ->get();

        return view('peminjam.katalog', compact('alats', 'kategoris', 'search', 'kategoriId'));
    }

    // Memproses Pengajuan Peminjaman Alat
    public function ajukanPeminjaman(Request $request)
    {
        $request->validate([
            'tgl_kembali_plan' => 'required|date|after:today',
            'alat_id'          => 'required|array',
            'jumlah'           => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            // 1. Buat Header Peminjaman
            $peminjaman = Peminjaman::create([
                'user_id'          => auth()->id(),
                'tgl_pinjam'       => now(),
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status'           => 'diajukan',
            ]);

            // 2. Simpan Detail Peminjaman (Hanya untuk alat_id yang dicentang)
            foreach ($request->alat_id as $alatId) {
                // Ambil jumlah pinjam spesifik berdasarkan key ID alat
                $jumlahPinjam = isset($request->jumlah[$alatId]) ? $request->jumlah[$alatId] : 1;

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id'        => $alatId,
                    'jumlah'         => $jumlahPinjam,
                ]);
            }

            DB::commit();
            return redirect()->route('peminjam.riwayat')->with('success', 'Pengajuan peminjaman berhasil dikirim.');
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
        }
    }

    // Melihat riwayat peminjaman user yang sedang login
    public function riwayatPeminjaman()
    {
        $peminjamans = Peminjaman::with('detailPinjams.alat')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('peminjam.riwayat', compact('peminjamans'));
    }

    public function showPeminjaman(Peminjaman $peminjaman)
    {
        $user = auth()->user();
        if ($peminjaman->user_id !== $user->id) {
            return redirect()->route('peminjam.katalog')->with('error', 'Akses ditolak.');
        }
        return view('peminjam.peminjaman.show', compact('peminjaman'));
    }

    public function updatePeminjaman(Request $request, Peminjaman $peminjaman)
    {
        $user = auth()->user();
        if ($user->id !== $peminjaman->user_id || $peminjaman->status !== 'diajukan') {
            return redirect()->back()->with('error', 'Tidak dapat diubah.');
        }
        $request->validate([
            'tgl_kembali_plan' => 'required|date|after:today',
        ]);
        $peminjaman->update(['tgl_kembali_plan' => $request->tgl_kembali_plan]);
        return redirect()->back()->with('success', 'Tanggal kembali diperbarui.');
    }

    public function destroyPeminjaman(Peminjaman $peminjaman)
    {
        $user = auth()->user();
        if ($user->id !== $peminjaman->user_id || $peminjaman->status !== 'diajukan') {
            return redirect()->back()->with('error', 'Tidak dapat dibatalkan.');
        }
        DB::transaction(function () use ($peminjaman) {
            $peminjaman->detailPinjam()->delete();
            $peminjaman->delete();
        });
        return redirect()->route('peminjam.katalog')->with('success', 'Pengajuan dibatalkan.');
    }
}
