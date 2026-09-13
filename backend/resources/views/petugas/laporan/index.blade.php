@extends('layouts.app')
@section('title', 'Laporan Peminjaman')
@section('header-title', 'Laporan Peminjaman')

@section('content')
<div class="bg-white border rounded-xl p-5 mb-4">
    <form action="{{ route('petugas.laporan.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div><label class="text-xs">Dari</label><input type="date" name="dari" value="{{ $dari }}" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
        <div><label class="text-xs">Sampai</label><input type="date" name="sampai" value="{{ $sampai }}" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
        <div><label class="text-xs">Status</label>
            <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">Semua</option><option value="diajukan" @selected($status=='diajukan')>Diajukan</option><option value="dipinjam" @selected($status=='dipinjam')>Dipinjam</option><option value="dikembalikan" @selected($status=='dikembalikan')>Dikembalikan</option><option value="telat" @selected($status=='telat')>Telat</option>
            </select>
        </div>
        <div class="flex items-end gap-2 md:col-span-2">
            <button class="flex-1 bg-gray-900 text-white px-4 py-2.5 rounded-lg text-sm">Tampilkan</button>
            <a href="{{ route('petugas.laporan.cetak', ['dari'=>$dari,'sampai'=>$sampai,'status'=>$status]) }}" class="flex-1 bg-red-500 text-white px-4 py-2.5 rounded-lg text-sm text-center">Cetak PDF →</a>
            <a href="{{ route('petugas.laporan.index') }}" class="bg-white border px-4 py-2.5 rounded-lg text-sm">Reset</a>
        </div>
    </form>
</div>

<div class="bg-white border rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 text-xs uppercase border-b"><th class="px-4 py-3">#</th><th class="px-4 py-3">Peminjam</th><th class="px-4 py-3">Alat</th><th class="px-4 py-3">Tgl Pinjam</th><th class="px-4 py-3">Status</th></tr></thead>
        <tbody>
            @forelse($peminjamans as $p)
                <tr class="border-b"><td class="px-4 py-3">{{ $p->id }}</td><td class="px-4 py-3">{{ $p->user->name }}</td><td class="px-4 py-3">{{ $p->detailPinjams->map(fn($d)=>$d->alat->nama_alat.'('.$d->jumlah.')')->join(', ') }}</td><td class="px-4 py-3">{{ $p->tgl_pinjam }}</td><td class="px-4 py-3">{{ ucfirst($p->status) }}</td></tr>
            @empty
                <tr><td colspan="5" class="py-6 text-center text-gray-500">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-4 bg-gray-50 border-t">{{ $peminjamans->links() }}</div>
</div>
@endsection