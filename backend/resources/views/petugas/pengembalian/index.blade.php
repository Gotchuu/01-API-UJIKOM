@extends('layouts.app')

@section('title', 'Pemantauan Pengembalian')
@section('header-title', 'Daftar Pengembalian Alat')

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- TABEL 1: PERMINTAAN KONFIRMASI PENGEMBALIAN --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 mb-6">
        <div class="p-5 border-b border-gray-200 bg-amber-50 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-amber-900">Perlu Verifikasi Fisik Barang</h3>
                <p class="text-xs text-amber-700">Daftar peminjam yang mengajukan pengembalian alat hari ini.</p>
            </div>
            <span class="bg-amber-200 text-amber-900 text-xs font-bold px-3 py-1 rounded-full">
                {{ isset($menungguKonfirmasi) ? $menungguKonfirmasi->where('status', 'diproses')->count() : 0 }} Pengajuan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Detail Alat</th>
                        <th class="py-3 px-4 border-b">Batas Rencana Kembali</th>
                        <th class="py-3 px-4 border-b text-center">Status Transaksi</th>
                        <th class="py-3 px-4 border-b text-center">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($menungguKonfirmasi as $p)
                        <tr class="hover:bg-gray-50 transition align-top">
                            <td class="py-3 px-4 border-b font-semibold text-gray-900">
                                {{ $p->user->name ?? 'User Dihapus' }}
                            </td>
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc list-inside space-y-0.5 text-xs">
                                    @foreach($p->detailPinjams as $d)
                                        <li>{{ $d->alat->nama_alat ?? 'Alat Dihapus' }} <b>({{ $d->jumlah }} unit)</b></li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4 border-b text-xs font-medium text-gray-700">
                                {{ \Carbon\Carbon::parse($p->tgl_kembali_plan)->format('d M Y') }}
                            </td>
                            
                            <!-- Status Dinamis Petugas -->
                            <td class="py-3 px-4 border-b text-center">
                                @if($p->status == 'diproses')
                                    <span class="px-2.5 py-1 rounded text-xs font-bold bg-amber-100 text-amber-800">Menunggu Cek</span>
                                @elseif($p->status == 'dipinjam')
                                    <span class="px-2.5 py-1 rounded text-xs font-bold bg-blue-100 text-blue-800">Dipinjam</span>
                                @else
                                    <span class="px-2.5 py-1 rounded text-xs font-bold bg-gray-100 text-gray-800">{{ ucfirst($p->status) }}</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 border-b text-center">
                                <button onclick="openModalProses({{ json_encode($p) }})" 
        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition shadow-sm">
        Proses Pengembalian
    </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500 text-xs">
                                Belum ada pengajuan pengembalian barang yang aktif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3 bg-gray-50 border-t border-gray-200">{{ $menungguKonfirmasi->links() }}</div>
        </div>
    </div>

    {{-- TABEL 2: HISTORI RIWAYAT PENGEMBALIAN SELESAI --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
            <h3 class="text-base font-bold text-gray-800 whitespace-nowrap">Riwayat & Pemantauan Pengembalian Selesai</h3>
            
            <form action="{{ route('petugas.pengembalian.index') }}" method="GET" class="flex flex-wrap md:flex-nowrap items-center gap-2 w-full xl:w-auto">
                <!-- Search Input Nama Peminjam/Alat -->
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari peminjam / alat..." class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-full md:w-40">

                <div>
                    <input type="date" id="tgl_mulai" name="tgl_mulai" value="{{ request('tgl_mulai') }}" 
                        onchange="updateMinDate()"
                        class="text-xs border border-gray-300 rounded-lg p-1.5 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <input type="date" id="tgl_selesai" name="tgl_selesai" value="{{ request('tgl_selesai') }}" 
                        class="text-xs border border-gray-300 rounded-lg p-1.5 focus:ring-2 focus:ring-blue-500">
                </div>

                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-xs px-3 py-1.5 rounded-lg font-semibold transition">
                    Filter
                </button>

                @if(request('tgl_mulai') || request('tgl_selesai') || request('search'))
                    <a href="{{ route('petugas.pengembalian.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs px-2.5 py-1.5 rounded-lg font-semibold transition" title="Reset Filter">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Alat yang Dikembalikan</th>
                        <th class="py-3 px-4 border-b">Tgl Kembali / Tenggat</th>
                        <th class="py-3 px-4 border-b">Kondisi Barang</th>
                        <th class="py-3 px-4 border-b">Denda</th>
                        <th class="py-3 px-4 border-b">Petugas Verifikasi</th>
                        <th class="py-3 px-4 border-b text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($pengembalians as $item)
                        <tr class="hover:bg-gray-50 transition align-top">
                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $item->peminjaman->user->name ?? 'User Dihapus' }}
                            </td>
                            <!-- Kolom Alat yang Dikembalikan -->
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc list-inside space-y-0.5 text-xs">
                                    @if($item->peminjaman && $item->peminjaman->detailPinjams)
                                        @foreach($item->peminjaman->detailPinjams as $d)
                                            <li>{{ $d->alat->nama_alat ?? 'Alat Dihapus' }} <span class="font-semibold text-gray-600">({{ $d->jumlah }} unit)</span></li>
                                        @endforeach
                                    @else
                                        <li class="text-gray-400 italic">Data alat tidak ditemukan</li>
                                    @endif
                                </ul>
                            </td>
                            <td class="py-3 px-4 border-b text-xs">
                                <div class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($item->tgl_kembali)->format('d M Y') }}</div>
                                @if($item->peminjaman)
                                    @php
                                        $tglKembali = \Carbon\Carbon::parse($item->tgl_kembali)->startOfDay();
                                        $tglPlan = \Carbon\Carbon::parse($item->peminjaman->tgl_kembali_plan)->startOfDay();
                                        $selisihHari = (int) $tglPlan->diffInDays($tglKembali, false);
                                    @endphp
                                    @if($selisihHari > 0)
                                        <div class="text-[10px] text-rose-600 font-bold mt-0.5">(Telat {{ $selisihHari }} hari)</div>
                                    @else
                                        <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">(Tepat Waktu)</div>
                                    @endif
                                @endif
                            </td>
                            <td class="py-3 px-4 border-b">
                                @php
                                    $k = strtolower($item->kondisi_kembali);
                                    $warnaKembali = str_contains($k, 'hilang') ? 'bg-red-100 text-red-800' : (str_contains($k, 'berat') ? 'bg-orange-100 text-orange-800' : (str_contains($k, 'ringan') ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'));
                                @endphp
                                <span class="px-2.5 py-1 rounded text-xs font-semibold {{ $warnaKembali }}">
                                    {{ $item->kondisi_kembali }}
                                </span>
                            </td>
                            <td class="py-3 px-4 border-b font-bold text-gray-900">
                                Rp {{ number_format($item->denda, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 border-b text-xs">
                                {{ $item->petugas->name ?? 'Sistem' }}
                            </td>
                            <td class="py-3 px-4 border-b text-center">
                                <button onclick='openPesan({{ $item->id }}, @json($item->peminjaman->user->name ?? "N/A"), @json($item->peminjaman && $item->peminjaman->detailPinjams ? $item->peminjaman->detailPinjams->map(fn($d) => ($d->alat->nama_alat ?? "Alat Dihapus") . " (" . $d->jumlah . " unit)")->join(", ") : ""))' class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 rounded text-xs font-semibold shadow-sm transition">
                                    Lapor
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-gray-500 text-xs">Belum ada riwayat pengembalian alat yang selesai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3 bg-gray-50 border-t border-gray-200">{{ $pengembalians->links() }}</div>
        </div>
    </div>

   {{-- MODAL PROSES PENGEMBALIAN PETUGAS (PRESISI SEPERTI ADMIN) --}}
    <div id="modalProses" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200 mb-4">
                <h4 class="text-lg font-bold text-gray-800">Form Pengembalian Alat</h4>
                <button type="button" onclick="closeModalProses()" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <form action="{{ route('petugas.pengembalian.store') }}" method="POST">
                @csrf
                <input type="hidden" name="peminjaman_id" id="proses_peminjaman_id">

                <!-- Nama Peminjam -->
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Nama Peminjam</label>
                    <input type="text" id="proses_peminjam_name" class="w-full text-sm bg-gray-100 border border-gray-300 rounded-lg p-2.5 text-gray-700 font-semibold focus:outline-none" readonly>
                </div>

                <!-- Box Daftar Alat yang Dipinjam -->
                <div class="mb-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Daftar Alat yang Dipinjam:</label>
                    <ul id="proses_detail_alat_list" class="list-disc pl-5 space-y-1 text-xs text-gray-800"></ul>
                </div>

                <!-- Tanggal Pengembalian -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pengembalian (Real-Time)</label>
                    <input type="date" name="tgl_kembali" id="proses_tgl_kembali" 
                        value="{{ date('Y-m-d') }}" 
                        min="{{ date('Y-m-d') }}" 
                        max="{{ date('Y-m-d') }}"
                        onchange="hitungDendaOtomatisPetugas()" 
                        class="w-full text-sm border border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:ring-blue-500 focus:outline-none" required>
                    <p class="text-[11px] text-gray-500 mt-1">* Petugas hanya dapat memproses pengembalian barang untuk hari ini.</p>
                </div>

                <!-- Kondisi Barang -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Barang</label>
                    <select name="kondisi_kembali" id="proses_kondisi_kembali" onchange="toggleFormDendaKondisiPetugas()" class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500 focus:outline-none" required>
                        <option value="Baik / Lengkap">Baik / Lengkap</option>
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                        <option value="Hilang">Hilang</option>
                    </select>
                </div>

                <!-- Deskripsi Kerusakan / Catatan Kehilangan -->
                <div id="wrapper_deskripsi_kondisi_petugas" class="mb-4 hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Kerusakan / Catatan Kehilangan</label>
                    <textarea name="deskripsi_kondisi" id="proses_deskripsi_kondisi" rows="3" placeholder="Jelaskan detail kerusakan atau kronologi kehilangan barang..." class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <!-- Box Estimasi Denda Telat -->
                <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900">
                    <div><strong>Estimasi Denda Telat:</strong> <span id="text_denda_telat_petugas">Rp 0</span></div>
                    <div class="text-gray-500 mt-0.5">* Tarik denda Rp 1.000 / hari keterlambatan</div>
                </div>

                <!-- Input Denda Kerusakan/Kehilangan -->
                <div id="wrapper_denda_kondisi_petugas" class="mb-4 hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Denda Kerusakan / Kehilangan (Rp)</label>
                    <input type="number" name="denda_kondisi" id="proses_denda_kondisi" min="0" value="0" placeholder="Masukkan denda alat" class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500 focus:outline-none">
                </div>

                <!-- Tombol Aksi -->
                <div class="flex justify-end gap-2 pt-3 border-t border-gray-200">
                    <button type="button" onclick="closeModalProses()" class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-semibold transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 rounded-lg text-white font-semibold transition shadow-sm">Proses & Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL LAPOR PERBAIKAN --}}
    <div id="modalPesan" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-full max-w-md shadow-lg">
            <h4 class="font-bold mb-3 text-gray-800">Lapor Perbaikan Pengembalian</h4>
            <div id="pesanInfo" class="bg-gray-50 p-3 rounded text-xs mb-3 text-gray-700"></div>
            <div class="bg-blue-50 border border-blue-200 p-3 rounded text-xs mb-3">
                <div class="font-semibold text-blue-800">Detail Alat:</div>
                <ul id="pesanDetailAlat" class="list-disc pl-4 mt-1 text-gray-700"></ul>
            </div>
            <form action="{{ route('petugas.pesan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="pengembalian_id" id="pesan_id">
                <select name="jenis" required class="w-full border border-gray-300 rounded p-2 text-sm mb-3 focus:ring-2 focus:ring-amber-500">
                    <option value="kondisi">Kondisi Barang Salah</option>
                    <option value="denda">Denda Salah</option>
                    <option value="tanggal">Tanggal Salah</option>
                    <option value="lainnya">Lainnya</option>
                </select>
                <textarea name="pesan" rows="3" required placeholder="Tulis pesan untuk admin..." class="w-full border border-gray-300 rounded p-2 text-sm mb-3 focus:ring-2 focus:ring-amber-500"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closePesan()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded text-sm">Batal</button>
                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded text-sm font-semibold">Kirim</button>
                </div>
            </form>
        </div>
    </div>

   <script>
    let tglPlanGlobalPetugas = '';

   document.getElementById('proses_tgl_kembali').addEventListener('change', function() {
       const val = this.value;
       const today = new Date().toISOString().split('T')[0];
       if (val && val < today) {
           alert('Tanggal kembali tidak boleh sebelum hari ini.');
           this.value = today;
       }
   });

   function toggleDeskripsiKondisi() {
       const kondisi = document.getElementById('proses_kondisi').value;
       const wrapper = document.getElementById('proses_wrapper_deskripsi');
       const textarea = document.getElementById('proses_deskripsi_kondisi');
       if (kondisi === 'Baik & Lengkap') {
           wrapper.classList.add('hidden');
           textarea.removeAttribute('required');
           textarea.value = '';
       } else {
           wrapper.classList.remove('hidden');
           textarea.setAttribute('required', 'required');
       }
   }

   // Dipanggil saat tombol 'Proses Pengembalian' di tabel atas diklik
    function openModalProses(peminjaman) {
        document.getElementById('proses_peminjaman_id').value = peminjaman.id;
        document.getElementById('proses_peminjam_name').value = peminjaman.user ? peminjaman.user.name : 'N/A';
        tglPlanGlobalPetugas = peminjaman.tgl_kembali_plan;

        const listContainer = document.getElementById('proses_detail_alat_list');
        listContainer.innerHTML = '';

        if (peminjaman.detail_pinjams && peminjaman.detail_pinjams.length > 0) {
            peminjaman.detail_pinjams.forEach(detail => {
                const namaAlat = detail.alat ? detail.alat.nama_alat : 'Alat Dihapus';
                const li = document.createElement('li');
                li.innerHTML = `<span class="font-semibold text-gray-900">${namaAlat}</span> — <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-[11px] font-bold">${detail.jumlah} unit</span>`;
                listContainer.appendChild(li);
            });
        } else {
            listContainer.innerHTML = '<li class="text-gray-500 italic">Tidak ada rincian alat.</li>';
        }

        document.getElementById('modalProses').classList.remove('hidden');
        document.getElementById('modalProses').classList.add('flex');
        hitungDendaOtomatisPetugas();
        toggleFormDendaKondisiPetugas();
    }

    function closeModalProses() {
        document.getElementById('modalProses').classList.add('hidden');
        document.getElementById('modalProses').classList.remove('flex');
    }

    function toggleFormDendaKondisiPetugas() {
        const kondisi = document.getElementById('proses_kondisi_kembali').value;
        const wrapperDenda = document.getElementById('wrapper_denda_kondisi_petugas');
        const wrapperDeskripsi = document.getElementById('wrapper_deskripsi_kondisi_petugas');
        const inputDeskripsi = document.getElementById('proses_deskripsi_kondisi');

        if (kondisi !== 'Baik / Lengkap' && kondisi !== 'Baik & Lengkap') {
            wrapperDenda.classList.remove('hidden');
            wrapperDeskripsi.classList.remove('hidden');
            inputDeskripsi.setAttribute('required', 'required');
        } else {
            wrapperDenda.classList.add('hidden');
            wrapperDeskripsi.classList.add('hidden');
            document.getElementById('proses_denda_kondisi').value = 0;
            inputDeskripsi.value = '';
            inputDeskripsi.removeAttribute('required');
        }
    }

    function hitungDendaOtomatisPetugas() {
        const inputVal = document.getElementById('proses_tgl_kembali').value;
        if (!inputVal || !tglPlanGlobalPetugas) return;

        const tglKembaliInput = new Date(inputVal);
        const tglPlan = new Date(tglPlanGlobalPetugas);

        tglKembaliInput.setHours(0, 0, 0, 0);
        tglPlan.setHours(0, 0, 0, 0);

        const diffTime = tglKembaliInput - tglPlan;
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays > 0) {
            const totalDendaTelat = diffDays * 1000;
            document.getElementById('text_denda_telat_petugas').innerText = 'Rp ' + totalDendaTelat.toLocaleString('id-ID') + ' (' + diffDays + ' hari keterlambatan)';
        } else {
            document.getElementById('text_denda_telat_petugas').innerText = 'Rp 0 (Tepat Waktu)';
        }
    }

    // Modal Lapor Pesan Perbaikan
    function openPesan(id, nama, detail){ 
        document.getElementById('pesan_id').value = id; 
        document.getElementById('pesanInfo').innerText = 'Pengembalian ID: #' + id + ' - ' + nama; 
        const ul = document.getElementById('pesanDetailAlat'); 
        ul.innerHTML = ''; 
        if (detail) {
            detail.split(', ').forEach(a => { 
                const li = document.createElement('li'); 
                li.innerText = a; 
                ul.appendChild(li); 
            }); 
        }
        document.getElementById('modalPesan').classList.remove('hidden'); 
        document.getElementById('modalPesan').classList.add('flex'); 
    }

    function closePesan(){ 
        document.getElementById('modalPesan').classList.add('hidden'); 
        document.getElementById('modalPesan').classList.remove('flex'); 
    }

    function updateMinDate() {
        const tglMulai = document.getElementById('tgl_mulai').value;
        const tglSelesaiInput = document.getElementById('tgl_selesai');
        
        if (tglMulai) {
            tglSelesaiInput.min = tglMulai;
            if (tglSelesaiInput.value && tglSelesaiInput.value < tglMulai) {
                tglSelesaiInput.value = tglMulai;
            }
        } else {
            tglSelesaiInput.removeAttribute('min');
        }
    }

    document.addEventListener('DOMContentLoaded', updateMinDate);
</script>
@endsection