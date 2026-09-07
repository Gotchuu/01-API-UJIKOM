@extends('layouts.app')

@section('title', 'Pemantauan Pengembalian')
@section('header-title', 'Daftar Pengembalian Alat')

@section('content')
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Riwayat & Pemantauan Pengembalian</h3>
            <form action="{{ route('petugas.pengembalian.index') }}" method="GET" class="flex w-full md:w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peminjam..."
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('petugas.pengembalian.index') }}"
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
                        <th class="py-3 px-4 border-b">Tanggal Kembali</th>
                        <th class="py-3 px-4 border-b">Kondisi Barang</th>
                        <th class="py-3 px-4 border-b">Denda</th>
                        <th class="py-3 px-4 border-b">Petugas Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($pengembalians as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $item->peminjaman->user->name ?? 'User Dihapus' }}
                            </td>
                            <td class="py-3 px-4 border-b">{{ $item->tgl_kembali }}</td>
                            <td class="py-3 px-4 border-b">
                                @php
                                    $k = strtolower($item->kondisi_kembali);
                                    $warnaKembali = str_contains($k, 'hilang') ? 'bg-red-100 text-red-800' : (str_contains($k, 'berat') ? 'bg-orange-100 text-orange-800' : (str_contains($k, 'ringan') ? 'bg-amber-100 text-amber-800' : (str_contains($k, 'baik') || str_contains($k, 'lengkap') || str_contains($k, 'baru') ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800')));
                                @endphp
                                <span class="px-2.5 py-1 rounded text-xs font-semibold {{ $warnaKembali }}">
                                    {{ $item->kondisi_kembali }}
                                </span>
                            </td>
                            <td class="py-3 px-4 border-b">
                                Rp {{ number_format($item->denda, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 border-b">
                                {{ $item->petugas->name ?? 'Sistem' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500">Belum ada data pengembalian alat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection