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
                <div class="w-full md:w-72">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Rencana Tanggal Kembali <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_kembali_plan" min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
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
                                    <!-- Name input jumlah diubah menggunakan Key ID Alat -->
                                    <input type="number" name="jumlah[{{ $alat->id }}]" value="1" min="1" max="{{ $alat->stok }}" 
                                           class="w-full text-center text-sm border border-gray-300 rounded-lg p-1.5 focus:ring-blue-500"
                                           oninput="if(this.value>{{ $alat->stok }})this.value={{ $alat->stok }};if(this.value<1)this.value=1;">
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
    </script>
@endsection