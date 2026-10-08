@extends('layouts.app')

@section('title', 'Kelola Peminjaman - Panel Admin')
@section('header-title', 'Manajemen Transaksi Peminjaman')

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
        {{-- Header & Search Form --}}
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="text-lg font-bold text-gray-800">Daftar Transaksi Peminjaman</h3>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <form action="{{ route('admin.peminjaman.index') }}" method="GET" class="flex gap-2 items-center w-full md:w-auto">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peminjam..." class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 md:w-48">
                    <select name="status" onchange="this.form.submit()" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full md:w-32">
                        <option value="" {{ request('status') == '' ? 'selected' : '' }}>Semua Status</option>
                        <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="dikembalikan" {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                        <option value="telat" {{ request('status') == 'telat' ? 'selected' : '' }}>Telat</option>
                        <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    @if(request('status'))
                        <a href="{{ route('admin.peminjaman.index', ['search' => request('search')]) }}" class="text-xs text-gray-500 hover:text-red-600 font-semibold ml-1" title="Reset filter">✕</a>
                    @endif
                </form>
                <a href="{{ route('admin.peminjaman.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition whitespace-nowrap">+ Tambah Peminjaman</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Alat yang Dipinjam</th>
                        <th class="py-3 px-4 border-b">Tgl Pinjam / Rencana</th>
                        <th class="py-3 px-4 border-b">Status</th>
                        <th class="py-3 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($peminjamans as $peminjaman)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 border-b font-medium text-gray-900">{{ $peminjaman->user->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc pl-4 space-y-1">
                                    @foreach($peminjaman->detailPinjams as $detail)
                                        <li>{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }} <span class="font-semibold text-gray-500">({{ $detail->jumlah }} unit)</span></li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4 border-b">
                                <div class="text-xs space-y-1">
                                    <div><span class="text-gray-500">Pinjam:</span> {{ $peminjaman->tgl_pinjam }}</div>
                                    <div><span class="text-gray-500">Rencana:</span> {{ $peminjaman->tgl_kembali_plan }}</div>
                                </div>
                            </td>
                            
                            <!-- Status Dinamis Admin -->
                            <td class="py-3 px-4 border-b">
                                    @if($peminjaman->pengembalian || $peminjaman->status == 'dikembalikan')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                        Dikembalikan
                                    </span>
                                @elseif($peminjaman->is_telat)
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800" title="Melewati batas tanggal rencana kembali">
                                        Telat Dipinjam
                                    </span>
                                @elseif($peminjaman->status == 'dipinjam')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Dipinjam
                                    </span>
                                @elseif($peminjaman->status == 'diajukan')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                                        Diajukan
                                    </span>
                                @elseif($peminjaman->status == 'ditolak')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-800" title="{{ $peminjaman->alasan_penolakan }}">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ ucfirst($peminjaman->status) }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 border-b">
                                <div class="flex flex-col space-y-2">
                                    {{-- HANYA jika BELUM ada data pengembalian & statusnya dipinjam --}}
                                    @if(!$peminjaman->pengembalian && in_array($peminjaman->status, ['diproses', 'diproses', 'dipinjam']))
                                        <button onclick="openModalPengembalian({{ json_encode($peminjaman) }})" 
                                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-xs font-semibold transition text-center shadow-sm">
                                            Proses Pengembalian
                                        </button>
                                        
                                    {{-- Jika status DIAJUKAN --}}
                                    @elseif($peminjaman->status == 'diajukan')
                                        <button onclick="openModalEditRequest({{ json_encode($peminjaman) }})" 
                                            class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 rounded text-xs font-semibold transition text-center shadow-sm w-full">
                                            ✏️ Edit Request
                                        </button>

                                        <!-- Tombol Setujui -->
                                        <form action="{{ route('admin.peminjaman.updateStatus', $peminjaman->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menyetujui peminjaman ini?')">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="dipinjam">
                                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-xs font-semibold transition w-full shadow-sm">
                                                ✅ Setujui
                                            </button>
                                        </form>

                                        <!-- Tombol Buka Modal Tolak -->
                                        <button type="button" onclick="openModalTolak({{ $peminjaman->id }}, '{{ json_encode($peminjaman->user->name ?? 'Peminjam') }}')" 
                                            class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded text-xs font-semibold transition w-full shadow-sm">
                                            ❌ Tolak
                                        </button>

                                    {{-- Jika SUDAH DIKEMBALIKAN ATAU DITOLAK --}}
                                    @else
                                        <span class="text-xs text-gray-500 italic text-center py-1">Transaksi Selesai</span>
                                    @endif

                                    {{-- Tombol Hapus --}}
                                    @if(in_array($peminjaman->status, ['dikembalikan', 'ditolak']))
                                    <form action="{{ route('admin.peminjaman.destroy', $peminjaman->id) }}" method="POST" onsubmit="return confirm('Yakin menghapus data transaksi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold transition w-full shadow-sm">
                                            Hapus
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-500">Belum ada data peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 bg-gray-50">{{ $peminjamans->links() }}</div>
    </div>
    
    {{-- MODAL PROCESS PENGEMBALIAN --}}
    <div id="modalPengembalian" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200 mb-4">
                <h4 class="text-lg font-bold text-gray-800">Form Pengembalian Alat</h4>
                <button onclick="closeModalPengembalian()" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <form action="{{ route('admin.pengembalian.store') }}" method="POST">
                @csrf
                <input type="hidden" name="peminjaman_id" id="modal_peminjaman_id">

                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Nama Peminjam</label>
                    <input type="text" id="modal_peminjam_name" class="w-full text-sm bg-gray-100 border border-gray-300 rounded-lg p-2.5 text-gray-700 font-semibold" readonly>
                </div>

                <div class="mb-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Daftar Alat yang Dipinjam:</label>
                    <ul id="modal_detail_alat_list" class="list-disc pl-5 space-y-1 text-xs text-gray-800"></ul>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pengembalian</label>
                    <input type="date" name="tgl_kembali" id="modal_tgl_kembali" value="{{ date('Y-m-d') }}" 
                        onchange="hitungDendaOtomatis()" class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Barang</label>
                    <select name="kondisi_kembali" id="modal_kondisi_kembali" onchange="toggleFormDendaKondisi()" class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500" required>
                        <option value="Baik / Lengkap">Baik / Lengkap</option>
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                        <option value="Hilang">Hilang</option>
                    </select>
                </div>

                <div id="wrapper_deskripsi_kondisi" class="mb-4 hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Kerusakan / Catatan Kehilangan</label>
                    <textarea name="deskripsi_kondisi" id="modal_deskripsi_kondisi" rows="3" placeholder="Jelaskan detail kerusakan atau kronologi kehilangan barang..." class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500"></textarea>
                </div>

                <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900">
                    <div><strong>Estimasi Denda Telat:</strong> <span id="text_denda_telat">Rp 0</span></div>
                    <div class="text-gray-500 mt-0.5">* Tarik denda Rp 1.000 / hari keterlambatan</div>
                </div>

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

    {{-- MODAL EDIT REQUEST PEMINJAMAN ADMIN --}}
    <div id="modalEditRequest" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200 mb-4">
                <h4 class="text-lg font-bold text-gray-800">Edit Pengajuan Peminjaman</h4>
                <button onclick="closeModalEditRequest()" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <form id="formEditRequest" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Peminjam</label>
                    <input type="text" id="edit_peminjam_name" class="w-full text-sm bg-gray-100 border border-gray-300 rounded-lg p-2.5 text-gray-700 font-semibold" readonly>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Tgl Pinjam</label>
                        <input type="date" name="tgl_pinjam" id="edit_tgl_pinjam" class="w-full text-sm border border-gray-300 rounded-lg p-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Rencana Kembali</label>
                        <input type="date" name="tgl_kembali_plan" id="edit_tgl_kembali_plan" class="w-full text-sm border border-gray-300 rounded-lg p-2 focus:ring-blue-500" required>
                    </div>
                </div>

                {{-- Daftar Alat Saat Ini --}}
                <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <label class="block text-xs font-bold text-amber-900 uppercase mb-2">Daftar Alat yang Dipinjam:</label>
                    <div id="wrapper_detail_alat_edit" class="space-y-2"></div>
                </div>

                {{-- Form Tambah Alat Baru --}}
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <label class="block text-xs font-bold text-blue-900 uppercase mb-2">+ Tambah Item Alat Baru (Opsional)</label>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <select name="new_alat_id" class="w-full text-xs border border-gray-300 rounded p-2 focus:ring-blue-500">
                                <option value="">-- Pilih Alat Tambahan --</option>
                                @if(isset($allAlats))
                                    @foreach($allAlats as $a)
                                        <option value="{{ $a->id }}">{{ $a->nama_alat }} (Stok: {{$a->stok }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div>
                            <input type="number" name="new_jumlah" placeholder="Jml" min="1" class="w-full text-xs border border-gray-300 rounded p-2 text-center font-bold">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-200">
                    <button type="button" onclick="closeModalEditRequest()" class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-amber-600 hover:bg-amber-700 rounded-lg text-white font-semibold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TOLAK PENGAJUAN ADMIN --}}
    <div id="modalTolak" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200 mb-4">
                <h4 class="text-base font-bold text-gray-800">Tolak Pengajuan Peminjaman</h4>
                <button onclick="closeModalTolak()" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <p id="peminjamTolakText" class="text-xs text-gray-600 mb-3"></p>

            <form id="formTolakPeminjaman" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="alasan_penolakan" rows="3" required placeholder="Contoh: Stok alat sedang dalam perawatan / Jadwal bentrok..." class="w-full text-xs border border-gray-300 rounded-lg p-2.5 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-gray-200">
                    <button type="button" onclick="closeModalTolak()" class="px-4 py-2 text-xs bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 text-xs bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold">Kirim Penolakan</button>
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

        function openModalEditRequest(peminjaman) {
            document.getElementById('formEditRequest').action = "/admin/peminjaman/" + peminjaman.id + "/edit-request";
            document.getElementById('edit_peminjam_name').value = peminjaman.user ? peminjaman.user.name : 'N/A';
            document.getElementById('edit_tgl_pinjam').value = peminjaman.tgl_pinjam;
            document.getElementById('edit_tgl_kembali_plan').value = peminjaman.tgl_kembali_plan;

            const wrapper = document.getElementById('wrapper_detail_alat_edit');
            wrapper.innerHTML = '';

            if (peminjaman.detail_pinjams && peminjaman.detail_pinjams.length > 0) {
                peminjaman.detail_pinjams.forEach(detail => {
                    const namaAlat = detail.alat ? detail.alat.nama_alat : 'Alat Dihapus';
                    const div = document.createElement('div');
                    div.className = 'flex items-center justify-between text-xs bg-white p-2 rounded border border-amber-200';
                    div.innerHTML = `
                        <span class="font-semibold text-gray-800">${namaAlat}</span>
                        <div class="flex items-center gap-2">
                            <label class="text-gray-500">Jumlah:</label>
                            <input type="number" name="jumlah[${detail.id}]" value="${detail.jumlah}" min="1" class="w-16 p-1 border border-gray-300 rounded text-center font-bold">
                            <a href="/admin/peminjaman/detail/${detail.id}" onclick="return confirm('Hapus alat ini dari daftar pengajuan?')" class="text-red-600 hover:text-red-800 font-bold ml-1" title="Hapus Item">&times;</a>
                        </div>
                    `;
                    wrapper.appendChild(div);
                });
            }

            document.getElementById('modalEditRequest').classList.remove('hidden');
            document.getElementById('modalEditRequest').classList.add('flex');
        }

        function closeModalEditRequest() {
            document.getElementById('modalEditRequest').classList.add('hidden');
            document.getElementById('modalEditRequest').classList.remove('flex');
        }

        function openModalTolak(id, nama) {
            document.getElementById('formTolakPeminjaman').action = "/admin/peminjaman/" + id + "/tolak";
            document.getElementById('peminjamTolakText').innerText = "Menolak pengajuan peminjaman atas nama: " + nama;
            document.getElementById('modalTolak').classList.remove('hidden');
            document.getElementById('modalTolak').classList.add('flex');
        }

        function closeModalTolak() {
            document.getElementById('modalTolak').classList.add('hidden');
            document.getElementById('modalTolak').classList.remove('flex');
        }
    </script>
@endsection