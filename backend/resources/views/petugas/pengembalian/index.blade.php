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
                        <th class="py-3 px-4 border-b">Batas Kembalikan</th>
                        <th class="py-3 px-4 border-b text-center">Status</th>
                        <th class="py-3 px-4 border-b">Status Peminjaman</th>
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
                            <td class="py-3 px-4 border-b text-xs">{{ $p->tgl_kembali_plan }}</td>
                            
                            <!-- Status Dinamis Petugas -->
                            <td class="py-3 px-4 border-b text-center">
                                @if($p->status == 'diproses')
                                    <span class="px-2.5 py-1 rounded text-xs font-bold bg-amber-100 text-amber-800">Menunggu Cek</span>
                                @elseif($p->status == 'diproses')
                                    <span class="px-2.5 py-1 rounded text-xs font-bold bg-purple-100 text-purple-800">Diproses</span>
                                @elseif($p->status == 'dipinjam')
                                    <span class="px-2.5 py-1 rounded text-xs font-bold bg-blue-100 text-blue-800">Dipinjam</span>
                                @else
                                    <span class="px-2.5 py-1 rounded text-xs font-bold bg-amber-100 text-amber-800">{{ ucfirst($p->status) }}</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 border-b text-center">
                                <span class="text-xs font-bold text-gray-700">{{ ucfirst($p->status) }}</span>
                            </td>
                            <td class="py-3 px-4 border-b text-center">
                                <button onclick='openModalProses({{ $p->id }}, @json($p->user->name ?? "N/A"), "{{ date('Y-m-d') }}", @json($p->detailPinjams->map(fn($d) => ($d->alat->nama_alat ?? "Alat Dihapus") . " (" . $d->jumlah . " unit)")->join(", ")))' 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition">
                                    Proses Pengembalian
                                </button>
                                @if($p->status == 'diproses')
                                <form action="{{ route('peminjam.peminjaman.destroy', $p->id) }}" method="POST" class="inline mt-1" onsubmit="return confirm('Batalkan pengajuan pengembalian ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white px-2 py-1 rounded text-[10px] font-semibold transition">Batalkan</button>
                                </form>
                                @endif
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
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h3 class="text-base font-bold text-gray-800">Riwayat & Pemantauan Pengembalian Selesai</h3>
            <form action="{{ route('petugas.pengembalian.index') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama..." class="text-xs border rounded px-2 py-1 w-32 focus:outline-none focus:ring-1 focus:ring-emerald-300">
                <div class="flex items-center gap-1">
                    <label class="text-xs text-gray-600 whitespace-nowrap">Dari:</label>
                    <input type="date" name="tgl_mulai" value="{{ request('tgl_mulai') }}" class="text-xs border rounded px-1 py-1 w-28">
                </div>
                <div class="flex items-center gap-1">
                    <label class="text-xs text-gray-600 whitespace-nowrap">Sampai:</label>
                    <input type="date" name="tgl_selesai" value="{{ request('tgl_selesai') }}" min="{{ request('tgl_mulai') ?: date('Y-m-d') }}" class="text-xs border rounded px-1 py-1 w-28">
                </div>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 text-xs font-semibold rounded transition">Filter</button>
                @if(request('search') || request('tgl_mulai') || request('tgl_selesai'))
                <a href="{{ route('petugas.pengembalian.index') }}" class="text-xs text-gray-500 hover:text-red-600">✕</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Tanggal Kembali</th>
                        <th class="py-3 px-4 border-b">Kondisi Barang</th>
                        <th class="py-3 px-4 border-b">Denda</th>
                        <th class="py-3 px-4 border-b">Petugas Verifikasi</th>
                        <th class="py-3 px-4 border-b text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($pengembalians as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $item->peminjaman->user->name ?? 'User Dihapus' }}
                            </td>
                            <td class="py-3 px-4 border-b text-xs">{{ $item->tgl_kembali }}</td>
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
                                <button onclick='openPesan({{ $item->id }}, @json($item->peminjaman->user->name ?? "N/A"), @json($item->peminjaman->detailPinjams->map(fn($d) => ($d->alat->nama_alat ?? "Alat Dihapus") . " (" . $d->jumlah . " unit)")->join(", ")))' class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 rounded text-xs font-semibold">
                                    Lapor
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-500 text-xs">Belum ada riwayat pengembalian alat yang selesai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3 bg-gray-50 border-t border-gray-200">{{ $pengembalians->links() }}</div>
        </div>
    </div>

   {{-- MODAL PROSES PENGEMBALIAN PETUGAS (DILENGKAPI RINCIAN ALAT & KALKULASI DENDA) --}}
    <div id="modalProses" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-xl w-full max-w-lg shadow-xl border border-gray-100">
            <div class="flex justify-between items-center border-b border-gray-200 pb-3 mb-4">
                <h3 class="text-base font-bold text-gray-800">Verifikasi Pengembalian Alat</h3>
                <button type="button" onclick="closeModalProses()" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>

            <p id="prosesPeminjamText" class="text-xs text-gray-600 font-medium mb-3"></p>

            <!-- Box Ringkasan Alat yang Dipinjam -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4 text-xs">
                <div class="font-bold text-blue-900 mb-1">Daftar Barang yang Dikembalikan:</div>
                <ul id="prosesDetailAlat" class="list-disc list-inside space-y-0.5 text-blue-800"></ul>
            </div>

            <form action="{{ route('petugas.pengembalian.store') }}" method="POST">
                @csrf
                <input type="hidden" name="peminjaman_id" id="proses_peminjaman_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tgl Kembali Real <span class="text-red-500">*</span></label>
                        <input type="date" name="tgl_kembali" id="proses_tgl_kembali" min="{{ date('Y-m-d') }}" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kondisi Barang <span class="text-red-500">*</span></label>
                        <select name="kondisi_kembali" id="proses_kondisi" onchange="toggleDeskripsiKondisi()" required class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="Baik & Lengkap">Baik & Lengkap</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                            <option value="Hilang">Hilang</option>
                        </select>
                    </div>
                </div>

                <div id="proses_wrapper_deskripsi" class="mb-4 hidden">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Deskripsi Kerusakan / Catatan Kehilangan</label>
                    <textarea name="deskripsi_kondisi" id="proses_deskripsi_kondisi" rows="3" placeholder="Jelaskan detail kerusakan atau kronologi kehilangan barang..." class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Denda Kerusakan / Kerugian (Rp)</label>
                    <input type="number" name="denda_kondisi" value="0" min="0" placeholder="0" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <span class="text-[11px] text-gray-500 mt-1 block">* Denda keterlambatan hari akan dikalkulasi otomatis oleh sistem.</span>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeModalProses()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-4 py-2 rounded-lg text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-xs transition shadow-sm">
                        Konfirmasi & Simpan
                    </button>
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

   function openModalProses(id, nama, tgl, detail) {
        document.getElementById('proses_peminjaman_id').value = id;
        document.getElementById('proses_tgl_kembali').value = tgl;
        document.getElementById('prosesPeminjamText').innerText = 'Memproses Pengembalian Transaksi #' + id + ' a/n ' + nama;
        
        // Render Rincian Alat ke dalam Modal
        const ul = document.getElementById('prosesDetailAlat');
        ul.innerHTML = '';
        if (detail) {
            detail.split(', ').forEach(a => {
                const li = document.createElement('li');
                li.innerText = a;
                ul.appendChild(li);
            });
        }

        document.getElementById('modalProses').classList.remove('hidden');
        document.getElementById('modalProses').classList.add('flex');
    }

    function closeModalProses() {
        document.getElementById('modalProses').classList.add('hidden');
        document.getElementById('modalProses').classList.remove('flex');
    }

    // Modal Lapor Pesan Perbaikan
    function openPesan(id, nama, detail){ 
        document.getElementById('pesan_id').value = id; 
        document.getElementById('pesanInfo').innerText = 'Pengembalian ID: #' + id + ' - ' + nama; 
        const ul = document.getElementById('pesanDetailAlat'); 
        ul.innerHTML = ''; 
        detail.split(', ').forEach(a => { 
            const li = document.createElement('li'); 
            li.innerText = a; 
            ul.appendChild(li); 
        }); 
        document.getElementById('modalPesan').classList.remove('hidden'); 
        document.getElementById('modalPesan').classList.add('flex'); 
    }

    function closePesan(){ 
        document.getElementById('modalPesan').classList.add('hidden'); 
        document.getElementById('modalPesan').classList.remove('flex'); 
    }
</script>
@endsection