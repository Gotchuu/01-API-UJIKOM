@extends('layouts.app')

@section('title', 'Kelola Laporan Petugas')
@section('header-title', 'Kelola Laporan Perbaikan')

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Daftar Laporan Perbaikan dari Petugas</h3>
            <p class="text-xs text-gray-500">Benarkan = hapus pengembalian & balik jadi dipinjam | Batal = tolak laporan</p>
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
                            <div class="font-semibold">#{{ $pesan->pengembalian_id }} - {{ $pesan->pengembalian->peminjaman->user->name ?? 'N/A' }}</div>
                            <div class="text-gray-500">{{ $pesan->pengembalian->kondisi_kembali ?? '-' }} | Rp{{ number_format($pesan->pengembalian->denda ?? 0,0,',','.') }}</div>
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
                                <button onclick="openBenarkan({{ $pesan->id }})" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-xs">Benarkan</button>
                                <button onclick="openBatal({{ $pesan->id }})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">Batal</button>
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

    {{-- Modal Benarkan --}}
    <div id="modalBenarkan" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-full max-w-md">
            <h4 class="font-bold mb-2">Benarkan Laporan (Hapus Pengembalian)</h4>
            <p class="text-xs text-gray-500 mb-3">Pengembalian akan dihapus & status balik jadi Dipinjam. Wajib isi catatan.</p>
            <form id="formBenarkan" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="aksi" value="benarkan">
                <textarea name="admin_catatan" required minlength="5" placeholder="Catatan admin..." class="w-full border rounded p-2 text-sm mb-3" rows="3"></textarea>
                <div class="flex justify-end gap-2"><button type="button" onclick="closeBenarkan()" class="bg-gray-300 px-4 py-2 rounded text-sm">Batal</button><button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded text-sm">Ya, Benarkan</button></div>
            </form>
        </div>
    </div>

    {{-- Modal Batal --}}
    <div id="modalBatal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-full max-w-md">
            <h4 class="font-bold mb-2">Batalkan Laporan</h4>
            <p class="text-xs text-gray-500 mb-3">Laporan akan ditolak. Wajib isi alasan.</p>
            <form id="formBatal" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="aksi" value="batal">
                <textarea name="admin_catatan" required minlength="5" placeholder="Alasan penolakan..." class="w-full border rounded p-2 text-sm mb-3" rows="3"></textarea>
                <div class="flex justify-end gap-2"><button type="button" onclick="closeBatal()" class="bg-gray-300 px-4 py-2 rounded text-sm">Batal</button><button type="submit" class="bg-red-500 text-white px-4 py-2 rounded text-sm">Tolak</button></div>
            </form>
        </div>
    </div>

    <script>
    function openBenarkan(id){ document.getElementById('formBenarkan').action='/admin/pesan-perbaikan/'+id; document.getElementById('modalBenarkan').classList.remove('hidden'); document.getElementById('modalBenarkan').classList.add('flex'); }
    function closeBenarkan(){ document.getElementById('modalBenarkan').classList.add('hidden'); }
    function openBatal(id){ document.getElementById('formBatal').action='/admin/pesan-perbaikan/'+id; document.getElementById('modalBatal').classList.remove('hidden'); document.getElementById('modalBatal').classList.add('flex'); }
    function closeBatal(){ document.getElementById('modalBatal').classList.add('hidden'); }
    </script>
@endsection