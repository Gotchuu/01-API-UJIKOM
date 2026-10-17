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

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Gambar Alat (Opsional)</label>
            @if($alat->gambar)
                <div class="mb-2">
                    <img src="{{ asset($alat->gambar) }}" class="w-16 h-16 object-cover rounded-lg border">
                </div>
            @endif
            <input type="file" name="gambar" accept="image/*" class="w-full text-sm border border-gray-300 rounded-lg p-2">
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.alat.index') }}" 
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection