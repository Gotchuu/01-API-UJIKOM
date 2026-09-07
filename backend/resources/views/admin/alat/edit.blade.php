@extends('layouts.app')

@section('title', 'Edit Alat - Panel Admin')
@section('header-title', 'Edit Data Alat')

@section('content')
<div class="max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('admin.alat.update', $alat->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Nama Alat -->
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Alat</label>
            <input type="text" name="nama_alat" value="{{ old('nama_alat', $alat->nama_alat) }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('nama_alat') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Kategori -->
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Kategori</label>
            <select name="kategori_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih Kategori --</option>
                @foreach($kategori as $item)
                    <option value="{{ $item->id }}" {{ old('kategori_id', $alat->kategori_id) == $item->id ? 'selected' : '' }}>
                        {{ $item->nama_kategori }}
                    </option>
                @endforeach
            </select>
            @error('kategori_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Stok & Kondisi (Grid 2 Kolom) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Stok</label>
                <input type="number" name="stok" value="{{ old('stok', $alat->stok) }}" min="0" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('stok') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Status Kondisi</label>
                <select name="status_kondisi" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Baik" {{ old('status_kondisi', $alat->status_kondisi) == 'Baik' ? 'selected' : '' }}>Baik</option>
                    <option value="Rusak Ringan" {{ old('status_kondisi', $alat->status_kondisi) == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="Rusak Berat" {{ old('status_kondisi', $alat->status_kondisi) == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                </select>
                @error('status_kondisi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Preview Gambar Lama & Upload Gambar Baru -->
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Gambar Alat (Opsional)</label>
            @if($alat->gambar)
                <div class="mb-3">
                    <span class="block text-xs text-gray-500 mb-1">Gambar Saat Ini:</span>
                    <img src="{{ asset($alat->gambar) }}" alt="{{ $alat->nama_alat }}" class="w-20 h-20 object-cover rounded-lg border">
                </div>
            @endif
            <input type="file" name="gambar" accept="image/*"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <span class="text-xs text-gray-400 mt-1 block">Biarkan kosong jika tidak ingin mengubah gambar.</span>
            @error('gambar') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('admin.alat.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded-lg transition">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                Perbarui Alat
            </button>
        </div>
    </form>
</div>
@endsection