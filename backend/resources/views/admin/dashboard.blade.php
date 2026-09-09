@extends('layouts.app')
@section('title', 'Dashboard Admin - Sistem Peminjaman')
@section('header-title', 'Ringkasan Aktivitas Sistem')

@section('content')
    {{-- Subheader minimalis --}}
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Halo, <span class="font-medium text-gray-700">{{ auth()->user()->name }}</span> — @if($peminjamanDiajukan > 0) <span class="text-amber-600 font-medium">{{ $peminjamanDiajukan }} peminjaman perlu persetujuan</span> @else <span class="text-emerald-600">semua peminjaman sudah diproses</span> @endif</p>
        <span class="text-xs text-gray-400">{{ now()->format('d M Y • H:i') }}</span>
    </div>

    {{-- Alert Pesan Petugas - hanya muncul kalau ada --}}
    @if($jmlPesanTerkirim > 0)
    <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-amber-100 rounded-lg flex items-center justify-center">⚠️</div>
            <div>
                <p class="text-sm font-semibold text-amber-900">Ada {{ $jmlPesanTerkirim }} laporan perbaikan dari petugas perlu ditindak</p>
                <p class="text-xs text-amber-700">Klik untuk lihat detail • status: terkirim</p>
            </div>
        </div>
        <a href="{{ route('admin.pesan.index') }}" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg">Lihat Laporan →</a>
    </div>
    @endif

    {{-- 4 Kartu --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex justify-between items-center"><p class="text-xs font-semibold tracking-widest text-gray-400 uppercase">Total Alat</p><div class="w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center text-gray-400">◫</div></div>
            <p class="text-2xl font-bold text-gray-900 mt-3">{{ $totalAlat }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $totalKategori }} kategori</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex justify-between items-center"><p class="text-xs font-semibold tracking-widest text-gray-400 uppercase">Total User</p><div class="w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center text-gray-400">◯</div></div>
            <p class="text-2xl font-bold text-gray-900 mt-3">{{ $totalUser }}</p>
            <p class="text-xs text-gray-500 mt-1">semua role</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex justify-between items-center"><p class="text-xs font-semibold tracking-widest text-gray-400 uppercase">Peminjaman Aktif</p><div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center text-amber-500">≡</div></div>
            <p class="text-2xl font-bold text-gray-900 mt-3">{{ $peminjamanAktif }}</p>
            <p class="text-xs mt-1"><span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">{{ $peminjamanDiajukan }} diajukan</span> <span class="text-gray-400">• {{ $peminjamanDipinjam }} dipinjam</span></p>
        </div>
        <div class="bg-white border {{ $jmlPesanTerkirim > 0 ? 'border-amber-200 bg-amber-50/30' : 'border-gray-200' }} rounded-xl p-5">
            <div class="flex justify-between items-center">
                <p class="text-xs font-semibold tracking-widest {{ $jmlPesanTerkirim > 0 ? 'text-amber-600' : 'text-gray-400' }} uppercase">
                    {{ $jmlPesanTerkirim > 0 ? 'Laporan Masuk' : 'Stok Menipis' }}
                </p>
                <div class="w-8 h-8 {{ $jmlPesanTerkirim > 0 ? 'bg-amber-100 text-amber-600' : 'bg-gray-50 text-gray-400' }} rounded-lg flex items-center justify-center">{{ $jmlPesanTerkirim > 0 ? '✉' : '!' }}</div>
            </div>
            <p class="text-2xl font-bold {{ $jmlPesanTerkirim > 0 ? 'text-amber-700' : 'text-gray-900' }} mt-3">{{ $jmlPesanTerkirim > 0 ? $jmlPesanTerkirim : $stokMenipis }}</p>
            <p class="text-xs {{ $jmlPesanTerkirim > 0 ? 'text-amber-700' : 'text-gray-500' }} mt-1">{{ $jmlPesanTerkirim > 0 ? 'perlu ditindak • terkirim' : 'perlu restock • stok ≤ 3' }}</p>
        </div>
    </div>

    {{-- Bawah: Log + Aksi --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-gray-800">Log Aktivitas Terbaru</h3>
                <a href="#" class="text-xs font-medium text-gray-500 hover:text-gray-800">Lihat semua →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-xs text-gray-400 uppercase tracking-wider border-b border-gray-100"><th class="text-left font-medium px-5 py-3">Waktu</th><th class="text-left font-medium px-5 py-3">User</th><th class="text-left font-medium px-5 py-3">Aktivitas</th></tr></thead>
                    <tbody class="text-gray-700">
                        @forelse($logs as $log)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                <td class="px-5 py-3 text-xs text-gray-500">{{ $log->created_at->format('d M H:i') }} • {{ $log->created_at->diffForHumans() }}</td>
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $log->user->name ?? 'Sistem' }}</td>
                                <td class="px-5 py-3">{{ $log->aktivitas }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-gray-500">Belum ada log aktivitas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="space-y-4">
            {{-- Panel Pesan Perbaikan --}}
            <div class="bg-white border border-amber-200 rounded-xl p-5 shadow-sm">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-semibold">Pesan Perbaikan</h3>
                    @if($jmlPesanTerkirim > 0)<span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold">{{ $jmlPesanTerkirim }} baru</span>@endif
                </div>
                <div class="space-y-3 text-sm">
                    @forelse($pesanTerbaru as $ps)
                        <div class="bg-amber-50 border border-amber-100 rounded-lg p-3">
                            <p class="font-medium text-gray-900">{{ ucfirst($ps->jenis) }}</p>
                            <p class="text-xs text-gray-600 mt-1">"{{ Str::limit($ps->pesan, 70) }}"</p>
                            <p class="text-xs text-gray-500 mt-2">Petugas {{ $ps->petugas->name ?? 'N/A' }} • {{ $ps->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">Tidak ada laporan baru ✓</p>
                    @endforelse
                </div>
                <a href="{{ route('admin.pesan.index') }}" class="block text-center bg-gray-900 hover:bg-black text-white text-sm font-medium px-4 py-2.5 rounded-lg mt-4">Kelola Laporan →</a>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">Aksi Cepat</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.peminjaman.create') }}" class="flex justify-between bg-gray-900 hover:bg-black text-white text-sm font-medium px-4 py-2.5 rounded-lg transition"><span>+ Tambah Peminjaman</span><span>→</span></a>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('admin.alat.create') }}" class="bg-white border border-gray-200 hover:bg-gray-50 text-sm font-medium px-3 py-2.5 rounded-lg text-center">+ Alat</a>
                        <a href="{{ route('admin.user.create') }}" class="bg-white border border-gray-200 hover:bg-gray-50 text-sm font-medium px-3 py-2.5 rounded-lg text-center">+ User</a>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">Stok Menipis</h3>
                <div class="space-y-3 text-sm">
                    @forelse($alatsMenipis as $alat)
                        <div class="flex justify-between items-center"><div><p class="font-medium text-gray-900">{{ $alat->nama_alat }}</p><p class="text-xs text-gray-500">{{ $alat->kategori->nama_kategori ?? '-' }} • sisa {{ $alat->stok }}</p></div><span class="text-xs {{ $alat->stok <=1 ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700' }} px-2 py-1 rounded-full font-medium">{{ $alat->stok <=1 ? 'kritis' : 'menipis' }}</span></div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-2">Stok aman ✓</p>
                    @endforelse
                </div>
                <a href="{{ route('admin.alat.index') }}" class="block text-center text-xs font-medium text-gray-500 hover:text-gray-800 mt-4">Kelola Alat →</a>
            </div>
        </div>
    </div>
@endsection
