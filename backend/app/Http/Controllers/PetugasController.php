1|<?php
2|
3|namespace App\Http\Controllers;
4|
5|use App\Http\Requests\Pengembalian\StorePengembalianRequest;
6|use App\Models\Alat;
7|use App\Models\Peminjaman;
8|use App\Models\Pengembalian;
9|use App\Models\PesanPerbaikan;
10|use Barryvdh\DomPDF\Facade\Pdf;
11|use Carbon\Carbon;
12|use Exception;
13|use Illuminate\Http\Request;
14|use Illuminate\Support\Facades\DB;
15|
16|class PetugasController extends Controller
17|{
18|    /**
19|     * Dashboard Petugas
20|     */
21|    public function dashboard()
22|    {
23|        $menunggu = Peminjaman::where('status', 'diajukan')->count();
24|        $dipinjam = Peminjaman::where('status', 'dipinjam')->count();
25|        
26|        // REVISI: Hitung telat secara dinamis (status 'dipinjam' & tgl_kembali_plan < hari ini)
27|        $telat = Peminjaman::where('status', 'dipinjam')
28|            ->whereDate('tgl_kembali_plan', '<', Carbon::today())
29|            ->count();
30|
31|        $kembaliHariIni = Pengembalian::whereDate('created_at', Carbon::today())->count();
32|        $totalAlat = Alat::count();
33|        $stokMenipis = Alat::where('stok', '<=', 3)->count();
34|        $recent = Peminjaman::with(['user', 'detailPinjams.alat'])->latest()->take(5)->get();
35|
36|        return view('petugas.dashboard', compact('menunggu', 'dipinjam', 'telat', 'kembaliHariIni', 'totalAlat', 'stokMenipis', 'recent'));
37|    }
38|
39|    /**
40|     * Menampilkan daftar pengajuan peminjaman (Persetujuan Peminjaman)
41|     */
42|    public function indexPeminjaman(Request $request)
43|    {
44|        $search = $request->input('search');
45|
46|        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat'])
47|            ->where('status', 'diajukan')
48|            ->when($search, function ($query, $search) {
49|                return $query->whereHas('user', function ($q) use ($search) {
50|                    $q->where('name', 'like', "%{$search}%");
51|                });
52|            })
53|            ->latest()
54|            ->get();
55|
56|        $allAlats = Alat::where('stok', '>', 0)->orderBy('nama_alat', 'asc')->get();
57|
58|        return view('petugas.peminjaman.index', compact('peminjamans', 'search', 'allAlats'));
59|    }
60|
61|    /**
62|     * Menyetujui pengajuan peminjaman awal oleh Petugas
63|     */
64|    public function setujuiPeminjaman(Request $request, $id)
65|    {
66|        try {
67|            DB::transaction(function () use ($id) {
68|                $peminjaman = Peminjaman::with('detailPinjams.alat')->findOrFail($id);
69|
70|                if ($peminjaman->status !== 'diajukan') {
71|                    throw new Exception('Peminjaman ini sudah diproses.');
72|                }
73|
74|                app()->instance('skip_alat_log', true);
75|
76|                foreach ($peminjaman->detailPinjams as $detail) {
77|                    $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);
78|                    if ($alat->stok < $detail->jumlah) {
79|                        throw new Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
80|                    }
81|                    $alat->decrement('stok', $detail->jumlah);
82|                }
83|
84|                $peminjaman->status = 'dipinjam';
85|                $peminjaman->save(); 
86|            });
87|
88|            return redirect()->back()->with('success', 'Peminjaman berhasil disetujui!');
89|        } catch (Exception $e) {
90|            return redirect()->back()->with('error', $e->getMessage());
91|        }
92|    }
93|
94|    /**
95|     * Menolak Peminjaman
96|     */
97|    public function tolakPeminjaman($id)
98|    {
99|        try {
100|            $peminjaman = Peminjaman::findOrFail($id);
101|
102|            if ($peminjaman->status == 'diajukan') {
103|                $peminjaman->delete();
104|
105|                return redirect()->back()->with('success', 'Pengajuan peminjaman berhasil ditolak.');
106|            }
107|
108|            return redirect()->back()->with('error', 'Status peminjaman sudah berubah.');
109|        } catch (Exception $e) {
110|            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
111|        }
112|    }
113|
114|    /**
115|     * Memperbarui data pengajuan peminjaman oleh Petugas (Khusus Status 'diajukan')
116|     */
117|    public function updatePeminjamanRequest(Request $request, $id)
118|    {
119|        $request->validate([
120|            'tgl_pinjam' => 'required|date',
121|            'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
122|            'jumlah' => 'nullable|array',
123|            'jumlah.*' => 'required|integer|min:1',
124|            'new_alat_id' => 'nullable|exists:alat,id',
125|            'new_jumlah' => 'required|integer|min:1',
126|        ]);
127|
128|        DB::beginTransaction();
129|        try {
130|            $peminjaman = Peminjaman::with('detailPinjams')->findOrFail($id);
131|
132|            if ($peminjaman->status !== 'diajukan') {
133|                throw new \Exception("Hanya peminjaman berstatus 'diajukan' yang dapat diubah.");
134|            }
135|
136|            $peminjaman->update([
137|                'tgl_pinjam' => $request->tgl_pinjam,
138|                'tgl_kembali_plan' => $request->tgl_kembali_plan,
139|            ]);
140|
141|            if ($request->has('jumlah')) {
142|                foreach ($request->jumlah as $detailId => $jumlahBaru) {
143|                    $detail = \App\Models\DetailPinjam::where('peminjaman_id', $peminjaman->id)->where('id', $detailId)->first();
144|                    if ($detail) {
145|                        $alat = Alat::findOrFail($detail->alat_id);
146|                        if ($alat->stok < $jumlahBaru) {
147|                            throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi (Tersedia: {$alat->stok}).");
148|                        }
149|                        $detail->update(['jumlah' => $jumlahBaru]);
150|                    }
151|                }
152|            }
153|
154|            if ($request->filled('new_alat_id') && $request->filled('new_jumlah')) {
155|                $alatBaru = Alat::findOrFail($request->new_alat_id);
156|                
157|                if ($alatBaru->stok < $request->new_jumlah) {
158|                    throw new \Exception("Stok alat '{$alatBaru->nama_alat}' tidak mencukupi (Tersedia: {$alatBaru->stok}).");
159|                }
160|
161|                $existingDetail = \App\Models\DetailPinjam::where('peminjaman_id', $peminjaman->id)
162|                                    ->where('alat_id', $request->new_alat_id)
163|                                    ->first();
164|
165|                if ($existingDetail) {
166|                    $totalJumlah = $existingDetail->jumlah + $request->new_jumlah;
167|                    if ($alatBaru->stok < $totalJumlah) {
168|                        throw new \Exception("Total stok alat '{$alatBaru->nama_alat}' tidak mencukupi.");
169|                    }
170|                    $existingDetail->update(['jumlah' => $totalJumlah]);
171|                } else {
172|                    \App\Models\DetailPinjam::create([
173|                        'peminjaman_id' => $peminjaman->id,
174|                        'alat_id' => $request->new_alat_id,
175|                        'jumlah' => $request->new_jumlah,
176|                    ]);
177|                }
178|            }
179|
180|            if ($peminjaman->detailPinjams()->count() === 0) {
181|                throw new \Exception("Peminjaman harus memiliki minimal 1 jenis alat.");
182|            }
183|
184|            DB::commit();
185|            return redirect()->back()->with('success', 'Request peminjaman berhasil diperbarui oleh petugas.');
186|        } catch (\Exception $e) {
187|            DB::rollback();
188|            return redirect()->back()->with('error', $e->getMessage());
189|        }
190|    }
191|
192|    public function destroyDetailItem($detailId)
193|    {
194|        try {
195|            $detail = \App\Models\DetailPinjam::with('peminjaman')->findOrFail($detailId);
196|            
197|            if ($detail->peminjaman->status !== 'diajukan') {
198|                return redirect()->back()->with('error', 'Hanya item berstatus diajukan yang bisa dihapus.');
199|            }
200|
201|            if ($detail->peminjaman->detailPinjams()->count() <= 1) {
202|                return redirect()->back()->with('error', 'Tidak bisa menghapus semua item. Minimal harus ada 1 item alat.');
203|            }
204|
205|            $detail->delete();
206|            return redirect()->back()->with('success', 'Item alat berhasil dihapus dari pengajuan.');
207|        } catch (\Exception $e) {
208|            return redirect()->back()->with('error', $e->getMessage());
209|        }
210|    }
211|
212|    /**
213|     * Menampilkan daftar pemantauan pengembalian alat
214|     */
215|    public function indexPengembalian(Request $request)
216|    {
217|        $search = $request->input('search');
218|
219|        $pengembalians = Pengembalian::with([
220|            'peminjaman' => function ($query) {
221|                $query->withTrashed()->with([
222|                    'user' => fn($q) => $q->withTrashed(),
223|                    'detailPinjams.alat' => fn($q) => $q->withTrashed()
224|                ]);
225|            },
226|            'petugas' => fn($q) => $q->withTrashed()
227|        ])
228|        ->when($search, function ($query, $search) {
229|            return $query->whereHas('peminjaman.user', function ($q) use ($search) {
230|                $q->withTrashed()->where('name', 'like', "%{$search}%");
231|            });
232|        })
233|        ->latest()
234|        ->paginate(10)->withQueryString();
235|
236|        // REVISI: Hanya ambil yang BELUM dikembalikan (status dipinjam / diproses)
237|        $menungguKonfirmasi = Peminjaman::with([
238|            'user' => fn($q) => $q->withTrashed(),
239|            'detailPinjams.alat' => fn($q) => $q->withTrashed()
240|        ])
        ->whereIn('status', ['menunggu_kembali', 'diproses', 'dipinjam'])
242|        ->when($search, function ($query, $search) {
243|            return $query->whereHas('user', function ($q) use ($search) {
244|                $q->withTrashed()->where('name', 'like', "%{$search}%");
245|            });
246|        })
247|        ->latest()
248|        ->paginate(10)->withQueryString();
249|
250|        return view('petugas.pengembalian.index', compact('pengembalians', 'menungguKonfirmasi', 'search'));
251|    }
252|
253|    /**
254|     * Proses Verifikasi Pengembalian Barang
255|     */
256|    public function storePengembalian(StorePengembalianRequest $request)
257|    {
258|        try {
259|            DB::transaction(function () use ($request) {
260|                $peminjaman = Peminjaman::with('detailPinjams')->lockForUpdate()->findOrFail($request->peminjaman_id);
261|
262|                if (! in_array($peminjaman->status, ['dipinjam', 'diproses'])) {
263|                    throw new Exception('Transaksi ini tidak sedang dalam proses pengembalian.');
264|                }
265|
266|                $tglKembali = Carbon::parse($request->tgl_kembali)->startOfDay();
267|                $tglPlan = Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();
268|                $dendaTelat = 0;
269|
270|                if ($tglKembali->greaterThan($tglPlan)) {
271|                    $selisihHari = max(0, (int) $tglPlan->diffInDays($tglKembali, false));
272|                    $dendaTelat = $selisihHari * 1000;
273|                }
274|
275|                $dendaKondisi = max(0, (int) ($request->denda_kondisi ?? 0));
276|                $totalDenda = $dendaTelat + $dendaKondisi;
277|
278|                Pengembalian::create([
279|                    'peminjaman_id' => $peminjaman->id,
280|                    'tgl_kembali' => $request->tgl_kembali,
281|                    'kondisi_kembali' => $request->kondisi_kembali,
282|                    'denda' => $totalDenda,
283|                    'petugas_id' => auth()->id(),
284|                ]);
285|
286|                // REVISI PERBAIKAN: Status transaksi SELALU diubah menjadi 'dikembalikan' setelah diproses
287|                $peminjaman->update(['status' => 'dikembalikan']);
288|
289|                app()->instance('skip_alat_log', true);
290|
291|                foreach ($peminjaman->detailPinjams as $detail) {
292|                    $alat = Alat::lockForUpdate()->find($detail->alat_id);
293|                    if ($alat) {
294|                        $alat->increment('stok', $detail->jumlah);
295|                    }
296|                }
297|            });
298|
299|            return redirect()->route('petugas.pengembalian.index')->with('success', 'Pengembalian barang berhasil diverifikasi dan diproses!');
300|        } catch (Exception $e) {
301|            return redirect()->back()->with('error', $e->getMessage());
302|        }
303|    }
304|
305|    public function storePesanPerbaikan(Request $request)
306|    {
307|        $request->validate([
308|            'pengembalian_id' => 'required|exists:pengembalian,id',
309|            'jenis' => 'required|in:kondisi,denda,tanggal,lainnya',
310|            'pesan' => 'required|string|min:10|max:500',
311|        ]);
312|
313|        PesanPerbaikan::create([
314|            'pengembalian_id' => $request->pengembalian_id,
315|            'petugas_id' => auth()->id(),
316|            'jenis' => $request->jenis,
317|            'pesan' => $request->pesan,
318|            'status' => 'terkirim',
319|        ]);
320|
321|        return back()->with('success', 'Pesan perbaikan terkirim ke admin!');
322|    }
323|
324|    public function indexPesan(Request $request)
325|    {
326|        $status = $request->input('status');
327|
328|        $pesans = PesanPerbaikan::with(['pengembalian.peminjaman.user', 'admin'])
329|            ->where('petugas_id', auth()->id())
330|            ->when($status, function ($q, $status) {
331|                $q->where('status', $status);
332|            })
333|            ->latest()
334|            ->paginate(10)
335|            ->withQueryString();
336|
337|        return view('petugas.pesan.index', compact('pesans', 'status'));
338|    }
339|
340|    public function indexLaporan(Request $request)
341|    {
342|        $dari = $request->input('dari', now()->subMonth()->toDateString());
343|        $sampai = $request->input('sampai', now()->toDateString());
344|        $status = $request->input('status');
345|
346|        // Validasi: tanggal sampai tidak boleh sebelum tanggal dari
347|        if ($request->filled('dari') && $request->filled('sampai')) {
348|            if ($sampai < $request->input('dari')) {
349|                return back()->with('error', 'Tanggal "Sampai" tidak boleh lebih awal dari tanggal "Dari".');
350|            }
351|        }
352|
353|        $query = Peminjaman::with(['user', 'detailPinjams.alat', 'pengembalian.petugas'])
354|            ->whereBetween('tgl_pinjam', [$dari, $sampai])
355|            ->when($status, fn($q) => $q->where('status', $status));
356|
357|        $peminjamans = $query->latest()->paginate(10)->withQueryString();
358|
359|        return view('petugas.laporan.index', compact('peminjamans', 'dari', 'sampai', 'status'));
360|    }
361|
362|    public function cetakPdf(Request $request)
363|    {
364|        $dari = $request->input('dari', now()->subMonth()->toDateString());
365|        $sampai = $request->input('sampai', now()->toDateString());
366|        $status = $request->input('status');
367|
368|        // Validasi: tanggal sampai tidak boleh sebelum tanggal dari
369|        if ($request->filled('dari') && $request->filled('sampai')) {
370|            if ($sampai < $request->input('dari')) {
371|                return back()->with('error', 'Tanggal "Sampai" tidak boleh lebih awal dari tanggal "Dari".');
372|            }
373|        }
374|
375|        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat', 'pengembalian.petugas'])
376|            ->whereBetween('tgl_pinjam', [$dari, $sampai])
377|            ->when($status, fn($q) => $q->where('status', $status))
378|            ->latest()
379|            ->get();
380|
381|        $pdf = Pdf::loadView('petugas.laporan.pdf', compact('peminjamans', 'dari', 'sampai', 'status'));
382|
383|        return $pdf->stream('Laporan-Peminjaman-' . $dari . '-sampai-' . $sampai . '.pdf');
384|    }
385|}
386|