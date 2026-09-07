@extends('layouts.app')

@section('title', 'Persetujuan & Pengembalian - Petugas')
@section('header-title', 'Daftar Pengajuan & Peminjaman Alat')

@section('content')
    {{-- Alert Messages --}}
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

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Manajemen Peminjaman & Pengembalian</h3>
            <form action="{{ route('petugas.peminjaman.index') }}" method="GET" class="flex w-full md:w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peminjam..."
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('petugas.peminjaman.index') }}"
                       class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 text-sm rounded-lg flex items-center transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Tanggal Pinjam</th>
                        <th class="py-3 px-4 border-b">Rencana Kembali</th>
                        <th class="py-3 px-4 border-b">Detail Alat</th>
                        <th class="py-3 px-4 border-b">Status</th>
                        <th class="py-3 px-4 border-b text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($peminjamans as $item)
                        <tr class="hover:bg-gray-50 transition align-top">
                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $item->user->name ?? 'User Dihapus' }}
                            </td>
                            <td class="py-3 px-4 border-b">{{ $item->tgl_pinjam }}</td>
                            <td class="py-3 px-4 border-b">{{ $item->tgl_kembali_plan }}</td>
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    @foreach($item->detailPinjams as $detail)
                                        <li>
                                            <span class="font-semibold">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</span> 
                                            <span class="font-semibold text-gray-500">({{ $detail->jumlah }} unit)</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4 border-b">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                    @if($item->status == 'diajukan') bg-amber-100 text-amber-800
                                    @elseif($item->status == 'dipinjam') bg-blue-100 text-blue-800
                                    @elseif($item->status == 'dikembalikan') bg-emerald-100 text-emerald-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 border-b text-center">
                                @if($item->status == 'diajukan')
                                    {{-- Tombol Setujui dan Tolak Pengajuan --}}
                                    <div class="flex justify-center items-center space-x-2">
                                        <form action="{{ route('petugas.peminjaman.setujui', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Setujui peminjaman alat ini?')"
                                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition shadow-sm">
                                                Setujui
                                            </button>
                                        </form>

                                        <form action="{{ route('petugas.peminjaman.tolak', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Yakin ingin menolak pengajuan peminjaman ini?')"
                                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition shadow-sm">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @elseif($item->status == 'dipinjam')
                                    {{-- Tombol Proses Pengembalian via Modal (Sama Seperti Admin) --}}
                                    <button onclick="openModalPengembalian({{ json_encode($item) }})" 
                                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition shadow-sm">
                                        Proses Pengembalian
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-500">Tidak ada transaksi peminjaman aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL PROCESS PENGEMBALIAN (PERSIS SEPERTI MILIK ADMIN) --}}
    <div id="modalPengembalian" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200 mb-4">
                <h4 class="text-lg font-bold text-gray-800">Form Pengembalian Alat (Petugas)</h4>
                <button onclick="closeModalPengembalian()" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <form action="{{ route('petugas.pengembalian.store') }}" method="POST">
                @csrf
                <input type="hidden" name="peminjaman_id" id="modal_peminjaman_id">

                <!-- Informasi Nama Peminjam -->
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Nama Peminjam</label>
                    <input type="text" id="modal_peminjam_name" class="w-full text-sm bg-gray-100 border border-gray-300 rounded-lg p-2.5 text-gray-700 font-semibold" readonly>
                </div>

                <!-- DETAIL ALAT YANG DIPINJAM -->
                <div class="mb-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Daftar Alat yang Dipinjam:</label>
                    <ul id="modal_detail_alat_list" class="list-disc pl-5 space-y-1 text-xs text-gray-800">
                        {{-- Diisi secara otomatis oleh JavaScript --}}
                    </ul>
                </div>

                <!-- Tanggal Pengembalian -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pengembalian</label>
                    <input type="date" name="tgl_kembali" id="modal_tgl_kembali" value="{{ date('Y-m-d') }}" 
                        onchange="hitungDendaOtomatis()" class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500" required>
                </div>

                <!-- Kondisi Kembali -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Barang</label>
                    <select name="kondisi_kembali" id="modal_kondisi_kembali" onchange="toggleFormDendaKondisi()" class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500" required>
                        <option value="Baik / Lengkap">Baik / Lengkap</option>
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                        <option value="Hilang">Hilang</option>
                    </select>
                </div>

                <!-- Input Deskripsi Kerusakan / Kehilangan (Tampil Jika Rusak / Hilang) -->
                <div id="wrapper_deskripsi_kondisi" class="mb-4 hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Kerusakan / Catatan Kehilangan</label>
                    <textarea name="deskripsi_kondisi" id="modal_deskripsi_kondisi" rows="3" placeholder="Jelaskan detail kerusakan atau kronologi kehilangan barang..." class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500"></textarea>
                </div>

                <!-- Denda Keterlambatan Info -->
                <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900">
                    <div><strong>Estimasi Denda Telat:</strong> <span id="text_denda_telat">Rp 0</span></div>
                    <div class="text-gray-500 mt-0.5">* Tarik denda Rp 1.000 / hari keterlambatan</div>
                </div>

                <!-- Form Denda Kondisi (Tampil Jika Rusak / Hilang) -->
                <div id="wrapper_denda_kondisi" class="mb-4 hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Denda Kerusakan / Kehilangan (Rp)</label>
                    <input type="number" name="denda_kondisi" id="modal_denda_kondisi" min="0" value="0" placeholder="Masukkan denda alat" class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-200">
                    <button type="button" onclick="closeModalPengembalian()" class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 rounded-lg text-white font-semibold">Proses & Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let tglPlanGlobal = '';

        function openModalPengembalian(peminjaman) {
            document.getElementById('modal_peminjaman_id').value = peminjaman.id;
            document.getElementById('modal_peminjam_name').value = peminjaman.user ? peminjaman.user.name : 'N/A';
            tglPlanGlobal = peminjaman.tgl_kembali_plan;

            // Render Rincian Detail Alat ke Modal
            const listContainer = document.getElementById('modal_detail_alat_list');
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

            document.getElementById('modalPengembalian').classList.remove('hidden');
            document.getElementById('modalPengembalian').classList.add('flex');
            hitungDendaOtomatis();
            toggleFormDendaKondisi();
        }

        function closeModalPengembalian() {
            document.getElementById('modalPengembalian').classList.add('hidden');
            document.getElementById('modalPengembalian').classList.remove('flex');
        }

        function toggleFormDendaKondisi() {
            const kondisi = document.getElementById('modal_kondisi_kembali').value;
            const wrapperDenda = document.getElementById('wrapper_denda_kondisi');
            const wrapperDeskripsi = document.getElementById('wrapper_deskripsi_kondisi');
            const inputDeskripsi = document.getElementById('modal_deskripsi_kondisi');

            if (kondisi !== 'Baik / Lengkap') {
                wrapperDenda.classList.remove('hidden');
                wrapperDeskripsi.classList.remove('hidden');
                inputDeskripsi.setAttribute('required', 'required');
            } else {
                wrapperDenda.classList.add('hidden');
                wrapperDeskripsi.classList.add('hidden');
                document.getElementById('modal_denda_kondisi').value = 0;
                inputDeskripsi.value = '';
                inputDeskripsi.removeAttribute('required');
            }
        }

        function hitungDendaOtomatis() {
            const inputVal = document.getElementById('modal_tgl_kembali').value;
            if (!inputVal || !tglPlanGlobal) return;

            const tglKembaliInput = new Date(inputVal);
            const tglPlan = new Date(tglPlanGlobal);

            tglKembaliInput.setHours(0, 0, 0, 0);
            tglPlan.setHours(0, 0, 0, 0);

            const diffTime = tglKembaliInput - tglPlan;
            const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays > 0) {
                const totalDendaTelat = diffDays * 1000;
                document.getElementById('text_denda_telat').innerText = 'Rp ' + totalDendaTelat.toLocaleString('id-ID') + ' (' + diffDays + ' hari keterlambatan)';
            } else {
                document.getElementById('text_denda_telat').innerText = 'Rp 0 (Tepat Waktu)';
            }
        }
    </script>
@endsection