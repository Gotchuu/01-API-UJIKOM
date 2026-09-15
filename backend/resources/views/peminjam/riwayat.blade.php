@extends('layouts.app')

@section('title', 'Riwayat Peminjaman - Peminjam')
@section('header-title', 'Riwayat Peminjaman Saya')

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

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        {{-- Header & Filter Status --}}
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="text-lg font-bold text-gray-800">Daftar Transaksi Saya</h3>
            
            <div class="flex items-center gap-3 w-full md:w-auto">
                <!-- Form Filter Status -->
                <form action="{{ route('peminjam.riwayat') }}" method="GET" class="w-full md:w-48">
                    <select name="status" onchange="this.form.submit()" class="w-full text-sm border border-gray-300 rounded-lg p-2 focus:ring-blue-500">
                        <option value="">-- Semua Status --</option>
                        <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="dikembalikan" {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                        <option value="telat" {{ request('status') == 'telat' ? 'selected' : '' }}>Telat</option>
                    </select>
                </form>

                <a href="{{ route('peminjam.katalog') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg transition whitespace-nowrap">
                    + Pinjam Alat Baru
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Detail Tanggal</th>
                        <th class="py-3 px-4 border-b">Alat yang Dipinjam</th>
                        <th class="py-3 px-4 border-b text-center">Status</th>
                        <th class="py-3 px-4 border-b">Info Pengembalian & Denda</th>
                        <th class="py-3 px-4 border-b text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($peminjamans as $item)
                        <tr class="hover:bg-gray-50 transition align-top">
                            <!-- Detail Tanggal -->
                            <td class="py-3 px-4 border-b">
                                <div class="text-xs space-y-1">
                                    <div><span class="text-gray-500">Pinjam:</span> <span class="font-semibold">{{ $item->tgl_pinjam }}</span></div>
                                    <div><span class="text-gray-500">Rencana:</span> <span class="font-semibold">{{ $item->tgl_kembali_plan }}</span></div>
                                    @if($item->pengembalian)
                                        <div class="text-emerald-700 font-semibold"><span class="text-gray-500">Dikembalikan:</span> {{ $item->pengembalian->tgl_kembali }}</div>
                                    @endif
                                </div>
                            </td>

                            <!-- List Alat -->
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    @foreach($item->detailPinjams as $detail)
                                        <li>
                                            <span class="font-semibold text-gray-900">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</span> 
                                            <span class="bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded text-[10px] font-bold">{{ $detail->jumlah }} unit</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>

                            <!-- Status Transaksi -->
                            <td class="py-3 px-4 border-b text-center">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                    @if($item->status == 'diajukan') bg-amber-100 text-amber-800
                                    @elseif($item->status == 'dipinjam') bg-blue-100 text-blue-800
                                    @elseif($item->status == 'dikembalikan') bg-emerald-100 text-emerald-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>

                            <!-- Info Denda & Kondisi -->
                            <td class="py-3 px-4 border-b">
                                @if($item->pengembalian)
                                    <div class="text-xs space-y-1">
                                        <div>Kondisi: <span class="font-bold text-gray-800">{{ $item->pengembalian->kondisi_kembali }}</span></div>
                                        @if($item->pengembalian->denda > 0)
                                            <div class="text-red-600 font-bold">Denda: Rp {{ number_format($item->pengembalian->denda, 0, ',', '.') }}</div>
                                        @else
                                            <div class="text-emerald-600 font-medium">Bebas Denda</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Belum dikembalikan</span>
                                @endif
                            </td>

                            <!-- Tombol Aksi -->
                            <td class="py-3 px-4 border-b text-center">
                                @if($item->status == 'diajukan')
                                    <form action="{{ route('peminjam.peminjaman.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pengajuan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition shadow-sm">
                                            Batalkan
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500">Belum ada riwayat transaksi peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection