@extends('layouts.app')
@section('title', 'Laporan Peminjaman')
@section('header-title', 'Laporan Peminjaman & Pengembalian Alat')

@section('content')
{{-- RINGKASAN STATISTIK SINGKAT --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <div class="text-xs text-gray-500 uppercase font-semibold">Total Transaksi</div>
        <div class="text-xl font-bold text-gray-800 mt-1">{{ $peminjamans->total() }}</div>
    </div>
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <div class="text-xs text-gray-500 uppercase font-semibold">Aktif Dipinjam</div>
        <div class="text-xl font-bold text-blue-600 mt-1">
            {{ $peminjamans->where('status', 'dipinjam')->count() }}
        </div>
    </div>
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <div class="text-xs text-gray-500 uppercase font-semibold">Dikembalikan</div>
        <div class="text-xl font-bold text-emerald-600 mt-1">
            {{ $peminjamans->where('status', 'dikembalikan')->count() }}
        </div>
    </div>
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <div class="text-xs text-gray-500 uppercase font-semibold">Total Denda (Halaman Ini)</div>
        <div class="text-xl font-bold text-red-600 mt-1">
            Rp {{ number_format($peminjamans->sum(fn($p) => $p->pengembalian->denda ?? 0), 0, ',', '.') }}
        </div>
    </div>
</div>

{{-- FORM FILTER DATA --}}
<div class="bg-white border rounded-xl p-5 mb-4 shadow-sm">
    <form action="{{ route('petugas.laporan.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div>
            <label class="text-xs font-semibold text-gray-600">Dari Tanggal</label>
            <input type="date" name="dari" value="{{ $dari }}" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-600">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ $sampai }}" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-600">Status Transaksi</label>
            <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="diajukan" @selected($status=='diajukan')>Diajukan</option>
                <option value="dipinjam" @selected($status=='dipinjam')>Dipinjam</option>
                <option value="dikembalikan" @selected($status=='dikembalikan')>Dikembalikan</option>
                <option value="telat" @selected($status=='telat')>Telat</option>
            </select>
        </div>
        <div class="flex items-end gap-2 md:col-span-2">
            <button type="submit" class="flex-1 bg-gray-900 hover:bg-black text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition">
                Tampilkan
            </button>
            <a href="{{ route('petugas.laporan.cetak', request()->all()) }}" target="_blank" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold text-center transition">
                Cetak PDF →
            </a>
            <a href="{{ route('petugas.laporan.index') }}" class="bg-gray-100 hover:bg-gray-200 border px-4 py-2.5 rounded-lg text-sm text-gray-700 transition">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- TABEL LAPORAN DETAILED --}}
<div class="bg-white border rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-gray-100 text-gray-700 text-xs uppercase tracking-wider border-b">
                    <th class="px-4 py-3 text-center"># ID</th>
                    <th class="px-4 py-3">Peminjam</th>
                    <th class="px-4 py-3">Rincian Alat & Unit</th>
                    <th class="px-4 py-3">Riwayat Tanggal</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3">Kondisi & Denda</th>
                    <th class="px-4 py-3">Petugas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($peminjamans as $p)
                    <tr class="hover:bg-gray-50 transition align-top">
                        <td class="px-4 py-3 text-center font-bold text-gray-600">#{{ $p->id }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $p->user->name ?? 'User Dihapus' }}</td>
                        <td class="px-4 py-3">
                            <ul class="list-disc list-inside space-y-0.5 text-xs">
                                @foreach($p->detailPinjams as $d)
                                    <li>{{ $d->alat->nama_alat ?? 'Alat Dihapus' }} <span class="font-bold text-gray-600">({{ $d->jumlah }} unit)</span></li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="px-4 py-3 text-xs space-y-1">
                            <div><span class="text-gray-500">Pinjam:</span> {{ $p->tgl_pinjam }}</div>
                            <div><span class="text-gray-500">Rencana:</span> {{ $p->tgl_kembali_plan }}</div>
                            @if($p->pengembalian)
                                <div class="text-emerald-700 font-semibold"><span class="text-gray-500">Kembali:</span> {{ $p->pengembalian->tgl_kembali }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                @if($p->status == 'diajukan') bg-amber-100 text-amber-800
                                @elseif($p->status == 'dipinjam') bg-blue-100 text-blue-800
                                @elseif($p->status == 'dikembalikan') bg-emerald-100 text-emerald-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            @if($p->pengembalian)
                                <div class="font-medium text-gray-800">Kondisi: {{ $p->pengembalian->kondisi_kembali }}</div>
                                @if($p->pengembalian->denda > 0)
                                    <div class="text-red-600 font-bold">Denda: Rp {{ number_format($p->pengembalian->denda, 0, ',', '.') }}</div>
                                @else
                                    <div class="text-emerald-600">Bebas Denda</div>
                                @endif
                            @else
                                <span class="text-gray-400 italic">Belum kembali</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            {{ $p->pengembalian->petugas->name ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-gray-500">Tidak ada data transaksi peminjaman ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 bg-gray-50 border-t">{{ $peminjamans->links() }}</div>
</div>
@endsection