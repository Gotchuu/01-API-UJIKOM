<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pengembalian\StorePengembalianRequest;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\PesanPerbaikan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PetugasController extends Controller
{
    /**
     * Dashboard Petugas
     */
    public function dashboard()
    {
        $menunggu = Peminjaman::where('status', 'diajukan')->count();
        $dipinjam = Peminjaman::whereIn('status', ['dipinjam', 'diproses'])->count();
        
        // Hitung telat secara dinamis (status 'dipinjam'/'diproses' & tgl_kembali_plan < hari ini)
        $telat = Peminjaman::whereIn('status', ['dipinjam', 'diproses'])
            ->whereDate('tgl_kembali_plan', '<', Carbon::today())
            ->count();

        $kembaliHariIni = Pengembalian::whereDate('created_at', Carbon::today())->count();
        $totalAlat = Alat::count();
        $stokMenipis = Alat::where('stok', '<=', 3)->count();
        $recent = Peminjaman::with(['user', 'detailPinjams.alat'])->latest()->take(5)->get();

        return view('petugas.dashboard', compact('menunggu', 'dipinjam', 'telat', 'kembaliHariIni', 'totalAlat', 'stokMenipis', 'recent'));
    }

    /**
     * Menampilkan daftar pengajuan peminjaman (Persetujuan Peminjaman)
     */
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat'])
            ->where('status', 'diajukan')
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $allAlats = Alat::where('stok', '>', 0)->orderBy('nama_alat', 'asc')->get();

        return view('petugas.peminjaman.index', compact('peminjamans', 'search', 'allAlats'));
    }

    /**
     * Menyetujui pengajuan peminjaman awal oleh Petugas
     */
    public function setujuiPeminjaman(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $peminjaman = Peminjaman::with('detailPinjams.alat')->findOrFail($id);

                if ($peminjaman->status !== 'diajukan') {
                    throw new Exception('Peminjaman ini sudah diproses.');
                }

                app()->instance('skip_alat_log', true);

                foreach ($peminjaman->detailPinjams as $detail) {
                    $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);
                    if ($alat->stok < $detail->jumlah) {
                        throw new Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
                    }
                    $alat->decrement('stok', $detail->jumlah);
                }

                $peminjaman->status = 'dipinjam';
                $peminjaman->save(); 
            });

            return redirect()->back()->with('success', 'Peminjaman berhasil disetujui!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Menolak Peminjaman
     */
    public function tolakPeminjaman(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:255',
        ]);

        try {
            $peminjaman = Peminjaman::findOrFail($id);

            if ($peminjaman->status === 'diajukan') {
                $peminjaman->update([
                    'status' => 'ditolak',
                    'alasan_penolakan' => $request->alasan_penolakan,
                ]);

                return redirect()->back()->with('success', 'Pengajuan peminjaman berhasil ditolak.');
            }

            return redirect()->back()->with('error', 'Status peminjaman sudah berubah.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    /**
     * Memperbarui data pengajuan peminjaman oleh Petugas (Khusus Status 'diajukan')
     */
    public function updatePeminjamanRequest(Request $request, $id)
    {
        $request->validate([
            'tgl_pinjam' => 'required|date',
            'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
            'jumlah' => 'nullable|array',
            'jumlah.*' => 'required|integer|min:1',
            'new_alat_id' => 'nullable|exists:alat,id',
            'new_jumlah' => 'nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjams')->findOrFail($id);

            if ($peminjaman->status !== 'diajukan') {
                throw new \Exception("Hanya peminjaman berstatus 'diajukan' yang dapat diubah.");
            }

            $peminjaman->update([
                'tgl_pinjam' => $request->tgl_pinjam,
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
            ]);

            if ($request->has('jumlah')) {
                foreach ($request->jumlah as $detailId => $jumlahBaru) {
                    $detail = \App\Models\DetailPinjam::where('peminjaman_id', $peminjaman->id)->where('id', $detailId)->first();
                    if ($detail) {
                        $alat = Alat::findOrFail($detail->alat_id);
                        if ($alat->stok < $jumlahBaru) {
                            throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi (Tersedia: {$alat->stok}).");
                        }
                        $detail->update(['jumlah' => $jumlahBaru]);
                    }
                }
            }

            if ($request->filled('new_alat_id') && $request->filled('new_jumlah')) {
                $alatBaru = Alat::findOrFail($request->new_alat_id);
                
                if ($alatBaru->stok < $request->new_jumlah) {
                    throw new \Exception("Stok alat '{$alatBaru->nama_alat}' tidak mencukupi (Tersedia: {$alatBaru->stok}).");
                }

                $existingDetail = \App\Models\DetailPinjam::where('peminjaman_id', $peminjaman->id)
                                    ->where('alat_id', $request->new_alat_id)
                                    ->first();

                if ($existingDetail) {
                    $totalJumlah = $existingDetail->jumlah + $request->new_jumlah;
                    if ($alatBaru->stok < $totalJumlah) {
                        throw new \Exception("Total stok alat '{$alatBaru->nama_alat}' tidak mencukupi.");
                    }
                    $existingDetail->update(['jumlah' => $totalJumlah]);
                } else {
                    \App\Models\DetailPinjam::create([
                        'peminjaman_id' => $peminjaman->id,
                        'alat_id' => $request->new_alat_id,
                        'jumlah' => $request->new_jumlah,
                    ]);
                }
            }

            if ($peminjaman->detailPinjams()->count() === 0) {
                throw new \Exception("Peminjaman harus memiliki minimal 1 jenis alat.");
            }

            DB::commit();
            return redirect()->back()->with('success', 'Request peminjaman berhasil diperbarui oleh petugas.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroyDetailItem($detailId)
    {
        try {
            $detail = \App\Models\DetailPinjam::with('peminjaman')->findOrFail($detailId);
            
            if ($detail->peminjaman->status !== 'diajukan') {
                return redirect()->back()->with('error', 'Hanya item berstatus diajukan yang bisa dihapus.');
            }

            if ($detail->peminjaman->detailPinjams()->count() <= 1) {
                return redirect()->back()->with('error', 'Tidak bisa menghapus semua item. Minimal harus ada 1 item alat.');
            }

            $detail->delete();
            return redirect()->back()->with('success', 'Item alat berhasil dihapus dari pengajuan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Menampilkan daftar pemantauan pengembalian alat
     */
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');
        $tglMulai = $request->input('tgl_mulai');
        $tglSelesai = $request->input('tgl_selesai');

        // Validasi sederhana jika tanggal selesai lebih kecil dari tanggal mulai
        if ($tglMulai && $tglSelesai && $tglSelesai < $tglMulai) {
            return back()->with('error', 'Tanggal "Sampai" tidak boleh lebih awal dari tanggal "Dari".');
        }

        $pengembalians = Pengembalian::with([
            'peminjaman' => function ($query) {
                $query->withTrashed()->with([
                    'user' => fn($q) => $q->withTrashed(),
                    'detailPinjams.alat' => fn($q) => $q->withTrashed()
                ]);
            },
            'petugas' => fn($q) => $q->withTrashed()
        ])
        // Filter Pencarian Nama Peminjam atau Nama Alat
        ->when($search, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->whereHas('peminjaman.user', function ($u) use ($search) {
                    $u->withTrashed()->where('name', 'like', "%{$search}%");
                })->orWhereHas('peminjaman.detailPinjams.alat', function ($a) use ($search) {
                    $a->withTrashed()->where('nama_alat', 'like', "%{$search}%");
                });
            });
        })
        // Filter Rentang Tanggal Pengembalian
        ->when($tglMulai && $tglSelesai, function ($query) use ($tglMulai, $tglSelesai) {
            return $query->whereBetween('tgl_kembali', [$tglMulai, $tglSelesai]);
        })
        ->when($tglMulai && !$tglSelesai, function ($query) use ($tglMulai) {
            return $query->whereDate('tgl_kembali', '>=', $tglMulai);
        })
        ->when(!$tglMulai && $tglSelesai, function ($query) use ($tglSelesai) {
            return $query->whereDate('tgl_kembali', '<=', $tglSelesai);
        })
        ->latest()
        ->paginate(10)->withQueryString();

        // Mengambil daftar peminjaman yang berstatus dipinjam atau sedang diproses pengembaliannya
        $menungguKonfirmasi = Peminjaman::with(['user', 'detailPinjams.alat'])
            ->whereIn('status', ['dipinjam', 'diproses', 'menunggu_kembali'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->withTrashed()->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)->withQueryString();

        return view('petugas.pengembalian.index', compact('pengembalians', 'menungguKonfirmasi', 'search', 'tglMulai', 'tglSelesai'));
    }

    /**
     * Proses Verifikasi Pengembalian Barang
     */
    public function storePengembalian(StorePengembalianRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $peminjaman = Peminjaman::with('detailPinjams')->lockForUpdate()->findOrFail($request->peminjaman_id);

                if (! in_array($peminjaman->status, ['dipinjam', 'diproses', 'menunggu_kembali'])) {
                    throw new Exception('Transaksi ini tidak sedang dalam proses pengembalian.');
                }

                $tglKembali = Carbon::parse($request->tgl_kembali)->startOfDay();
                $today = Carbon::today();
                $tglPlan = Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();
                $dendaTelat = 0;

                if ($tglKembali->lessThan($today)) {
                    throw new Exception("Petugas tidak diizinkan memproses pengembalian untuk tanggal masa lalu. Silakan hubungi Admin jika butuh penyesuaian.");
                }

                if ($tglKembali->greaterThan($tglPlan)) {
                    $selisihHari = max(0, (int) $tglPlan->diffInDays($tglKembali, false));
                    $dendaTelat = $selisihHari * 1000;
                }

                $dendaKondisi = max(0, (int) ($request->denda_kondisi ?? 0));
                $totalDenda = $dendaTelat + $dendaKondisi;

                Pengembalian::create([
                    'peminjaman_id' => $peminjaman->id,
                    'tgl_kembali' => $request->tgl_kembali,
                    'kondisi_kembali' => $request->kondisi_kembali,
                    'denda' => $totalDenda,
                    'petugas_id' => auth()->id(),
                ]);

                $peminjaman->update(['status' => 'dikembalikan']);

                app()->instance('skip_alat_log', true);

                $kondisi = strtolower($request->kondisi_kembali);

                foreach ($peminjaman->detailPinjams as $detail) {
                    $alat = Alat::lockForUpdate()->find($detail->alat_id);
                    if ($alat) {
                        if (str_contains($kondisi, 'hilang')) {
                            $alat->increment('stok_hilang', $detail->jumlah);
                        } elseif (str_contains($kondisi, 'berat')) {
                            $alat->increment('stok_rusak_berat', $detail->jumlah);
                        } elseif (str_contains($kondisi, 'ringan')) {
                            $alat->increment('stok_rusak_ringan', $detail->jumlah);
                        } else {
                            $alat->increment('stok_baik', $detail->jumlah);
                        }
                    }
                }
            });

            return redirect()->route('petugas.pengembalian.index')->with('success', 'Pengembalian barang berhasil diverifikasi dan diproses!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
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

    public function indexPesan(Request $request)
    {
        $status = $request->input('status');

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

    public function indexLaporan(Request $request)
    {
        $dari = $request->input('dari', now()->subMonth()->toDateString());
        $sampai = $request->input('sampai', now()->toDateString());
        $status = $request->input('status');

        if ($request->filled('dari') && $request->filled('sampai')) {
            if ($sampai < $request->input('dari')) {
                return back()->with('error', 'Tanggal "Sampai" tidak boleh lebih awal dari tanggal "Dari".');
            }
        }

        // Query Utama dengan penanganan Soft Delete
        $query = Peminjaman::with([
            'user' => fn($u) => $u->withTrashed(),
            'detailPinjams.alat' => fn($a) => $a->withTrashed(),
            'pengembalian.petugas' => fn($p) => $p->withTrashed()
        ])
        ->whereBetween('tgl_pinjam', [$dari, $sampai])
        ->when($status, fn($q) => $q->where('status', $status));

        $peminjamans = $query->latest()->paginate(10)->withQueryString();

        return view('petugas.laporan.index', compact('peminjamans', 'dari', 'sampai', 'status'));
    }

    public function cetakPdf(Request $request)
    {
        $dari = $request->input('dari', now()->subMonth()->toDateString());
        $sampai = $request->input('sampai', now()->toDateString());
        $status = $request->input('status');

        if ($request->filled('dari') && $request->filled('sampai')) {
            if ($sampai < $request->input('dari')) {
                return back()->with('error', 'Tanggal "Sampai" tidak boleh lebih awal dari tanggal "Dari".');
            }
        }

        $peminjamans = Peminjaman::with([
            'user' => fn($u) => $u->withTrashed(),
            'detailPinjams.alat' => fn($a) => $a->withTrashed(),
            'pengembalian.petugas' => fn($p) => $p->withTrashed()
        ])
        ->whereBetween('tgl_pinjam', [$dari, $sampai])
        ->when($status, fn($q) => $q->where('status', $status))
        ->latest()
        ->get();

        $pdf = Pdf::loadView('petugas.laporan.pdf', compact('peminjamans', 'dari', 'sampai', 'status'));

        return $pdf->stream('Laporan-Peminjaman-' . $dari . '-sampai-' . $sampai . '.pdf');
    }
}