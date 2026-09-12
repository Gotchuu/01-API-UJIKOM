<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pengembalian\StorePengembalianRequest;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\PesanPerbaikan;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PetugasController extends Controller
{
    // Menampilkan daftar pengajuan peminjaman dari siswa/peminjam
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat'])
            ->whereIn('status', ['diajukan', 'dipinjam']) // Ambil status diajukan dan dipinjam
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('petugas.peminjaman.index', compact('peminjamans', 'search'));
    }

    // Menyetujui Peminjaman (Mengubah status & mengurangi stok alat)
    public function setujuiPeminjaman($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjams')->findOrFail($id);
            $peminjaman->update(['status' => 'dipinjam']);

            // Kurangi stok alat secara otomatis
            app()->instance('skip_alat_log', true);
            foreach ($peminjaman->detailPinjams as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok = $alat->stok - $detail->jumlah;
                $alat->saveQuietly(); // saveQuietly = tanpa trigger observer sama sekali (lebih bersih)
            }
            app()->instance('skip_alat_log', false);

            DB::commit();

            return redirect()->back()->with('success', 'Peminjaman disetujui dan stok alat dikurangi.');
        } catch (Exception $e) {
            DB::rollback();

            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    // Menolak Peminjaman (Menghapus pengajuan agar siswa bisa mengajukan ulang)
    public function tolakPeminjaman($id)
    {
        try {
            $peminjaman = Peminjaman::findOrFail($id);

            // Pastikan statusnya memang masih diajukan
            if ($peminjaman->status == 'diajukan') {
                $peminjaman->delete();

                return redirect()->back()->with('success', 'Pengajuan peminjaman berhasil ditolak.');
            }

            return redirect()->back()->with('error', 'Status peminjaman sudah berubah.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    // Menampilkan daftar pemantauan pengembalian alat
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        $pengembalians = Pengembalian::with(['peminjaman.user', 'peminjaman.detailPinjams.alat', 'petugas'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('peminjaman.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('petugas.pengembalian.index', compact('pengembalians', 'search'));
    }

    public function storePengembalian(StorePengembalianRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $peminjaman = Peminjaman::with('detailPinjams')->lockForUpdate()->findOrFail($request->peminjaman_id);
                if (! in_array($peminjaman->status, ['dipinjam', 'telat'])) {
                    throw new Exception('Transaksi ini tidak dalam status dipinjam.');
                }
                $tglKembali = Carbon::parse($request->tgl_kembali)->startOfDay();
                $tglPlan = Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();
                $dendaTelat = 0;
                if ($tglKembali->greaterThan($tglPlan)) {
                    $selisihHari = max(0, (int) $tglPlan->diffInDays($tglKembali, false));
                    $dendaTelat = $selisihHari * 1000;
                }
                $dendaKondisi = max(0, (int) ($request->denda_kondisi ?? 0));
                $totalDenda = $dendaTelat + $dendaKondisi;
                $statusPeminjaman = $tglKembali->greaterThan($tglPlan) ? 'telat' : 'dikembalikan';

                Pengembalian::create([
                    'peminjaman_id' => $peminjaman->id,
                    'tgl_kembali' => $request->tgl_kembali,
                    'kondisi_kembali' => $request->kondisi_kembali,
                    'denda' => $totalDenda,
                    'petugas_id' => auth()->id(),
                ]);
                $peminjaman->update(['status' => $statusPeminjaman]);
                foreach ($peminjaman->detailPinjams as $detail) {
                    $alat = Alat::lockForUpdate()->find($detail->alat_id);
                    if ($alat) {
                        $alat->increment('stok', $detail->jumlah);
                    }
                }
            });

            return redirect()->route('petugas.peminjaman.index')->with('success', 'Pengembalian berhasil diproses!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function dashboard()
    {
        $menunggu = Peminjaman::where('status', 'diajukan')->count();
        $dipinjam = Peminjaman::where('status', 'dipinjam')->count();
        $telat = Peminjaman::where('status', 'telat')->count();
        $kembaliHariIni = Pengembalian::whereDate('created_at', Carbon::today())->count();
        $totalAlat = Alat::count();
        $stokMenipis = Alat::where('stok', '<=', 3)->count();
        $recent = Peminjaman::with(['user', 'detailPinjams.alat'])->latest()->take(5)->get();

        return view('petugas.dashboard', compact('menunggu', 'dipinjam', 'telat', 'kembaliHariIni', 'totalAlat', 'stokMenipis', 'recent'));
    }

    public function storePesanPerbaikan(Request $request)
    {
        $request->validate([
            'pengembalian_id' => 'required|exists:pengembalian,id',
            'jenis' => 'required|in:kondisi,denda,tanggal,lainnya',
            'pesan' => 'required|string|min:10|max:500',
        ]);
        PesanPerbaikan::create([
            'pengembalian_id' => $request->pengembalian_id,
            'petugas_id' => auth()->id(),
            'jenis' => $request->jenis,
            'pesan' => $request->pesan,
            'status' => 'terkirim',
        ]);

        return back()->with('success', 'Pesan perbaikan terkirim ke admin!');
    }

    // Riwayat laporan milik petugas sendiri + balasan admin
    public function indexPesan(Request $request)
    {
        $status = $request->input('status'); // filter: terkirim/dibaca/selesai

        $pesans = PesanPerbaikan::with(['pengembalian.peminjaman.user', 'admin'])
            ->where('petugas_id', auth()->id())
            ->when($status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('petugas.pesan.index', compact('pesans', 'status'));
    }
}
