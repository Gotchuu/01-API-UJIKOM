@extends('layouts.app')

@section('title', 'Rekap Pengembalian - Panel Admin')
@section('header-title', 'Manajemen Transaksi Pengembalian')

@section('content')
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="text-lg font-bold text-gray-800">Daftar Pengembalian Alat</h3>

            <form action="{{ route('admin.pengembalian.index') }}" method="GET" class="flex w-full md:w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari peminjam / kondisi.."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">Cari</button>
                @if(request('search'))
                    <a href="{{ route('admin.pengembalian.index') }}" 
                        class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 text-sm rounded-lg flex items-center transition" title="Reset Pencarian">
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
                        <th class="py-3 px-4 border-b">Alat Dikembalikan</th>
                        <th class="py-3 px-4 border-b">Tgl Kembali</th>
                        <th class="py-3 px-4 border-b">Kondisi Barang</th>
                        <th class="py-3 px-4 border-b">Denda</th>
                        <th class="py-3 px-4 border-b">Petugas Penerima</th>
                        <th class="py-3 px-4 border-b text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($pengembalians as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $item->peminjaman->user->name ?? 'N/A' }}
                            </td>
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc pl-4 space-y-1 text-xs">
                                    @foreach($item->peminjaman->detailPinjams as $detail)
                                        <li>{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }} ({{ $detail->jumlah }} unit)</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4 border-b text-xs">
                                {{ $item->tgl_kembali }}
                            </td>
                            <td class="py-3 px-4 border-b">
                                @php
                                    $k = strtolower($item->kondisi_kembali);
                                    $warnaKembali = str_contains($k, 'hilang') ? 'bg-red-100 text-red-800' : (str_contains($k, 'berat') ? 'bg-orange-100 text-orange-800' : (str_contains($k, 'ringan') ? 'bg-amber-100 text-amber-800' : (str_contains($k, 'baik') || str_contains($k, 'lengkap') || str_contains($k, 'baru') ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800')));
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $warnaKembali }}">
                                    {{ $item->kondisi_kembali }}
                                </span>
                            </td>
                            <td class="py-3 px-4 border-b font-semibold">
                                @if($item->denda > 0)
                                    <span class="text-red-600">Rp {{ number_format($item->denda, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-emerald-600">Rp 0</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 border-b text-xs text-gray-600">
                                {{ $item->petugas->name ?? 'System' }}
                            </td>
                            <td class="py-3 px-4 border-b text-center">
                            <form action="{{ route('admin.pengembalian.destroy', $item->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus data pengembalian ini?')"
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition shadow-sm">
                                    Hapus
                                </button>
                            </form>
                        </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-gray-500">Belum ada riwayat pengembalian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 bg-gray-50">
            {{ $pengembalians->links() }}
        </div>
    </div>
@endsection