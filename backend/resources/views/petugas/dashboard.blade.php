@extends('layouts.app')
@section('title', 'Dashboard Petugas')
@section('header-title', 'Dashboard Petugas')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-5 rounded-lg shadow border-l-4 border-amber-500">
        <div class="text-sm text-gray-500">Menunggu Persetujuan</div>
        <div class="text-3xl font-bold text-amber-600">{{ $menunggu }}</div>
        <a href="{{ route('petugas.peminjaman.index') }}" class="text-xs text-amber-600 hover:underline">Lihat →</a>
    </div>
    <div class="bg-white p-5 rounded-lg shadow border-l-4 border-blue-500">
        <div class="text-sm text-gray-500">Sedang Dipinjam</div>
        <div class="text-3xl font-bold text-blue-600">{{ $dipinjam }}</div>
        @if($telat > 0)<div class="text-xs text-red-500">{{ $telat }} telat</div>@endif
    </div>
    <div class="bg-white p-5 rounded-lg shadow border-l-4 border-emerald-500">
        <div class="text-sm text-gray-500">Kembali Hari Ini</div>
        <div class="text-3xl font-bold text-emerald-600">{{ $kembaliHariIni }}</div>
        <a href="{{ route('petugas.pengembalian.index') }}" class="text-xs text-emerald-600 hover:underline">Lihat →</a>
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white p-5 rounded-lg shadow">Total Alat: <b>{{ $totalAlat }}</b> | Stok Menipis (≤3): <b class="text-red-600">{{ $stokMenipis }}</b></div>
    <div class="bg-white p-5 rounded-lg shadow flex gap-2">
        <a href="{{ route('petugas.peminjaman.index') }}" class="bg-amber-500 text-white px-4 py-2 rounded text-sm">Persetujuan</a>
        <a href="{{ route('petugas.pengembalian.index') }}" class="bg-emerald-600 text-white px-4 py-2 rounded text-sm">Pengembalian</a>
    </div>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
    <div class="p-4 font-bold border-b bg-gray-50 flex justify-between items-center">
        <span>5 Peminjaman Terbaru</span>
        <a href="{{ route('petugas.peminjaman.index') }}" class="text-xs text-blue-600 hover:underline">Lihat Semua →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider">
                    <th class="py-3 px-4 border-b">Peminjam</th>
                    <th class="py-3 px-4 border-b">Alat</th>
                    <th class="py-3 px-4 border-b">Tgl Pinjam</th>
                    <th class="py-3 px-4 border-b">Status</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700">
                @forelse($recent as $r)
                <tr class="hover:bg-gray-50 transition">
                    <td class="py-3 px-4 border-b font-medium text-gray-900">{{ $r->user->name ?? 'N/A' }}</td>
                    <td class="py-3 px-4 border-b">
                        <ul class="list-disc pl-4 space-y-1 text-xs">
                            @foreach($r->detailPinjams as $d)
                                <li>{{ $d->alat->nama_alat ?? 'Alat Dihapus' }} <span class="font-semibold text-gray-500">({{ $d->jumlah }} unit)</span></li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="py-3 px-4 border-b text-xs">{{ \Carbon\Carbon::parse($r->tgl_pinjam)->format('d M Y') }}<br><span class="text-gray-400">Rencana: {{ \Carbon\Carbon::parse($r->tgl_kembali_plan)->format('d M Y') }}</span></td>
                    <td class="py-3 px-4 border-b">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                            @if($r->status == 'diajukan') bg-amber-100 text-amber-800
                            @elseif($r->status == 'dipinjam') bg-blue-100 text-blue-800
                            @elseif($r->status == 'dikembalikan') bg-emerald-100 text-emerald-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($r->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-6 text-center text-gray-500">Belum ada peminjaman</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection