@extends('layouts.app')

@section('title', 'Edit Alat - Panel Admin')
@section('header-title', 'Edit Data Alat')

@section('content')
<div class="max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('admin.alat.update', $alat->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Alat</label>
            <input type="text" name="nama_alat" value="{{ old('nama_alat', $alat->nama_alat) }}" required 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            @error('nama_alat') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Kategori Alat</label>
            <select name="kategori_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                @foreach($kategori as $k)
                    <option value="{{ $k->id }}" {{ old('kategori_id', $alat->kategori_id) == $k->id ? 'selected' : '' }}>
                        {{ $k->nama_kategori }}
                    </option>
                @endforeach
            </select>
            @error('kategori_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- GRID RINCIAN STOK BERDASARKAN KONDISI -->
        <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <label class="block text-gray-800 text-sm font-bold uppercase mb-3">Rincian Stok Alat per Kondisi</label>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-bold text-emerald-700 mb-1">Baik / Siap Pinjam</label>
                    <input type="number" name="stok_baik" min="0" value="{{ old('stok_baik', $alat->stok_baik) }}" required
                        class="w-full px-3 py-2 border border-emerald-300 rounded-lg focus:ring-2 focus:ring-emerald-500 text-sm font-bold text-emerald-800">
                    @error('stok_baik') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-amber-700 mb-1">Rusak Ringan</label>
                    <input type="number" name="stok_rusak_ringan" min="0" value="{{ old('stok_rusak_ringan', $alat->stok_rusak_ringan) }}" required
                        class="w-full px-3 py-2 border border-amber-300 rounded-lg focus:ring-2 focus:ring-amber-500 text-sm font-bold text-amber-800">
                    @error('stok_rusak_ringan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-orange-700 mb-1">Rusak Berat</label>
                    <input type="number" name="stok_rusak_berat" min="0" value="{{ old('stok_rusak_berat', $alat->stok_rusak_berat) }}" required
                        class="w-full px-3 py-2 border border-orange-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm font-bold text-orange-800">
                    @error('stok_rusak_berat') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-red-700 mb-1">Hilang</label>
                    <input type="number" name="stok_hilang" min="0" value="{{ old('stok_hilang', $alat->stok_hilang) }}" required
                        class="w-full px-3 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm font-bold text-red-800">
                    @error('stok_hilang') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            <p class="text-[11px] text-gray-500 mt-2">* Admin dapat memindahkan atau menyesuaikan jumlah unit jika alat selesai diperbaiki atau ditemukan.</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Status Kondisi Umum Master</label>
            <select name="status_kondisi" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="Baik & Lengkap" {{ old('status_kondisi', $alat->status_kondisi) == 'Baik & Lengkap' ? 'selected' : '' }}>Baik & Lengkap</option>
                <option value="Rusak Ringan" {{ old('status_kondisi', $alat->status_kondisi) == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                <option value="Rusak Berat" {{ old('status_kondisi', $alat->status_kondisi) == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Deskripsi / Catatan (Opsional)</label>
            <textarea name="deskripsi" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">{{ old('deskripsi', $alat->deskripsi) }}</textarea>
        </div>

        <!-- GAMBAR ALAT DENGAN FITUR INSPECT / PREVIEW -->
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Gambar Alat (Opsional)</label>
            
            @if($alat->gambar)
                <div class="mb-3 flex items-center gap-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    <!-- Thumbnail alat (w-24 h-24 / rounded-lg) yang dapat diklik -->
                    <div class="relative group cursor-pointer" onclick="openAlatModal('{{ asset($alat->gambar) }}')">
                        <img src="{{ asset($alat->gambar) }}" 
                             alt="Gambar {{ $alat->nama_alat }}" 
                             class="w-24 h-24 rounded-lg object-cover border-2 border-white shadow-md group-hover:opacity-80 transition">
                        <div class="absolute inset-0 bg-black/40 rounded-lg opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-700">Gambar alat saat ini</p>
                        <button type="button" class="text-[11px] text-blue-600 hover:underline cursor-pointer p-0 bg-transparent border-0 inline-block text-left" onclick="openAlatModal('{{ asset($alat->gambar) }}')">
                            Klik foto / tulisan ini untuk inspect
                        </button>
                    </div>
                </div>
            @endif

            <input type="file" name="gambar" accept="image/*" class="w-full text-sm border border-gray-300 rounded-lg p-2 bg-white">
            <p class="text-[10px] text-gray-500 mt-1">Format: JPG/PNG/WEBP, maksimal 2MB (Unggah file baru jika ingin mengganti)</p>
            @error('gambar') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.alat.index') }}" 
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Simpan Perubahan</button>
        </div>
    </form>
</div>

<!-- MODAL PREVIEW / INSPECT GAMBAR ALAT -->
<div id="alatInspectModal" style="display: none;" class="fixed inset-0 z-[9999] bg-black/80 items-center justify-center p-4">
    <div class="relative max-w-2xl w-full bg-white rounded-xl shadow-2xl overflow-hidden p-5 flex flex-col items-center">
        <!-- Header Modal -->
        <div class="w-full flex justify-between items-center pb-3 border-b mb-4">
            <h4 class="text-sm font-bold text-gray-800">Inspect Gambar Alat</h4>
            <button type="button" onclick="closeAlatModal()" class="text-gray-400 hover:text-gray-700 text-xl font-bold px-2">
                ✕
            </button>
        </div>
        
        <!-- Wadah Gambar Ukuran Besar -->
        <div class="max-h-[70vh] overflow-auto flex justify-center w-full p-2">
            <img id="alatPreviewImage" src="" alt="Preview Alat" class="rounded-lg object-contain max-h-[60vh] border shadow-sm">
        </div>

        <!-- Tombol Aksi Bawah -->
        <div class="w-full flex justify-end gap-2 mt-4 pt-3 border-t">
            <a id="alatDownloadLink" href="" target="_blank" class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-semibold hover:bg-blue-100 transition">
                Buka di Tab Baru
            </a>
            <button type="button" onclick="closeAlatModal()" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-300 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function openAlatModal(imageUrl) {
        var modal = document.getElementById('alatInspectModal');
        var img = document.getElementById('alatPreviewImage');
        var link = document.getElementById('alatDownloadLink');
        
        if (img && link && modal) {
            img.src = imageUrl;
            link.href = imageUrl;
            modal.style.display = 'flex';
        }
    }

    function closeAlatModal() {
        var modal = document.getElementById('alatInspectModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeAlatModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('alatInspectModal');
        if (modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === this) {
                    closeAlatModal();
                }
            });
        }
    });
</script>
@endsection