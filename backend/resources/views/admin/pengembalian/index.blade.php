@extends('layouts.app')

@section('title', 'Rekap Pengembalian - Panel Admin')
@section('header-title', 'Manajemen Transaksi Pengembalian')

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
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            @php 
            $jmlLaporan = \App\Models\PesanPerbaikan::where('status','terkirim')->count(); 
            @endphp
            @if($jmlLaporan > 0)

        <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-lg flex justify-between items-center">
    <div>⚠️ Ada <b>{{ $jmlLaporan }} laporan perbaikan</b> dari petugas perlu ditindak</div>
    <a href="{{ route('admin.pesan.index') }}" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded text-sm">Lihat Daftar →</a>
    </div>
    @endif
            <h3 class="text-lg font-bold text-gray-800">Daftar Pengembalian Alat</h3>

                <!-- Form Search & Filter Tanggal -->
        <form action="{{ route('admin.pengembalian.index') }}" method="GET" class="flex flex-wrap items-center gap-3 mb-4">
            <!-- Input Search Nama User / Alat -->
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peminjam / alat..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Input Tanggal Mulai -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-gray-600">Dari:</label>
                <input type="date" name="tgl_mulai" value="{{ request('tgl_mulai') }}"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Input Tanggal Selesai -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-gray-600">Sampai:</label>
                <input type="date" name="tgl_selesai" value="{{ request('tgl_selesai') }}" min="{{ request('tgl_mulai') ?: date('Y-m-d') }}"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Filter Status Peminjaman -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-gray-600">Status:</label>
                <select name="status" class="text-xs border border-gray-300 rounded-lg px-2 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" {{ request('status') == '' ? 'selected' : '' }}>Semua</option>
                    <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                    <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="dikembalikan" {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                    <option value="telat" {{ request('status') == 'telat' ? 'selected' : '' }}>Telat</option>
                </select>
            </div>

            <!-- Tombol Filter & Reset -->
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-lg transition">
                    Filter
                </button>

                @if(request('search') || request('tgl_mulai') || request('tgl_selesai'))
                    <a href="{{ route('admin.pengembalian.index') }}"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 text-sm font-semibold rounded-lg transition">
                        Reset
                    </a>
                @endif
            </div>
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
                                        onclick="return confirm('Apakah Anda yakin ingin mereset data pengembalian ini?')"
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition shadow-sm">
                                    Reset
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