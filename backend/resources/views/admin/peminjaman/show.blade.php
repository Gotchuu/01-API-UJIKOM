====================== DASHBOARD ================

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

=============================== END DASHBOARD =================================================



=============================== Katalog ==========================================================
@extends('layouts.app')

@section('title', 'Katalog Alat - Peminjam')
@section('header-title', 'Katalog Alat Tersedia')

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

    {{-- BOX FILTER & PENCARIAN ALAT --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form action="{{ route('peminjam.katalog') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-center justify-between">
            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto flex-1">
                <!-- Input Search -->
                <div class="w-full md:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama alat..."
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <!-- Dropdown Filter Kategori -->
                <div class="w-full md:w-56">
                    <select name="kategori_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tombol Cari & Reset -->
                <div class="flex gap-2">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                        Cari
                    </button>
                    @if(request('search') || request('kategori_id'))
                        <a href="{{ route('peminjam.katalog') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold px-3 py-2 rounded-lg transition">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- FORM PENGAJUAN PEMINJAMAN --}}
    <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST" onsubmit="return validateFormForm()">
        @csrf
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="w-full md:w-72 space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tanggal Pinjam <span class="text-red-500">*</span></label>
                        <input type="date" name="tgl_pinjam" id="tglPinjam" min="{{ date('Y-m-d') }}" value="{{ old('tgl_pinjam', date('Y-m-d')) }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Rencana Tanggal Kembali <span class="text-red-500">*</span></label>
                        <!-- PERBAIKAN: Atribut min diubah menjadi minimal sama dengan tgl_pinjam hari ini -->
                        <input type="date" name="tgl_kembali_plan" id="tglKembali" min="{{ old('tgl_pinjam', date('Y-m-d')) }}" value="{{ old('tgl_kembali_plan', date('Y-m-d')) }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                    </div>
                </div>
                <div class="text-xs text-gray-500">
                    * Centang minimal satu alat yang ingin dipinjam pada tabel di bawah.
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                            <th class="py-3 px-4 border-b text-center w-12">Pilih</th>
                            <th class="py-3 px-4 border-b">Detail Alat</th>
                            <th class="py-3 px-4 border-b">Kategori</th>
                            <th class="py-3 px-4 border-b text-center">Stok Tersedia</th>
                            <th class="py-3 px-4 border-b text-center w-32">Jumlah Pinjam</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm">
                        @forelse($alats as $alat)
                            <tr class="hover:bg-gray-50 transition align-middle">
                                <td class="py-3 px-4 border-b text-center">
                                    <input type="checkbox" name="alat_id[]" value="{{ $alat->id }}" class="alat-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                </td>
                                <td class="py-3 px-4 border-b font-medium text-gray-900">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $placeholder = 'https://placehold.co/120x120/f3f4f6/9ca3af?text=' . urlencode(Str::substr(Str::upper($alat->nama_alat), 0, 2));
                                            $gambarAda = $alat->gambar && file_exists(public_path($alat->gambar));
                                        @endphp
                                        @if($gambarAda)
                                            <img src="{{ asset($alat->gambar) }}" alt="{{ $alat->nama_alat }}" class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                                        @else
                                            <img src="{{ $placeholder }}" alt="{{ $alat->nama_alat }}" class="w-12 h-12 rounded-lg border border-gray-200 bg-gray-50">
                                        @endif
                                        <div>
                                            <div class="font-bold text-gray-800">{{ $alat->nama_alat }}</div>
                                            <div class="text-xs text-gray-500">Kondisi: {{ $alat->status_kondisi ?? 'Baik' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 border-b text-gray-600">
                                    <span class="bg-gray-100 text-gray-700 text-xs px-2.5 py-1 rounded font-medium border border-gray-200">
                                        {{ $alat->kategori->nama_kategori ?? 'Tanpa Kategori' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 border-b text-center">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $alat->stok <= 3 ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $alat->stok }} unit
                                    </span>
                                </td>
                                <td class="py-3 px-4 border-b text-center">
                                    <input type="number" 
                                    name="jumlah[{{ $alat->id }}]" 
                                    value="1" 
                                    min="1" 
                                    max="{{ min($alat->stok, 2) }}" {{-- Batas max adalah stok yang ada ATAU maksimal 2 unit --}}
                                    class="w-full text-center text-sm border border-gray-300 rounded-lg p-1.5 focus:ring-blue-500"
                                    oninput="
                                        let maxVal = {{ min($alat->stok, 2) }};
                                        if(this.value > maxVal) this.value = maxVal;
                                        if(this.value < 1) this.value = 1;
                                    ">
                                <span class="text-[10px] text-gray-500 block mt-0.5">*Maks 2 unit</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-500">Tidak ada alat yang cocok dengan pencarian / filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-blue-50 border-t border-blue-200 flex items-start gap-3">
                <span class="text-blue-600 text-lg">ℹ</span>
                <div class="text-xs text-blue-800">
                    <p class="font-bold mb-0.5">Petunjuk Peminjaman:</p>
                    <p>Centang kotak <b>Pilih</b> pada item alat yang ingin Anda pinjam, lalu tentukan <b>Rencana Tanggal Kembali</b> sebelum menekan tombol pengajuan.</p>
                </div>
            </div>

            <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-between items-center">
                <span class="text-xs text-gray-500">Status Awal Transaksi: <strong class="text-amber-600 uppercase">Diajukan</strong></span>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition shadow-sm">
                    Ajukan Peminjaman &rarr;
                </button>
            </div>
        </div>
    </form>

    <script>
    function validateFormForm() {
        const checkboxes = document.querySelectorAll('.alat-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Silakan pilih minimal satu alat yang ingin dipinjam dengan mencentang kotak centang!');
            return false;
        }
        return true;
    }

    function updateMinTanggalKembali() {
        const pinjamInput = document.getElementById('tglPinjam');
        const kembaliInput = document.getElementById('tglKembali');
        
        if (pinjamInput && kembaliInput && pinjamInput.value) {
            // Set batas minimal tanggal kembali = tanggal pinjam yang dipilih (same day return)
            kembaliInput.min = pinjamInput.value;
            
            // Jika tanggal kembali yang sudah dipilih ternyata lebih awal dari tanggal pinjam baru, samakan nilainya
            if (kembaliInput.value && kembaliInput.value < pinjamInput.value) {
                kembaliInput.value = pinjamInput.value;
            }
        }
    }

    // Event saat tanggal pinjam diubah manual oleh user
    document.getElementById('tglPinjam').addEventListener('change', updateMinTanggalKembali);

    // Jalankan sekali saat DOM selesai dimuat agar batas tanggal langsung tersetting sempurna
    document.addEventListener('DOMContentLoaded', updateMinTanggalKembali);
</script>
@endsection


=========================================== END KATALOG ===============================================================



========================================== RIWAYAT BLADE =====================================================================


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
        {{-- Header & Filter Form --}}
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
            <h3 class="text-lg font-bold text-gray-800 whitespace-nowrap">Daftar Transaksi Saya</h3>
            
            <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3 w-full xl:w-auto">
                <form action="{{ route('peminjam.riwayat') }}" method="GET" class="flex flex-wrap md:flex-nowrap items-center gap-2 w-full md:w-auto">
                    <!-- Search Input Nama Alat -->
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama alat..." class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-full md:w-36">

                    <!-- Filter Status -->
                    <select name="status" onchange="this.form.submit()" class="text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full md:w-36">
                        <option value="">-- Semua Status --</option>
                        <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Menunggu Cek</option>
                        <option value="dikembalikan" {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                        <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>

                    <!-- Filter Tanggal Dari -->
                    <div class="flex items-center gap-1">
                        <span class="text-xs text-gray-500 font-medium">Dari:</span>
                        <input type="date" id="peminjam_tgl_mulai" name="tgl_mulai" value="{{ request('tgl_mulai') }}" onchange="updateMinReturnDatePeminjam()" class="px-2 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Filter Tanggal Sampai -->
                    <div class="flex items-center gap-1">
                        <span class="text-xs text-gray-500 font-medium">Sampai:</span>
                        <input type="date" id="peminjam_tgl_selesai" name="tgl_selesai" value="{{ request('tgl_selesai') }}" class="px-2 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Tombol Submit Filter -->
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-xs px-3 py-1.5 rounded-lg font-semibold transition">
                        Filter
                    </button>

                    <!-- Tombol Reset -->
                    @if(request('search') || request('status') || request('tgl_mulai') || request('tgl_selesai'))
                        <a href="{{ route('peminjam.riwayat') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs px-2.5 py-1.5 rounded-lg font-semibold transition" title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </form>

                <a href="{{ route('peminjam.katalog') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition whitespace-nowrap text-center">
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

                            <!-- Status Transaksi Dinamis -->
                            <td class="py-3 px-4 border-b text-center">
                                @if($item->status == 'ditolak')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-800">
                                        Ditolak
                                    </span>
                                @elseif($item->pengembalian || $item->status == 'dikembalikan')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                        Dikembalikan
                                    </span>
                                @elseif($item->status == 'diproses')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                                        Diproses
                                    </span>
                                @elseif($item->is_telat)
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Telat Dipinjam
                                    </span>
                                @elseif($item->status == 'dipinjam')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Dipinjam
                                    </span>
                                @elseif($item->status == 'diajukan')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                                        Diajukan
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Info Denda, Kondisi, & Alasan Penolakan -->
                            <td class="py-3 px-4 border-b">
                                @if($item->status == 'ditolak')
                                    <div class="text-xs text-red-700 bg-red-50 p-2.5 rounded-lg border border-red-200 space-y-1">
                                        <div class="font-bold flex items-center gap-1">
                                            <span>❌</span> Pengajuan Ditolak
                                        </div>
                                        <div class="text-[11px] text-red-600">
                                            <span class="font-semibold text-gray-700">Alasan:</span> 
                                            {{ $item->alasan_penolakan ?? 'Tidak memenuhi syarat peminjaman.' }}
                                        </div>
                                    </div>
                                @elseif($item->pengembalian)
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

                            <!-- Tombol Aksi Kunci -->
                            <td class="py-3 px-4 border-b text-center">
                                {{-- 1. Jika masih diajukan --}}
                                @if($item->status == 'diajukan')
                                    <form action="{{ route('peminjam.peminjaman.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pengajuan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition shadow-sm">
                                            Batalkan
                                        </button>
                                    </form>

                                {{-- 2. HANYA tampilkan tombol Kembalikan jika BELUM ada record pengembalian & statusnya dipinjam --}}
                                @elseif(!$item->pengembalian && $item->status == 'dipinjam')
                                    <form action="{{ route('peminjam.peminjaman.ajukanPengembalian', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda ingin mengembalikan alat ini ke Petugas?')">
                                        @csrf
                                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition shadow-sm">
                                            Kembalikan Alat
                                        </button>
                                    </form>

                                {{-- 3. Jika sedang menunggu verifikasi --}}
                                @elseif($item->status == 'diproses')
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-amber-100 text-amber-800">
                                        Menunggu Cek Petugas
                                    </span>

                                {{-- 4. Jika DITOLAK: Berikan opsi bersihkan dari riwayat --}}
                                @elseif($item->status == 'ditolak')
                                    <form action="{{ route('peminjam.peminjaman.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat penolakan ini dari daftar Anda?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-gray-400 hover:bg-gray-500 text-white px-2.5 py-1 rounded text-xs font-semibold transition shadow-sm">
                                            Hapus Riwayat
                                        </button>
                                    </form>

                                {{-- 5. Jika SUDAH DIKEMBALIKAN --}}
                                @else
                                    <span class="text-xs text-gray-400 italic font-medium">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500 text-xs">Belum ada riwayat transaksi peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Links Pagination --}}
        <div class="p-4 border-t border-gray-200 bg-gray-50">
            {{ $peminjamans->links() }}
        </div>
    </div>

    <script>
        function updateMinReturnDatePeminjam() {
            const tglMulai = document.getElementById('peminjam_tgl_mulai').value;
            const tglSelesaiInput = document.getElementById('peminjam_tgl_selesai');
            
            if (tglMulai) {
                tglSelesaiInput.min = tglMulai;
                if (tglSelesaiInput.value && tglSelesaiInput.value < tglMulai) {
                    tglSelesaiInput.value = tglMulai;
                }
            } else {
                tglSelesaiInput.removeAttribute('min');
            }
        }

        document.addEventListener('DOMContentLoaded', updateMinReturnDatePeminjam);
    </script>
@endsection


======================================================= END RIWAYAT ==============================================


+++++++++++++++++++++++++++++++++++++++++++++++++++++++ CTLR +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori; // Tambahkan Import Kategori
use App\Models\DetailPinjam;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class PeminjamController extends Controller
{
    // Melihat daftar/katalog alat yang tersedia (LENGKAP DENGAN FILTER & SEARCH)
    public function katalogAlat(Request $request)
    {
        $search = $request->input('search');
        $kategoriId = $request->input('kategori_id');

        // Ambil semua data kategori untuk dropdown filter
        $kategoris = Kategori::all();

        $alats = Alat::with('kategori')
            ->where('stok', '>', 0)
            ->when($search, function ($query, $search) {
                return $query->where('nama_alat', 'like', "%{$search}%");
            })
            ->when($kategoriId, function ($query, $kategoriId) {
                return $query->where('kategori_id', $kategoriId);
            })
            ->latest()
            ->get();

        return view('peminjam.katalog', compact('alats', 'kategoris', 'search', 'kategoriId'));
    }

    // Memproses Pengajuan Peminjaman Alat
    public function ajukanPeminjaman(Request $request)
    {
        $request->validate([
            'tgl_pinjam' => 'required|date',
            'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
            'alat_id' => 'required|array|min:1',
            'alat_id.*' => 'exists:alat,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'integer|min:1|max:2',
        ], [
            'alat_id.required' => 'Pilih minimal satu alat yang ingin dipinjam.',
            'jumlah.*.max' => 'Maksimal jumlah peminjaman per item alat adalah 2 unit.',
        ]);

        DB::beginTransaction();
        try {
            // 1. Buat Header Peminjaman
            $peminjaman = Peminjaman::create([
                'user_id'          => auth()->id(),
                'tgl_pinjam'       => $request->tgl_pinjam,
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status'           => 'diajukan',
            ]);

            // 2. Simpan Detail Peminjaman (Hanya untuk alat_id yang dicentang)
            foreach ($request->alat_id as $alatId) {
                // Ambil jumlah pinjam spesifik berdasarkan key ID alat
                $jumlahPinjam = isset($request->jumlah[$alatId]) ? $request->jumlah[$alatId] : 1;

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id'        => $alatId,
                    'jumlah'         => $jumlahPinjam,
                ]);
            }

            DB::commit();
            return redirect()->route('peminjam.riwayat')->with('success', 'Pengajuan peminjaman berhasil dikirim.');
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
        }
    }

   // Melihat riwayat peminjaman user yang sedang login (Lengkap Filter Status, Tanggal, Search Alat, & Pagination)
    public function riwayatPeminjaman(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $tglMulai = $request->input('tgl_mulai');
        $tglSelesai = $request->input('tgl_selesai');

        // Validasi tanggal selesai tidak boleh lebih kecil dari tanggal mulai
        if ($tglMulai && $tglSelesai && $tglSelesai < $tglMulai) {
            return back()->with('error', 'Tanggal "Sampai" tidak boleh lebih awal dari tanggal "Dari".');
        }

        $peminjamans = Peminjaman::with(['detailPinjams.alat', 'pengembalian'])
            ->where('user_id', auth()->id())
            // Filter Status
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            // Filter Pencarian Nama Alat
            ->when($search, function ($query, $search) {
                return $query->whereHas('detailPinjams.alat', function ($q) use ($search) {
                    $q->where('nama_alat', 'like', "%{$search}%");
                });
            })
            // Filter Rentang Tanggal Pinjam
            ->when($tglMulai && $tglSelesai, function ($query) use ($tglMulai, $tglSelesai) {
                return $query->whereBetween('tgl_pinjam', [$tglMulai, $tglSelesai]);
            })
            ->when($tglMulai && !$tglSelesai, function ($query) use ($tglMulai) {
                return $query->whereDate('tgl_pinjam', '>=', $tglMulai);
            })
            ->when(!$tglMulai && $tglSelesai, function ($query) use ($tglSelesai) {
                return $query->whereDate('tgl_pinjam', '<=', $tglSelesai);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('peminjam.riwayat', compact('peminjamans', 'status', 'search', 'tglMulai', 'tglSelesai'));
    }

    public function showPeminjaman(Peminjaman $peminjaman)
    {
        $user = auth()->user();
        if ($peminjaman->user_id !== $user->id) {
            return redirect()->route('peminjam.katalog')->with('error', 'Akses ditolak.');
        }
        return view('peminjam.peminjaman.show', compact('peminjaman'));
    }

    public function updatePeminjaman(Request $request, Peminjaman $peminjaman)
    {
        $user = auth()->user();
        if ($user->id !== $peminjaman->user_id || $peminjaman->status !== 'diajukan') {
            return redirect()->back()->with('error', 'Tidak dapat diubah.');
        }
        $request->validate([
            'tgl_kembali_plan' => 'required|date|after:today',
        ]);
        $peminjaman->update(['tgl_kembali_plan' => $request->tgl_kembali_plan]);
        return redirect()->back()->with('success', 'Tanggal kembali diperbarui.');
    }

    public function destroyPeminjaman(Peminjaman $peminjaman)
    {
        $user = auth()->user();

        if ($user->id !== $peminjaman->user_id || !in_array($peminjaman->status, ['diajukan', 'ditolak'])) {
            return redirect()->back()->with('error', 'Tidak dapat dibatalkan.');
        }
        DB::transaction(function () use ($peminjaman) {
            $peminjaman->detailPinjams()->delete();
            $peminjaman->delete();
        });
        $pesanSukses = $peminjaman->status === 'ditolak' 
            ? 'Riwayat penolakan berhasil dibersihkan.' 
            : 'Pengajuan peminjaman berhasil dibatalkan.';

        return redirect()->route('peminjam.riwayat')->with('success', $pesanSukses);
    }

    // Peminjam mengajukan pengembalian alat
    public function ajukanPengembalian(Request $request, Peminjaman $peminjaman)
    {
        $user = auth()->user();

        if ($peminjaman->user_id !== $user->id || !in_array($peminjaman->status, ['dipinjam', 'telat'])) {
            return redirect()->back()->with('error', 'Transaksi tidak dapat diajukan untuk pengembalian.');
        }

        $peminjaman->update([
            'status' => 'diproses',
        ]);

        return redirect()->route('peminjam.riwayat')->with('success', 'Pengajuan pengembalian berhasil dikirim! Silakan serahkan fisik barang ke Petugas.');
    }
    // Method Dashboard Peminjam
    public function dashboard()
    {
        $userId = auth()->id();

        // 1. Hitung Statistik Ringkas
        $totalDipinjam = Peminjaman::where('user_id', $userId)->where('status', 'dipinjam')->count();
        $totalDiajukan = Peminjaman::where('user_id', $userId)->where('status', 'diajukan')->count();
        $totalSelesai  = Peminjaman::where('user_id', $userId)->where('status', 'dikembalikan')->count();
        
        // Total Denda yang Pernah Dikenakan
        $totalDenda = Peminjaman::where('user_id', $userId)
            ->whereHas('pengembalian')
            ->with('pengembalian')
            ->get()
            ->sum(fn($p) => $p->pengembalian->denda ?? 0);

        // 2. Ambil Peminjaman Aktif (Status 'dipinjam')
        $pinjamanAktif = Peminjaman::with('detailPinjams.alat')
            ->where('user_id', $userId)
            ->where('status', 'dipinjam')
            ->latest()
            ->get();

        // 3. Cek Peringatan Tenggat Waktu (Terlambat atau H-1)
        $today = Carbon::today();
        $peringatan = [];

        foreach ($pinjamanAktif as $pinjam) {
            $tglPlan = Carbon::parse($pinjam->tgl_kembali_plan);
            if ($today->greaterThan($tglPlan)) {
                $selisih = $today->diffInDays($tglPlan);
                $peringatan[] = [
                    'type'    => 'danger',
                    'message' => "Peminjaman ID #{$pinjam->id} sudah TERLAMBAT {$selisih} hari! Harap segera kembalikan ke Petugas."
                ];
            } elseif ($today->equalTo($tglPlan)) {
                $peringatan[] = [
                    'type'    => 'warning',
                    'message' => "Peminjaman ID #{$pinjam->id} jatuh tempo HARI INI. Jangan lupa mengembalikan alat ke Petugas."
                ];
            }
        }

        return view('peminjam.dashboard', compact(
            'totalDipinjam',
            'totalDiajukan',
            'totalSelesai',
            'totalDenda',
            'pinjamanAktif',
            'peringatan'
        ));
    }
}






sudo chown -R $USER:$USER ~/01\ API-UJIKOM/backend