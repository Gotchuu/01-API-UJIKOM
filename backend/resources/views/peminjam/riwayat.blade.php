@extends('layouts.app')
@section('title','Riwayat Peminjaman')
@section('header-title','Riwayat Pinjam')

@section('content')
<div class="bg-white border rounded-xl overflow-hidden">
    <div class="p-5 border-b bg-gray-50"><h3 class="font-bold">Riwayat Peminjaman Saya</h3></div>
    
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs uppercase"><tr><th class="px-4 py-3">#</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th></tr></thead>
        <tbody>
            @forelse($peminjamans as $p)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3">{{ $p->id }}</td>
                <td class="px-4 py-3">{{ $p->tgl_pinjam }} → {{ $p->tgl_kembali_plan }}</td>
                <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-bold
                    @if($p->status=='diajukan') bg-amber-100 text-amber-700
                    @elseif($p->status=='dipinjam') bg-blue-100 text-blue-700
                    @elseif($p->status=='telat') bg-red-100 text-red-700
                    @else bg-emerald-100 text-emerald-700 @endif">{{ ucfirst($p->status) }}</span></td>
                <td class="px-4 py-3"><a href="{{ route('peminjam.peminjaman.show', $p->id) }}" class="text-blue-600 hover:underline text-xs">Lihat Detail</a></td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada riwayat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection