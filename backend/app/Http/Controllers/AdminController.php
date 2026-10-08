<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pengembalian\StorePengembalianRequest;
use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\PesanPerbaikan;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // Menampilkan Dashboard Admin & Log Aktivitas
    public function index()
    {
        $logs = LogAktivitas::with('user')->latest()->take(10)->get();

        $totalAlat = Alat::count();
        $totalUser = User::count();
        $totalKategori = Kategori::count();
        $peminjamanDiajukan = Peminjaman::where('status', 'diajukan')->count();
        $peminjamanDipinjam = Peminjaman::where('status', 'dipinjam')->count();
        $peminjamanAktif = $peminjamanDiajukan + $peminjamanDipinjam;
        $stokMenipis = Alat::where('stok', '<=', 3)->count();
        $alatsMenipis = Alat::with('kategori')->where('stok', '<=', 3)->orderBy('stok')->take(3)->get();

        $jmlPesanTerkirim = PesanPerbaikan::where('status', 'terkirim')->count();
        $pesanTerbaru = PesanPerbaikan::with(['petugas','pengembalian'])->where('status', 'terkirim')->latest()->take(2)->get();

        return view('admin.dashboard', compact(
            'logs', 'totalAlat', 'totalUser', 'totalKategori',
            'peminjamanDiajukan', 'peminjamanDipinjam', 'peminjamanAktif',
            'stokMenipis', 'alatsMenipis',
            'jmlPesanTerkirim', 'pesanTerbaru'
        ));
    }

    // 1. Menampilkan daftar alat
    public function indexAlat(Request $request)
    {
        $search = $request->input('search');

        $alats = Alat::with('kategori')
            ->withExists(['detailPinjam as sedang_dipinjam' => function ($q) {
                $q->whereHas('peminjaman', function ($p) {
                    $p->whereIn('status', ['diajukan', 'dipinjam', 'diproses', 'diproses']);
                });
            }])
            
            ->withSum(['detailPinjam as total_dipinjam' => function ($q) {
                $q->whereHas('peminjaman', function ($p) {
                    $p->whereIn('status', ['diajukan', 'dipinjam', 'diproses', 'diproses']);
                });
            }], 'jumlah')

            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('nama_alat', 'like', "%{$search}%")
                      ->orWhere('status_kondisi', 'like', "%{$search}%")
                      ->orWhereHas('kategori', function ($k) use ($search) {
                          $k->where('nama_kategori', 'like', "%{$search}%");
                      });
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
            'kategori_id' => 'required|exists:kategori,id',
            'nama_alat' => [
                'required',
                'string',
                'max:255',
                Rule::unique('alat', 'nama_alat')->whereNull('deleted_at')
            ],
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'nama_alat.unique' => 'Nama alat sudah ada di inventaris aktif. Silakan tingkatkan stok pada data alat yang tersedia.',
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/'.$filename;
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
            'kategori_id' => 'required|exists:kategori,id',
            'nama_alat' => 'required|string|max:255|unique:alat,nama_alat,' . $id,
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $alat = Alat::findOrFail($id);
        $data = $request->all();

        if ($request->hasFile('gambar')) {
            if ($alat->gambar && file_exists(public_path($alat->gambar))) {
                unlink(public_path($alat->gambar));
            }

            $file = $request->file('gambar');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/'.$filename;
        }

        $alat->update($data);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil diperbarui.');
    }

    // 6. Menghapus data alat
    public function destroyAlat($id)
    {
        $alat = Alat::findOrFail($id);

        $sedangDipinjam = DetailPinjam::where('alat_id', $id)
            ->whereHas('peminjaman', function ($query) {
                $query->whereIn('status', ['diajukan', 'dipinjam', 'diproses']);
            })
            ->exists();

        if ($sedangDipinjam) {
            return redirect()->route('admin.alat.index')
                ->with('error', "Alat '{$alat->nama_alat}' tidak dapat dihapus karena sedang dalam proses atau status peminjaman aktif!");
        }

        if ($alat->gambar && file_exists(public_path($alat->gambar))) {
            unlink(public_path($alat->gambar));
        }

        $alat->delete();

        return redirect()->route('admin.alat.index')
            ->with('success', "Data alat '{$alat->nama_alat}' berhasil dihapus.");
    }

    // CRUD User
    public function indexUser(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("FIELD(role, 'admin', 'petugas', 'peminjam') ASC")
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.user.index', compact('users', 'search'));
    }

    public function createUser()
    {
        return view('admin.user.create');
    }

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

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() === $user->id && $request->role !== $user->role) {
            return redirect()->back()
                ->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri yang sedang digunakan!');
        }

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role'  => 'required|in:admin,petugas,peminjam',
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ]);

        return redirect()->route('admin.user.index')
            ->with('success', "Data user '{$user->name}' berhasil diperbarui.");
    }

    public function toggleUserStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'peminjam') {
            return redirect()->back()->with('error', 'Fitur penonaktifan akun hanya berlaku untuk Peminjam/Siswa!');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusPesan = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Akun peminjam '{$user->name}' berhasil {$statusPesan}.");
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() === $user->id) {
            return redirect()->route('admin.user.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang digunakan!');
        }

        if ($user->role === 'admin') {
            $jumlahAdmin = User::where('role', 'admin')->count();
            if ($jumlahAdmin <= 1) {
                return redirect()->route('admin.user.index')
                    ->with('error', 'Akun Admin ini tidak dapat dihapus karena merupakan satu-satunya Admin tersisa di sistem!');
            }
        }

        $memilikiPeminjamanAktif = Peminjaman::where('user_id', $id)
            ->whereIn('status', ['diajukan', 'dipinjam', 'diproses'])
            ->exists();

        if ($memilikiPeminjamanAktif) {
            return redirect()->route('admin.user.index')
                ->with('error', "User '{$user->name}' tidak dapat dihapus karena masih memiliki transaksi peminjaman yang aktif atau belum mengembalikan alat!");
        }

        $user->delete();

        return redirect()->route('admin.user.index')
            ->with('success', "User '{$user->name}' berhasil dihapus.");
    }

    // Kategori
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

    public function createKategori()
    {
        return view('admin.kategori.create');
    }

    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori', 'nama_kategori')->whereNull('deleted_at')
            ],
        ], [
            'nama_kategori.unique' => 'Nama kategori ini sudah ada! Silakan gunakan nama kategori lain.',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')
            ->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('admin.kategori.edit', compact('kategori'));
    }

    public function updateKategori(Request $request, $id)
    {
        $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori', 'nama_kategori')
                    ->ignore($id)
                    ->whereNull('deleted_at')
            ],
        ], [
            'nama_kategori.unique' => 'Nama kategori ini sudah digunakan oleh kategori lain!',
        ]);

        $kategori = Kategori::findOrFail($id);
        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->alat()->count() > 0) {
            return redirect()->route('admin.kategori.index')
                ->with('error', "Kategori '{$kategori->nama_kategori}' tidak dapat dihapus karena masih digunakan oleh data alat!");
        }

        $kategori->delete();

        return redirect()->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    // Peminjaman
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');

        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat', 'pengembalian'])
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
            ->when($statusFilter, function ($query, $statusFilter) {
                return $query->where('status', $statusFilter);
            })
            ->latest()
            ->paginate(7)
            ->withQueryString();

        $allAlats = Alat::where('stok', '>', 0)->orderBy('nama_alat', 'asc')->get();

        return view('admin.peminjaman.index', compact('peminjamans', 'allAlats'));
    }

    public function createPeminjaman()
    {
        $users = User::where('role', 'peminjam')->get();
        $alats = Alat::where('stok', '>', 0)->get();

        return view('admin.peminjaman.create', compact('users', 'alats'));
    }

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
            $peminjaman = Peminjaman::create([
                'user_id' => $request->user_id,
                'tgl_pinjam' => $request->tgl_pinjam,
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);

            foreach ($request->alat_id as $index => $alatId) {
                $jumlahPinjam = $request->jumlah[$index];
                $alat = Alat::findOrFail($alatId);

                if ($alat->stok < $jumlahPinjam) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlahPinjam,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function showPeminjaman($id)
    {
        $peminjaman = Peminjaman::with(['user', 'detailPinjams.alat', 'pengembalian'])->findOrFail($id);

        return view('admin.peminjaman.show', compact('peminjaman'));
    }

    public function updateStatusPeminjaman(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:diajukan,dipinjam',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjams')->findOrFail($id);
            $statusLama = $peminjaman->status;
            $statusBaru = $request->status;

            if (in_array($statusLama, ['dikembalikan', 'ditolak']) || $peminjaman->pengembalian) {
                throw new \Exception("Transaksi yang sudah selesai atau ditolak tidak dapat diubah statusnya.");
            }

            if ($statusBaru == 'dipinjam' && $statusLama == 'diajukan') {
                app()->instance('skip_alat_log', true);

                foreach ($peminjaman->detailPinjams as $detail) {
                    $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);
                    
                    if ($alat->stok < $detail->jumlah) {
                        throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
                    }

                    $alat->decrement('stok', $detail->jumlah);
                }
            }

            $peminjaman->update(['status' => $statusBaru]);

            DB::commit();

            return redirect()->back()->with('success', 'Status peminjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

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
                    $detail = DetailPinjam::where('peminjaman_id', $peminjaman->id)->where('id', $detailId)->first();
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

                $existingDetail = DetailPinjam::where('peminjaman_id', $peminjaman->id)
                                    ->where('alat_id', $request->new_alat_id)
                                    ->first();

                if ($existingDetail) {
                    $totalJumlah = $existingDetail->jumlah + $request->new_jumlah;
                    if ($alatBaru->stok < $totalJumlah) {
                        throw new \Exception("Total stok alat '{$alatBaru->nama_alat}' tidak mencukupi.");
                    }
                    $existingDetail->update(['jumlah' => $totalJumlah]);
                } else {
                    DetailPinjam::create([
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
            return redirect()->back()->with('success', 'Request peminjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroyPeminjaman($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjams')->findOrFail($id);

            if ($peminjaman->status === 'dipinjam') {
                app()->instance('skip_alat_log', true);

                foreach ($peminjaman->detailPinjams as $detail) {
                    $alat = Alat::lockForUpdate()->find($detail->alat_id);
                    if ($alat) {
                        $alat->increment('stok', $detail->jumlah);
                    }
                }
            }

            $peminjaman->delete();

            DB::commit();

            return redirect()->route('admin.peminjaman.index')
                ->with('success', 'Data peminjaman berhasil dihapus dan stok alat telah disesuaikan!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

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
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroyDetailItem($detailId)
    {
        try {
            $detail = DetailPinjam::with('peminjaman')->findOrFail($detailId);
            
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

    // Process Pengembalian
    public function pengembalianStore(StorePengembalianRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $peminjaman = Peminjaman::with('detailPinjams')->lockForUpdate()->findOrFail($request->peminjaman_id);

                if (! in_array($peminjaman->status, ['dipinjam', 'diproses'])) {
                    throw new \Exception('Transaksi ini tidak sedang dalam status dipinjam.');
                }

                $tglKembali = Carbon::parse($request->tgl_kembali)->startOfDay();
                $tglPlan = Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();

                $dendaTelat = 0;
                if ($tglKembali->greaterThan($tglPlan)) {
                    $selisihHari = $tglPlan->diffInDays($tglKembali, false);
                    $selisihHari = max(0, (int) $selisihHari);
                    $dendaTelat = $selisihHari * 1000;
                }

                $dendaKondisi = max(0, (int) ($request->denda_kondisi ?? 0));
                $totalDenda = $dendaTelat + $dendaKondisi;

                // 1. Simpan ke tabel Pengembalian
                Pengembalian::create([
                    'peminjaman_id' => $peminjaman->id,
                    'tgl_kembali' => $request->tgl_kembali,
                    'kondisi_kembali' => $request->kondisi_kembali,
                    'denda' => $totalDenda,
                    'petugas_id' => auth()->id(),
                ]);

                // 2. REVISI UTAMA: Status transaksi SELALU di-update ke 'dikembalikan'
                $peminjaman->update(['status' => 'dikembalikan']);

                // 3. Increment Stok Alat
                app()->instance('skip_alat_log', true);
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

    public function pengembalianIndex(Request $request)
    {
        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat'])
            ->where('status', 'dipinjam')
            ->get();

        $query = Pengembalian::with([
            'peminjaman' => function ($q) {
                $q->withTrashed()->with([
                    'user' => fn($u) => $u->withTrashed(),
                    'detailPinjams.alat' => fn($a) => $a->withTrashed()
                ]);
            },
            'petugas' => fn($p) => $p->withTrashed()
        ]);

        $status = $request->input('status');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('peminjaman.user', function ($userQuery) use ($search) {
                    $userQuery->withTrashed()->where('name', 'like', "%{$search}%");
                })->orWhereHas('peminjaman.detailPinjams.alat', function ($alatQuery) use ($search) {
                    $alatQuery->withTrashed()->where('nama_alat', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('status')) {
            $query->whereHas('peminjaman', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        if ($request->filled('tgl_mulai') && $request->filled('tgl_selesai')) {
            if ($request->tgl_selesai < $request->tgl_mulai) {
                return back()->with('error', 'Tanggal "Sampai" tidak boleh lebih awal dari tanggal "Dari".');
            }
            $query->whereBetween('tgl_kembali', [$request->tgl_mulai, $request->tgl_selesai]);
        } elseif ($request->filled('tgl_mulai')) {
            $query->whereDate('tgl_kembali', '>=', $request->tgl_mulai);
        } elseif ($request->filled('tgl_selesai')) {
            $query->whereDate('tgl_kembali', '<=', $request->tgl_selesai);
        }

        $pengembalians = $query->latest()->paginate(10)->withQueryString();

        return view('admin.pengembalian.index', compact('pengembalians', 'peminjamans'));
    }

    public function destroyPengembalian($id)
    {
        DB::beginTransaction();
        try {
            $pengembalian = Pengembalian::findOrFail($id);
            $pengembalian->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Data pengembalian berhasil dibatalkan, status kembali Dipinjam, dan stok alat berhasil disesuaikan!');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function indexPesan(Request $request)
    {
        $status = $request->input('status');

        $pesans = PesanPerbaikan::with(['pengembalian.peminjaman.user', 'petugas', 'admin'])
            ->when($status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pesan.index', compact('pesans', 'status'));
    }

    public function updatePesan(Request $request, $id)
    {
        $request->validate([
            'aksi' => 'required|in:perbaiki,batal',
            'admin_catatan' => 'required|string|min:5|max:500',
        ]);

        $pesan = PesanPerbaikan::findOrFail($id);

        if ($request->aksi === 'perbaiki') {
            DB::beginTransaction();
            try {
                $pengembalian = $pesan->pengembalian;

                if ($pengembalian) {
                    $pengembalian->delete();
                }

                $pesan->update([
                    'status' => 'selesai',
                    'admin_id' => auth()->id(),
                    'admin_catatan' => $request->admin_catatan
                ]);

                DB::commit();

                return back()->with('success', 'Laporan diperbaiki: pengembalian berhasil di-reset, stok disesuaikan, & status kembali dipinjam.');
                
            } catch (\Exception $e) {
                DB::rollBack();

                return back()->with('error', 'Gagal memproses laporan: ' . $e->getMessage());
            }
        } else {
            $pesan->update([
                'status' => 'dibaca',
                'admin_id' => auth()->id(),
                'admin_catatan' => $request->admin_catatan
            ]);

            return back()->with('success', 'Laporan dibatalkan/ditolak.');
        }
    }
}