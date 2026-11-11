@extends('layouts.app')

@section('title', 'Kelola User - Panel Admin')
@section('header-title', 'Manajemen Pengguna Sistem')

@section('content')
    <!-- Notifikasi Sukses/Gagal -->
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Alert Gagal / Terkena Proteksi Admin (Warna Merah) -->
    @if(session('error'))
        <div class="mb-4 p-4 rounded-lg bg-red-50 text-red-700 border border-red-200 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="text-lg font-bold text-gray-800">Daftar Pengguna Sistem</h3>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <!-- Form Search -->
                <form action="{{ route('admin.user.index') }}" method="GET" class="flex w-full md:w-80">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, role, alamat..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                        Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.user.index') }}"
                            class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 text-sm rounded-lg flex items-center transition" title="Reset Pencarian">
                            Reset
                        </a>
                    @endif
                </form>

                <!-- Tombol Tambah User -->
                <a href="{{ route('admin.user.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition whitespace-nowrap">
                    + Tambah User
                </a>
            </div>
        </div>  

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Foto</th>
                        <th class="py-3 px-4 border-b">Nama</th>
                        <th class="py-3 px-4 border-b">Email</th>
                        <th class="py-3 px-4 border-b">Role / Hak Akses</th>
                        <th class="py-3 px-4 border-b">No. HP</th>
                        <th class="py-3 px-4 border-b">Alamat</th>
                        <th class="py-3 px-4 border-b">Status</th>
                        <th class="py-3 px-4 border-b text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <!-- FOTO PROFIL KOTAK (w-12 h-12) & BISA DI-INSPECT -->
                            <td class="py-3 px-4 border-b">
                                @if($user->foto_profile)
                                    <div class="relative group cursor-pointer inline-block" onclick="openUserInspectModal('{{ asset('storage/' . $user->foto_profile) }}', '{{ addslashes($user->name) }}')">
                                        <img src="{{ asset('storage/' . $user->foto_profile) }}" 
                                             alt="Foto Profil {{ $user->name }}" 
                                             class="w-12 h-12 rounded-lg object-cover border border-gray-300 shadow-sm group-hover:opacity-80 transition">
                                        <!-- Overlay Kaca Pembesar Saat Hover -->
                                        <div class="absolute inset-0 bg-black/35 rounded-lg opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center font-bold text-sm border border-gray-300 shadow-sm" title="Belum ada foto profil">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 border-b font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="py-3 px-4 border-b">{{ $user->email }}</td>
                            <td class="py-3 px-4 border-b">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                    @if($user->role == 'admin') bg-purple-100 text-purple-800
                                    @elseif($user->role == 'petugas') bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 border-b">{{ $user->no_hp ?? '-' }}</td>
                            
                            <!-- KOLOM ALAMAT -->
                            <td class="py-3 px-4 border-b text-xs text-gray-600 max-w-xs truncate" title="{{ $user->alamat }}">
                                {{ $user->alamat ?? '-' }}
                            </td>

                            <!-- Kolom Status -->
                            <td class="py-3 px-4 border-b">
                                @if($user->is_active)
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-rose-100 text-rose-800 border border-rose-200 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom Aksi -->
                            <td class="py-3 px-4 border-b text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Tombol Toggle Status KHUSUS PEMINJAM -->
                                    @if($user->role === 'peminjam')
                                        <form action="{{ route('admin.user.toggle-status', $user->id) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} akun peminjam ini?')">
                                            @csrf
                                            @method('PATCH')
                                            
                                            @if($user->is_active)
                                                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white text-xs px-3 py-1.5 rounded transition">
                                                    Nonaktifkan
                                                </button>
                                            @else
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-1.5 rounded transition">
                                                    Aktifkan
                                                </button>
                                            @endif
                                        </form>
                                    @endif

                                    <!-- Tombol Edit -->
                                    <a href="{{ route('admin.user.edit', $user->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition">
                                        Edit
                                    </a>

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('admin.user.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-4 text-center text-gray-500">Belum ada data pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 bg-gray-50">
            {{ $users->links() }}
        </div>
    </div>

    <!-- MODAL PREVIEW / INSPECT FOTO PENGGUNA -->
    <div id="userInspectModal" style="display: none;" class="fixed inset-0 z-[9999] bg-black/80 items-center justify-center p-4">
        <div class="relative max-w-lg w-full bg-white rounded-xl shadow-2xl overflow-hidden p-5 flex flex-col items-center">
            <!-- Header Modal -->
            <div class="w-full flex justify-between items-center pb-3 border-b mb-4">
                <h4 id="userInspectTitle" class="text-sm font-bold text-gray-800">Inspect Foto Profil</h4>
                <button type="button" onclick="closeUserInspectModal()" class="text-gray-400 hover:text-gray-700 text-xl font-bold px-2">
                    ✕
                </button>
            </div>
            
            <!-- Wadah Gambar Ukuran Besar -->
            <div class="max-h-[65vh] overflow-auto flex justify-center w-full p-2">
                <img id="userInspectImage" src="" alt="Preview Foto" class="rounded-lg object-contain max-h-[60vh] border shadow-sm">
            </div>

            <!-- Tombol Aksi Bawah -->
            <div class="w-full flex justify-end gap-2 mt-4 pt-3 border-t">
                <a id="userInspectDownloadLink" href="" target="_blank" class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-semibold hover:bg-blue-100 transition">
                    Buka di Tab Baru
                </a>
                <button type="button" onclick="closeUserInspectModal()" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-300 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function openUserInspectModal(imageUrl, userName) {
            var modal = document.getElementById('userInspectModal');
            var img = document.getElementById('userInspectImage');
            var link = document.getElementById('userInspectDownloadLink');
            var title = document.getElementById('userInspectTitle');
            
            if (img && link && modal) {
                img.src = imageUrl;
                link.href = imageUrl;
                if (title && userName) {
                    title.textContent = 'Inspect Foto: ' + userName;
                }
                modal.style.display = 'flex';
            }
        }

        function closeUserInspectModal() {
            var modal = document.getElementById('userInspectModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeUserInspectModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('userInspectModal');
            if (modal) {
                modal.addEventListener('click', function(event) {
                    if (event.target === this) {
                        closeUserInspectModal();
                    }
                });
            }
        });
    </script>
@endsection