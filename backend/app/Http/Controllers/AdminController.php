<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pengembalian\StorePengembalianRequest;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\User;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\DetailPinjam;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Menampilkan Dashboard Admin & Log Aktivitas
    public function index()
    {
        $logs = LogAktivitas::with('user')->latest()->take(10)->get();
        return view('admin.dashboard', compact('logs'));
    }

    // CRUD Alat: Menampilkan daftar alat
    // 1. Menampilkan daftar alat
    public function indexAlat(Request $request)
    {
        $search = $request->input('search');

        $alats = Alat::with('kategori')
            ->when($search, function ($query, $search) {
                return $query->where('nama_alat', 'like', "%{$search}%")
                    ->orWhere('status_kondisi', 'like', "%{$search}%")
                    ->orWhereHas('kategori', function ($q) use ($search) {
                        $q->where('nama_kategori', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.alat.index', compact('alats', 'search'));
    }

    // 2. Menampilkan form tambah alat
    public function createAlat()
    {
        $kategori = Kategori::all();
        return view('admin.alat.create', compact('kategori'));
    }

    // 3. Menyimpan data alat baru
    public function storeAlat(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required',
            'nama_alat' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();

        // Upload gambar jika ada
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/' . $filename;
        }

        Alat::create($data);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil ditambahkan.');
    }

    // 4. Menampilkan form edit alat
    public function editAlat($id)
    {
        $alat = Alat::findOrFail($id);
        $kategori = Kategori::all();
        return view('admin.alat.edit', compact('alat', 'kategori'));
    }

    // 5. Memperbarui data alat
    public function updateAlat(Request $request, $id)
    {
        $request->validate([
            'kategori_id' => 'required',
            'nama_alat' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $alat = Alat::findOrFail($id);
        $data = $request->all();

        // Cek jika ada upload gambar baru
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($alat->gambar && file_exists(public_path($alat->gambar))) {
                unlink(public_path($alat->gambar));
            }

            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/' . $filename;
        }

        $alat->update($data);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil diperbarui.');
    }

    // 6. Menghapus data alat
    public function destroyAlat($id)
    {
        $alat = Alat::findOrFail($id);

        // Hapus file gambar fisik jika ada
        if ($alat->gambar && file_exists(public_path($alat->gambar))) {
            unlink(public_path($alat->gambar));
        }

        $alat->delete();

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil dihapus.');
    }

    // CRUD User (Manajemen User Admin, Petugas, Peminjam)
    public function indexUser(Request $request)
{
    $search = $request->input('search');

    $users = User::when($search, function ($query, $search) {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->orWhere('role', 'like', "%{$search}%");
    })
    ->latest()
    ->paginate(10) // Tampilkan 10 data per halaman
    ->withQueryString(); // Memastikan parameter search tetap ada saat pindah halaman

    return view('admin.user.index', compact('users', 'search'));
}

    public function createUser()
    {
        return view('admin.user.create');
    }

    // Menyimpan user baru ke database
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,petugas,peminjam',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ]);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user.edit', compact('user'));
    }

    // Memperbarui data user
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,petugas,peminjam',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ];

        // Update password jika diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.user.index')->with('success', 'Data user berhasil diperbarui.');
    }

    // Menghapus data user
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'Data user berhasil dihapus.');
    }

        // 1. Menampilkan daftar kategori (dengan Search & Pagination)
    public function indexKategori(Request $request)
    {
        $search = $request->input('search');

        $kategoris = Kategori::when($search, function ($query, $search) {
            return $query->where('nama_kategori', 'like', "%{$search}%");
        })
        ->latest()
        ->paginate(5)
        ->withQueryString();

        return view('admin.kategori.index', compact('kategoris', 'search'));
    }

    // 2. Menampilkan form tambah kategori
    public function createKategori()
    {
        return view('admin.kategori.create');
    }

    // 3. Menyimpan kategori baru
    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategoris,nama_kategori',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    // 4. Menampilkan form edit kategori
    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('admin.kategori.edit', compact('kategori'));
    }

    // 5. Memperbarui kategori
    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $id,
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    // 6. Menghapus kategori
    public function destroyKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        // Opsional: Cek apakah kategori masih dipakai oleh alat
        if ($kategori->alats()->count() > 0) {
            return redirect()->route('admin.kategori.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data alat.');
        }

        $kategori->delete();

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    // 1. Menampilkan daftar semua transaksi peminjaman
        public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('detailPinjams.alat', function ($q3) use ($search) {
                        $q3->where('nama_alat', 'like', "%{$search}%");
                    });
                });
            })
            ->latest()
            ->paginate(7)
            ->withQueryString();

        return view('admin.peminjaman.index', compact('peminjamans'));
    }

    // 2. Menampilkan form tambah peminjaman baru
    public function createPeminjaman()
    {
        $users = User::where('role', 'peminjam')->get();
        $alats = Alat::where('stok', '>', 0)->get();
        return view('admin.peminjaman.create', compact('users', 'alats'));
    }

    // 3. Menyimpan data peminjaman baru
    public function storePeminjaman(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tgl_pinjam' => 'required|date',
            'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
            'alat_id' => 'required|array',
            'alat_id.*' => 'exists:alat,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Buat transaksi utama peminjaman
            $peminjaman = Peminjaman::create([
                'user_id' => $request->user_id,
                'tgl_pinjam' => $request->tgl_pinjam,
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan', // Status awal
            ]);

            // Simpan detail alat yang dipinjam
            foreach ($request->alat_id as $index => $alatId) {
                $jumlahPinjam = $request->jumlah[$index];

                $alat = Alat::findOrFail($alatId);

                // Validasi stok
                if ($alat->stok < $jumlahPinjam) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlahPinjam,
                ]);

                // Kurangi stok alat jika status langsung disetujui/dipinjam (Opsional, atau dikurangi saat status berubah jadi 'dipinjam')
            }

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // 4. Menampilkan detail transaksi peminjaman
    public function showPeminjaman($id)
    {
        $peminjaman = Peminjaman::with(['user', 'detailPinjams.alat', 'pengembalian'])->findOrFail($id);
        return view('admin.peminjaman.show', compact('peminjaman'));
    }

    // 5. Mengubah status peminjaman (Persetujuan / Pengembalian / Penolakan)
    // 5. Mengubah status peminjaman
