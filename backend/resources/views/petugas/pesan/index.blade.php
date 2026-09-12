@extends('layouts.app')
@section('title', 'Riwayat Laporan Saya')
@section('header-title', 'Riwayat Laporan Saya')

@section('content')
    
    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 p-3 rounded text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="flex gap-2 mb-4">
        <a href="{{ route('petugas.pesan.index') }}" class="px-4 py-2 rounded-full text-sm {{ !$status ? 'bg-gray-900 text-white' : 'bg-white border' }}">Semua</a>
        <a href="{{ route('petugas.pesan.index', ['status'=>'terkirim']) }}" class="px-4 py-2 rounded-full text-sm {{ $status=='terkirim' ? 'bg-amber-500 text-white' : 'bg-white border' }}">Terkirim</a>
        <a href="{{ route('petugas.pesan.index', ['status'=>'dibaca']) }}" class="px-4 py-2 rounded-full text-sm {{ $status=='dibaca' ? 'bg-gray-700 text-white' : 'bg-white border' }}">Dibaca</a>
        <a href="{{ route('petugas.pesan.index', ['status'=>'selesai']) }}" class="px-4 py-2 rounded-full text-sm {{ $status=='selesai' ? 'bg-emerald-600 text-white' : 'bg-white border' }}">Selesai</a>
    </div>

    <div class="bg-white border rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-xs uppercase text-gray-500 border-b">
                    <th class="px-5 py-3">Pengembalian</th><th class="px-5 py-3">Jenis</th><th class="px-5 py-3">Pesan Saya</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Balasan Admin</th>
                </tr></thead>
                <tbody>
                    @forelse($pesans as $p)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-5 py-3 text-xs">@if($p->pengembalian)<b>#{{ $p->pengembalian_id }}</b> - {{ $p->pengembalian->peminjaman->user->name ?? 'User Dihapus' }}@else<span class="font-semibold text-emerald-700">Laporan #{{ $p->id }} - Dihapus</span>@endif</td>
                            <td class="px-5 py-3"><span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs">{{ ucfirst($p->jenis) }}</span></td>
                            <td class="px-5 py-3 max-w-xs truncate">{{ Str::limit($p->pesan, 60) }}</td>
                            <td class="px-5 py-3"><span class="px-2 py-1 rounded-full text-xs font-bold @if($p->status=='terkirim') bg-amber-100 text-amber-700 @elseif($p->status=='dibaca') bg-gray-100 @else bg-emerald-100 text-emerald-700 @endif">{{ ucfirst($p->status) }}</span></td>
                            <td class="px-5 py-3 text-xs">@if($p->admin_catatan)<div class="bg-gray-50 border rounded p-2">{{ $p->admin->name ?? 'Admin' }}: "{{ $p->admin_catatan }}"</div>@else <span class="text-gray-400">— Belum dibalas —</span> @endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-gray-500">Belum ada laporan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t bg-gray-50">{{ $pesans->links() }}</div>
    </div>
@endsection