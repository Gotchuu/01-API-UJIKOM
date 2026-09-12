@extends('layouts.app')

@section('title', 'Kelola Laporan Petugas')
@section('header-title', 'Kelola Laporan Perbaikan')

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="flex gap-2 mb-4">
        <a href="{{ route('admin.pesan.index') }}" class="px-4 py-2 rounded-full text-sm {{ !$status ? 'bg-gray-900 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">Semua</a>
        <a href="{{ route('admin.pesan.index', ['status'=>'terkirim']) }}" class="px-4 py-2 rounded-full text-sm {{ $status=='terkirim' ? 'bg-amber-500 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">Terkirim</a>
        <a href="{{ route('admin.pesan.index', ['status'=>'dibaca']) }}" class="px-4 py-2 rounded-full text-sm {{ $status=='dibaca' ? 'bg-gray-700 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">Dibaca</a>
        <a href="{{ route('admin.pesan.index', ['status'=>'selesai']) }}" class="px-4 py-2 rounded-full text-sm {{ $status=='selesai' ? 'bg-emerald-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">Selesai</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Daftar Laporan Perbaikan dari Petugas</h3>
            <p class="text-xs text-gray-500">Perbaiki = hapus pengembalian & balik jadi dipinjam | Batal = tolak laporan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Pengembalian</th>
                        <th class="py-3 px-4 border-b">Petugas</th>
                        <th class="py-3 px-4 border-b">Jenis</th>
                        <th class="py-3 px-4 border-b">Pesan</th>
                        <th class="py-3 px-4 border-b">Status</th>
                        <th class="py-3 px-4 border-b text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    @forelse($pesans as $pesan)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 border-b text-xs">
                            @if($pesan->pengembalian)
                                <div class="font-semibold">#{{ $pesan->pengembalian_id }} - {{ $pesan->pengembalian->peminjaman->user->name ?? 'User Dihapus' }}</div>
                                <div class="text-gray-500">{{ $pesan->pengembalian->kondisi_kembali ?? '-' }} | Rp{{ number_format($pesan->pengembalian->denda ?? 0,0,',','.') }}</div>
                            @else
                                <div class="font-semibold text-emerald-700">Laporan #{{ $pesan->id }} - Pengembalian Dihapus</div>
                                <div class="text-emerald-600">Sudah diperbaiki • Status: {{ ucfirst($pesan->status) }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 border-b">{{ $pesan->petugas->name ?? 'N/A' }}</td>
                        <td class="py-3 px-4 border-b"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">{{ ucfirst($pesan->jenis) }}</span></td>
                        <td class="py-3 px-4 border-b max-w-xs truncate" title="{{ $pesan->pesan }}">{{ Str::limit($pesan->pesan, 60) }}</td>
                        <td class="py-3 px-4 border-b">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                @if($pesan->status=='terkirim') bg-amber-100 text-amber-800
                                @elseif($pesan->status=='dibaca') bg-gray-100 text-gray-800
                                @else bg-emerald-100 text-emerald-800 @endif">
                                {{ ucfirst($pesan->status) }}
                            </span>
                            @if($pesan->admin_catatan)<div class="text-xs text-gray-500 mt-1">Catatan: {{ Str::limit($pesan->admin_catatan, 40) }}</div>@endif
                        </td>
                        <td class="py-3 px-4 border-b text-center">
                            @if($pesan->status=='terkirim')
                            <div class="flex justify-center gap-1">
                                <button data-id="{{ $pesan->id }}" data-pesan="{{ $pesan->pesan }}" data-jenis="{{ $pesan->jenis }}" data-detail="{{ $pesan->pengembalian->peminjaman->detailPinjams->map(fn($d) => ($d->alat->nama_alat ?? 'Alat Dihapus') . ' (' . $d->jumlah . ' unit)')->join(', ') }}" onclick="openPerbaiki(this)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-xs">Perbaiki</button>
                                <button data-id="{{ $pesan->id }}" data-pesan="{{ $pesan->pesan }}" data-jenis="{{ $pesan->jenis }}" data-detail="{{ $pesan->pengembalian->peminjaman->detailPinjams->map(fn($d) => ($d->alat->nama_alat ?? 'Alat Dihapus') . ' (' . $d->jumlah . ' unit)')->join(', ') }}" onclick="openBatal(this)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">Batal</button>
                            </div>
                            @else
                            <span class="text-xs text-gray-400">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-6 text-center text-gray-500">Belum ada laporan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t bg-gray-50">{{ $pesans->links() }}</div>
    </div>

    {{-- Modal Perbaiki --}}
    <div id="modalPerbaiki" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-full max-w-md">
            <h4 class="font-bold mb-2">Perbaiki Laporan (Hapus Pengembalian)</h4>
            <div class="bg-blue-50 border border-blue-200 p-3 rounded text-xs mb-3">
                <div class="font-semibold text-blue-800">Detail Alat:</div>
                <div id="detailAlatPerbaiki" class="text-gray-700 mt-1"></div>
            </div>
            <div class="bg-amber-50 border border-amber-200 p-3 rounded text-xs mb-3">
                <div class="font-semibold text-amber-800">Pesan Petugas:</div>
                <div id="pesanPetugasPerbaiki" class="text-gray-800 mt-1"></div>
                <div class="text-gray-500 mt-1">Jenis: <span id="jenisPerbaiki" class="font-semibold"></span></div>
            </div>
            <p class="text-xs text-gray-500 mb-2">Pengembalian akan dihapus & status balik jadi Dipinjam. Wajib isi catatan admin:</p>
            <form id="formPerbaiki" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="aksi" value="perbaiki">
                <textarea name="admin_catatan" required minlength="5" placeholder="Catatan admin..." class="w-full border rounded p-2 text-sm mb-3" rows="3"></textarea>
                <div class="flex justify-end gap-2"><button type="button" onclick="closePerbaiki()" class="bg-gray-300 px-4 py-2 rounded text-sm">Batal</button><button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded text-sm">Ya, Perbaiki</button></div>
            </form>
        </div>
    </div>

    {{-- Modal Batal --}}
    <div id="modalBatal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-full max-w-md">
            <h4 class="font-bold mb-2">Batalkan Laporan</h4>
            <div class="bg-blue-50 border border-blue-200 p-3 rounded text-xs mb-3">
                <div class="font-semibold text-blue-800">Detail Alat:</div>
                <div id="detailAlatBatal" class="text-gray-700 mt-1"></div>
            </div>
            <div class="bg-gray-50 border p-3 rounded text-xs mb-3">
                <div class="font-semibold">Pesan Petugas:</div>
                <div id="pesanPetugasBatal" class="text-gray-800 mt-1"></div>
                <div class="text-gray-500 mt-1">Jenis: <span id="jenisBatal" class="font-semibold"></span></div>
            </div>
            <p class="text-xs text-gray-500 mb-2">Laporan akan ditolak. Wajib isi alasan penolakan:</p>
            <form id="formBatal" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="aksi" value="batal">
                <textarea name="admin_catatan" required minlength="5" placeholder="Alasan penolakan..." class="w-full border rounded p-2 text-sm mb-3" rows="3"></textarea>
                <div class="flex justify-end gap-2"><button type="button" onclick="closeBatal()" class="bg-gray-300 px-4 py-2 rounded text-sm">Batal</button><button type="submit" class="bg-red-500 text-white px-4 py-2 rounded text-sm">Tolak</button></div>
            </form>
        </div>
    </div>

    <script>
    function openPerbaiki(btn){
         const id=btn.dataset.id; const pesan=btn.dataset.pesan; const jenis=btn.dataset.jenis; const detail=btn.dataset.detail; document.getElementById('formPerbaiki').action='{{ url("/admin/pesan-perbaikan") }}/'+id; document.getElementById('pesanPetugasPerbaiki').innerText=pesan; document.getElementById('jenisPerbaiki').innerText=jenis; document.getElementById('detailAlatPerbaiki').innerText=detail; document.getElementById('modalPerbaiki').classList.remove('hidden'); document.getElementById('modalPerbaiki').classList.add('flex'); 
        }
    function closePerbaiki(){ 
        document.getElementById('modalPerbaiki').classList.add('hidden'); document.getElementById('modalPerbaiki').classList.remove('flex');
    }
    
    function openBatal(btn){ const id=btn.dataset.id; const pesan=btn.dataset.pesan; const jenis=btn.dataset.jenis; const detail=btn.dataset.detail; document.getElementById('formBatal').action='/admin/pesan-perbaikan/'+id; document.getElementById('pesanPetugasBatal').innerText=pesan; document.getElementById('jenisBatal').innerText=jenis; document.getElementById('detailAlatBatal').innerText=detail; document.getElementById('modalBatal').classList.remove('hidden'); document.getElementById('modalBatal').classList.add('flex'); }
    function closeBatal(){ document.getElementById('modalBatal').classList.add('hidden'); document.getElementById('modalBatal').classList.remove('flex'); }
    </script>
@endsection
