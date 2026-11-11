@extends('layouts.app')

@section('title', 'Edit User - Panel Admin')
@section('header-title', 'Edit Data Pengguna')

@section('content')
<div class="max-w-xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('admin.user.update', $user->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Password Baru 
                <span class="text-xs text-gray-400 font-normal">(Kosongkan jika tidak ingin mengubah password)</span></label>
            <input type="password" name="password" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- FOTO PROFILE DENGAN UKURAN LEBIH BESAR & FITUR INSPECT / PREVIEW -->
        <div class="mb-4">
            <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Foto Profile (opsional)</label>
            
            @if($user->foto_profile)
                <div class="mb-3 flex items-center gap-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    <!-- Foto Diperbesar (w-20 h-20) dan bisa diklik untuk inspect -->
                    <div class="relative group cursor-pointer" onclick="openImageModal('{{ asset('storage/' . $user->foto_profile) }}')">
                        <img src="{{ asset('storage/' . $user->foto_profile) }}" 
                             alt="Foto Profil {{ $user->name }}" 
                             class="w-20 h-20 rounded-full object-cover border-2 border-white shadow-md group-hover:opacity-85 transition">
                        <div class="absolute inset-0 bg-black/40 rounded-full opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-700">Foto profil saat ini</p>
                        <p class="text-[11px] text-blue-600 hover:underline cursor-pointer" onclick="openImageModal('{{ asset('storage/' . $user->foto_profile) }}')">
                            Klik foto untuk memperbesar / inspect
                        </p>
                    </div>
                </div>
            @endif

            <input type="file" name="foto_profile" accept="image/*" class="w-full text-sm border border-gray-300 rounded-lg p-2 bg-white">
            <p class="text-[10px] text-gray-500 mt-1">Format: JPG/PNG, maksimal 2MB (Unggah baru untuk mengganti foto)</p>
            @error('foto_profile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Role / Hak Akses</label>
            
            @if(auth()->id() === $user->id)
                <select disabled class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
                    <option value="admin" selected>Admin (Akun Anda Saat Ini)</option>
                </select>
                <input type="hidden" name="role" value="{{ $user->role }}">
                <p class="text-[11px] text-amber-600 mt-1 italic">*Anda tidak dapat mengubah role akun Anda sendiri.</p>
            @else
                <select name="role" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="petugas" {{ $user->role === 'petugas' ? 'selected' : '' }}>Petugas</option>
                    <option value="peminjam" {{ $user->role === 'peminjam' ? 'selected' : '' }}>Peminjam / Siswa</option>
                </select>
            @endif
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">No. HP (Opsional)</label>
            <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- INPUT EDIT ALAMAT -->
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Alamat Lengkap (Opsional)</label>
            <textarea name="alamat" rows="3" placeholder="Masukkan alamat domisili user..." 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">{{ old('alamat', $user->alamat) }}</textarea>
            @error('alamat') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.user.index') }}" 
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Perbarui</button>
        </div>
    </form>
</div>

<!-- MODAL PREVIEW / INSPECT GAMBAR -->
<div id="imageInspectModal" style="display: none;" class="fixed inset-0 z-50 bg-black/80 items-center justify-center p-4">
    <div class="relative max-w-2xl w-full bg-white rounded-xl shadow-2xl overflow-hidden p-4 flex flex-col items-center">
        <!-- Header Modal -->
        <div class="w-full flex justify-between items-center pb-3 border-b mb-4">
            <h4 class="text-sm font-bold text-gray-800">Inspect Foto Profil</h4>
            <button type="button" onclick="closeImageModal()" class="text-gray-400 hover:text-gray-700 text-lg font-bold px-2">
                ✕
            </button>
        </div>
        
        <!-- Wadah Gambar Ukuran Besar -->
        <div class="max-h-[70vh] overflow-auto flex justify-center w-full">
            <img id="modalPreviewImage" src="" alt="Preview Foto" class="rounded-lg object-contain max-h-[65vh] border shadow-sm">
        </div>

        <!-- Tombol Aksi Bawah -->
        <div class="w-full flex justify-end gap-2 mt-4 pt-3 border-t">
            <a id="downloadImageLink" href="" target="_blank" class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-semibold hover:bg-blue-100 transition">
                Buka di Tab Baru
            </a>
            <button type="button" onclick="closeImageModal()" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-300 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function openImageModal(imageUrl) {
        const modal = document.getElementById('imageInspectModal');
        const img = document.getElementById('modalPreviewImage');
        const link = document.getElementById('downloadImageLink');
        
        if (img && link && modal) {
            img.src = imageUrl;
            link.href = imageUrl;
            modal.style.display = 'flex';
        }
    }

    function closeImageModal() {
        const modal = document.getElementById('imageInspectModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Menutup modal jika tombol ESC ditekan
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeImageModal();
        }
    });

    // Menutup modal jika area gelap di luar modal diklik
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('imageInspectModal');
        if (modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === this) {
                    closeImageModal();
                }
            });
        }
    });
</script>

@endsection