public function updateStatusPeminjaman(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:diajukan,dipinjam,dikembalikan,telat',
    ]);

    DB::beginTransaction();
    try {
        $peminjaman = Peminjaman::with('detailPinjams')->findOrFail($id);
        $statusLama = $peminjaman->status;
        $statusBaru = $request->status;

        // 1. Jika status berubah jadi "dipinjam" -> Kurangi stok alat
        if ($statusBaru == 'dipinjam' && $statusLama != 'dipinjam') {
            foreach ($peminjaman->detailPinjams as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                if ($alat->stok < $detail->jumlah) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
                }
                $alat->stok -= $detail->jumlah;
                $alat->save();
            }
        }

        // 2. Jika status berubah jadi "dikembalikan" dari "dipinjam" atau "telat" -> Kembalikan stok alat
        if ($statusBaru == 'dikembalikan' && in_array($statusLama, ['dipinjam', 'telat'])) {
            foreach ($peminjaman->detailPinjams as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok += $detail->jumlah;
                $alat->save();
            }
        }

        // Update status di database
        $peminjaman->update(['status' => $statusBaru]);

        DB::commit();
        return redirect()->back()->with('success', 'Status peminjaman berhasil diperbarui.');
    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->with('error', $e->getMessage());
    }
}

    // 6. Menghapus data transaksi peminjaman
    public function destroyPeminjaman($id)
    {
        $peminjaman = Peminjaman::findOrFail($id);
        $peminjaman->delete();

        return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil dihapus.');
    }

    public function pengembalianStore(StorePengembalianRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $peminjaman = Peminjaman::with('detailPinjams')->lockForUpdate()->findOrFail($request->peminjaman_id);

                if (!in_array($peminjaman->status, ['dipinjam', 'telat'])) {
                    throw new Exception("Transaksi ini tidak dalam status dipinjam.");
                }

                                // Carbon parse & set ke jam 00:00:00
                $tglKembali = Carbon::parse($request->tgl_kembali)->startOfDay();
                $tglPlan    = Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();

                // Hitung Denda Keterlambatan (Rp 1.000 / Hari)
                $dendaTelat = 0;
                if ($tglKembali->greaterThan($tglPlan)) {
                    // Parameter kedua `false` memastikan nilai selisih absolut (selalu positif)
                    $selisihHari = $tglPlan->diffInDays($tglKembali, false);
                    
                    // Pastikan angka hari bernilai positif
                    $selisihHari = max(0, (int) $selisihHari);
                    $dendaTelat  = $selisihHari * 1000;
                }

                // Denda Kondisi (Rusak/Hilang) dari Input Form (Pastikan di-cast ke Integer positif)
                $dendaKondisi = max(0, (int) ($request->denda_kondisi ?? 0));

                // TOTAL DENDA = DENDA TELAT + DENDA KONDISI
                $totalDenda = $dendaTelat + $dendaKondisi;

                // Status Akhir Peminjaman
                $statusPeminjaman = $tglKembali->greaterThan($tglPlan) ? 'telat' : 'dikembalikan';

                // 1. Simpan ke tabel Pengembalian
                Pengembalian::create([
                    'peminjaman_id'   => $peminjaman->id,
                    'tgl_kembali'     => $request->tgl_kembali,
                    'kondisi_kembali' => $request->kondisi_kembali,
                    'denda'           => $totalDenda,
                    'petugas_id'      => auth()->id(),
                ]);

                // 2. Update Status Peminjaman
                $peminjaman->update(['status' => $statusPeminjaman]);

                // 3. Increment Stok Alat
                foreach ($peminjaman->detailPinjams as $detail) {
                    $alat = Alat::lockForUpdate()->find($detail->alat_id);
                    if ($alat) {
                        $alat->increment('stok', $detail->jumlah);
                    }
                }
            });

            return redirect()->route('admin.peminjaman.index')
                ->with('success', 'Pengembalian barang berhasil diproses dan stok alat diperbarui!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tampilan Rekapitulasi Data Pengembalian
     */
    public function pengembalianIndex(Request $request)
    {
        // Mengambil data peminjaman yang statusnya masih 'dipinjam' beserta detail alatnya untuk modal
        $peminjamans = \App\Models\Peminjaman::with(['user', 'detailPinjams.alat'])
            ->where('status', 'dipinjam')
            ->get();

        $query = Pengembalian::with(['peminjaman.user', 'peminjaman.detailPinjams.alat', 'petugas']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('peminjaman.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('kondisi_kembali', 'like', "%{$search}%");
        }

        $pengembalians = $query->latest()->paginate(10);

        return view('admin.pengembalian.index', compact('pengembalians'));
    }

    public function destroyPengembalian($id)
    {
        DB::beginTransaction();
        try {
            $pengembalian = Pengembalian::findOrFail($id);

            // 1. Cari data peminjaman terkait
            $peminjaman = Peminjaman::find($pengembalian->peminjaman_id);

            if ($peminjaman) {
                // 2. Ubah status peminjaman kembali menjadi 'dipinjam'
                $peminjaman->update([
                    'status' => 'dipinjam'
                ]);
            }

            // 3. Hapus data pengembalian
            $pengembalian->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Data pengembalian berhasil dihapus dan status peminjaman dikembalikan menjadi Dipinjam.');
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
    }