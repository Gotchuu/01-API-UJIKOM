@extends('layouts.app')

@section('title', 'Dashboard - Peminjam')
@section('header-title', 'Dashboard Peminjam')

@section('content')
    {{-- BANNER WELCOME --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl p-6 text-white shadow-sm mb-6">
        <h2 class="text-xl font-bold mb-1">Selamat Datang kembali, {{ auth()->user()->name }}! 👋</h2>
        <p class="text-blue-100 text-xs md:text-sm">
            Pantau barang yang sedang kamu pinjam dan pastikan mengembalikan alat tepat waktu untuk menghindari denda.
        </p>
    </div>

    {{-- WARNING / DEADLINE ALERT --}}
    @foreach($peringatan as $alert)
        <div class="mb-4 p-4 rounded-lg shadow-sm text-sm flex items-center gap-3 
            {{ $alert['type'] == 'danger' ? 'bg-red-50 border border-red-200 text-red-800' : 'bg-amber-50 border border-amber-200 text-amber-800' }}">
            <span class="text-lg">{{ $alert['type'] == 'danger' ? '🚨' : '⚠️' }}</span>
            <div class="font-semibold">{{ $alert['message'] }}</div>
        </div>
    @endforeach

    {{-- STATISTIK CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold text-gray-500 uppercase">Pinjaman Aktif</div>
            <div class="text-2xl font-bold text-blue-600 mt-1">{{ $totalDipinjam }}</div>
            <div class="text-[11px] text-gray-400 mt-1">Barang di tanganmu</div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold text-gray-500 uppercase">Menunggu Persetujuan</div>
            <div class="text-2xl font-bold text-amber-600 mt-1">{{ $totalDiajukan }}</div>
            <div class="text-[11px] text-gray-400 mt-1">Sedang divalidasi petugas</div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold text-gray-500 uppercase">Riwayat Selesai</div>
            <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $totalSelesai }}</div>
            <div class="text-[11px] text-gray-400 mt-1">Transaksi dikembalikan</div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold text-gray-500 uppercase">Total Denda Pernah Dibayar</div>
            <div class="text-2xl font-bold text-red-600 mt-1">Rp {{ number_format($totalDenda, 0, ',', '.') }}</div>
            <div class="text-[11px] text-gray-400 mt-1">Catatan histori denda</div>
        </div>
    </div>

    {{-- TABEL BARANG YANG SEDANG DIPINJAM --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h3 class="text-base font-bold text-gray-800">Alat yang Sedang Dipinjam</h3>
            <a href="{{ route('peminjam.katalog') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition">
                + Pinjam Alat Lain
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider border-b">
                        <th class="py-3 px-4"># ID</th>
                        <th class="py-3 px-4">Alat & Jumlah</th>
                        <th class="py-3 px-4">Tgl Pinjam</th>
                        <th class="py-3 px-4">Batas Pengembalian</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($pinjamanAktif as $item)
                        <tr class="hover:bg-gray-50 transition border-b">
                            <td class="py-3 px-4 font-bold text-gray-600">#{{ $item->id }}</td>
                            <td class="py-3 px-4">
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    @foreach($item->detailPinjams as $detail)
                                        <li>
                                            <span class="font-semibold text-gray-900">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</span> 
                                            <span class="bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded text-[10px] font-bold">{{ $detail->jumlah }} unit</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4 text-xs">{{ $item->tgl_pinjam }}</td>
                            <td class="py-3 px-4 text-xs font-bold text-gray-900">{{ $item->tgl_kembali_plan }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Dipinjam
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500 text-xs">
                                Kamu sedang tidak meminjam alat apa pun saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection