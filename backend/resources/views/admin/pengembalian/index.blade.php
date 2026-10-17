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
    
    {{-- Banner Peringatan Pesan Perbaikan dari Petugas --}}
    @php 
        $jmlLaporan = \App\Models\PesanPerbaikan::where('status','terkirim')->count(); 
    @endphp
    @if($jmlLaporan > 0)
        <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-lg flex justify-between items-center text-sm shadow-sm">
            <div>⚠️ Ada <b>{{ $jmlLaporan }} laporan perbaikan</b> dari petugas perlu ditindaklanjuti.</div>
            <a href="{{ route('admin.pesan.index') }}" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-1.5 rounded-lg text-xs font-semibold transition">Lihat Daftar &rarr;</a>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h3 class="text-lg font-bold text-gray-800 whitespace-nowrap">Daftar Pengembalian Alat</h3>

            <!-- Form Search & Filter Tanggal / Kondisi -->
            <form action="{{ route('admin.pengembalian.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                <!-- Input Search Nama User / Alat -->
                <div class="w-full md:w-48">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari peminjam / alat..."
                        class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Input Tanggal Dari -->
                <div class="flex items-center gap-1">
                    <label class="text-xs font-medium text-gray-600">Dari:</label>
                    <input type="date" id="admin_tgl_mulai" name="tgl_mulai" value="{{ request('tgl_mulai') }}" onchange="updateMinReturnDateAdmin()"
                        class="px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Input Tanggal Sampai (Tanpa default min hari ini agar tanggal lalu bisa dipilih) -->
                <div class="flex items-center gap-1">
                    <label class="text-xs font-medium text-gray-600">Sampai:</label>
                    <input type="date" id="admin_tgl_selesai" name="tgl_selesai" value="{{ request('tgl_selesai') }}" min="{{ request('tgl_mulai') }}"
                        class="px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Filter Kondisi Barang -->
                <div class="flex items-center gap-1">
                    <label class="text-xs font-medium text-gray-600">Kondisi:</label>
                    <select name="kondisi" class="text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" {{ request('kondisi') == '' ? 'selected' : '' }}>Semua Kondisi</option>
                        <option value="baik" {{ request('kondisi') == 'baik' ? 'selected' : '' }}>Baik / Lengkap</option>
                        <option value="ringan" {{ request('kondisi') == 'ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="berat" {{ request('kondisi') == 'berat' ? 'selected' : '' }}>Rusak Berat</option>
                        <option value="hilang" {{ request('kondisi') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                    </select>
                </div>

                <!-- Tombol Filter & Reset -->
                <div class="flex items-center gap-1.5">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-3.5 py-1.5 text-xs font-semibold rounded-lg transition">
                        Filter
                    </button>

                    @if(request('search') || request('tgl_mulai') || request('tgl_selesai') || request('kondisi'))
                        <a href="{{ route('admin.pengembalian.index') }}"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1.5 text-xs font-semibold rounded-lg transition">
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
                                {{ $item->peminjaman->user->name ?? 'User Dihapus' }}
                            </td>
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc pl-4 space-y-1 text-xs">
                                    @foreach($item->peminjaman->detailPinjams as $detail)
                                        <li>{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }} <span class="font-bold text-gray-600">({{ $detail->jumlah }} unit)</span></li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4 border-b text-xs">
                                {{ $item->tgl_kembali }}
                            </td>
                            <td class="py-3 px-4 border-b">
                                @php
                                    $k = strtolower($item->kondisi_kembali);
                                    $warnaKembali = str_contains($k, 'hilang') ? 'bg-red-100 text-red-800 border border-red-200' : (str_contains($k, 'berat') ? 'bg-orange-100 text-orange-800 border border-orange-200' : (str_contains($k, 'ringan') ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200'));
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
                            <td colspan="7" class="py-6 text-center text-gray-500 text-xs">Tidak ada riwayat pengembalian yang sesuai dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 bg-gray-50">
            {{ $pengembalians->links() }}
        </div>
    </div>

    <script>
        function updateMinReturnDateAdmin() {
            const tglMulaiInput = document.getElementById('admin_tgl_mulai');
            const tglSelesaiInput = document.getElementById('admin_tgl_selesai');
            
            if (tglMulaiInput && tglSelesaiInput) {
                const valMulai = tglMulaiInput.value;
                if (valMulai) {
                    tglSelesaiInput.min = valMulai;
                    if (tglSelesaiInput.value && tglSelesaiInput.value < valMulai) {
                        tglSelesaiInput.value = valMulai;
                    }
                } else {
                    tglSelesaiInput.removeAttribute('min');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', updateMinReturnDateAdmin);
    </script>
@endsection