============================================INDEX LPRN=========================================================


@extends('layouts.app')
@section('title', 'Laporan Peminjaman')
@section('header-title', 'Laporan Peminjaman & Pengembalian Alat')

@section('content')
{{-- RINGKASAN STATISTIK SINGKAT --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <div class="text-xs text-gray-500 uppercase font-semibold">Total Transaksi</div>
        <div class="text-xl font-bold text-gray-800 mt-1">{{ $peminjamans->total() }}</div>
    </div>
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <div class="text-xs text-gray-500 uppercase font-semibold">Aktif Dipinjam</div>
        <div class="text-xl font-bold text-blue-600 mt-1">
            {{ $peminjamans->whereIn('status', ['dipinjam', 'diproses'])->count() }}
        </div>
    </div>
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <div class="text-xs text-gray-500 uppercase font-semibold">Dikembalikan</div>
        <div class="text-xl font-bold text-emerald-600 mt-1">
            {{ $peminjamans->where('status', 'dikembalikan')->count() }}
        </div>
    </div>
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <div class="text-xs text-gray-500 uppercase font-semibold">Total Denda (Halaman Ini)</div>
        <div class="text-xl font-bold text-red-600 mt-1">
            Rp {{ number_format($peminjamans->sum(fn($p) => $p->pengembalian->denda ?? 0), 0, ',', '.') }}
        </div>
    </div>
</div>

{{-- FORM FILTER DATA --}}
<div class="bg-white border rounded-xl p-5 mb-4 shadow-sm">
    <form action="{{ route('petugas.laporan.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div>
            <label class="text-xs font-semibold text-gray-600">Dari Tanggal</label>
            <input type="date" id="tgl_dari" name="dari" value="{{ $dari }}" onchange="updateMinReturnDate()" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-600">Sampai Tanggal</label>
            <input type="date" id="tgl_sampai" name="sampai" value="{{ $sampai }}" min="{{ $dari }}" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-600">Status Transaksi</label>
            <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="diajukan" @selected($status=='diajukan')>Diajukan</option>
                <option value="dipinjam" @selected($status=='dipinjam')>Dipinjam</option>
                <option value="diproses" @selected($status=='diproses')>Diproses</option>
                <option value="dikembalikan" @selected($status=='dikembalikan')>Dikembalikan</option>
                <option value="telat" @selected($status=='telat')>Telat</option>
                <option value="ditolak" @selected($status=='ditolak')>Ditolak</option>
            </select>
        </div>
        <div class="flex items-end gap-2 md:col-span-2">
            <button type="submit" class="flex-1 bg-gray-900 hover:bg-black text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition">
                Tampilkan
            </button>
            <a href="{{ route('petugas.laporan.cetak', request()->all()) }}" target="_blank" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold text-center transition">
                Cetak PDF →
            </a>
            <a href="{{ route('petugas.laporan.index') }}" class="bg-gray-100 hover:bg-gray-200 border px-4 py-2.5 rounded-lg text-sm text-gray-700 transition">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- TABEL LAPORAN DETAILED --}}
<div class="bg-white border rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-gray-100 text-gray-700 text-xs uppercase tracking-wider border-b">
                    <th class="px-4 py-3 text-center"># ID</th>
                    <th class="px-4 py-3">Peminjam</th>
                    <th class="px-4 py-3">Rincian Alat & Unit</th>
                    <th class="px-4 py-3">Riwayat Tanggal</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3">Kondisi & Denda</th>
                    <th class="px-4 py-3">Petugas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($peminjamans as $p)
                    <tr class="hover:bg-gray-50 transition align-top">
                        <td class="px-4 py-3 text-center font-bold text-gray-600">#{{ $p->id }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $p->user->name ?? 'User Dihapus' }}</td>
                        <td class="px-4 py-3">
                            <ul class="list-disc list-inside space-y-0.5 text-xs">
                                @foreach($p->detailPinjams as $d)
                                    <li>{{ $d->alat->nama_alat ?? 'Alat Dihapus' }} <span class="font-bold text-gray-600">({{ $d->jumlah }} unit)</span></li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="px-4 py-3 text-xs space-y-1">
                            <div><span class="text-gray-500">Pinjam:</span> {{ $p->tgl_pinjam }}</div>
                            <div><span class="text-gray-500">Rencana:</span> {{ $p->tgl_kembali_plan }}</div>
                            @if($p->pengembalian)
                                <div class="text-emerald-700 font-semibold"><span class="text-gray-500">Kembali:</span> {{ $p->pengembalian->tgl_kembali }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $st = strtolower($p->status);
                                $badgeColor = match($st) {
                                    'diajukan' => 'bg-amber-100 text-amber-800 border border-amber-200',
                                    'dipinjam', 'diproses' => 'bg-blue-100 text-blue-800 border border-blue-200',
                                    'dikembalikan' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                                    'ditolak' => 'bg-gray-200 text-gray-800 border border-gray-300',
                                    'telat' => 'bg-red-100 text-red-800 border border-red-200',
                                    default => 'bg-gray-100 text-gray-700'
                                };
                            @endphp
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $badgeColor }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            @if($p->pengembalian)
                                <div class="font-medium text-gray-800">Kondisi: {{ $p->pengembalian->kondisi_kembali }}</div>
                                @if($p->pengembalian->denda > 0)
                                    <div class="text-red-600 font-bold">Denda: Rp {{ number_format($p->pengembalian->denda, 0, ',', '.') }}</div>
                                @else
                                    <div class="text-emerald-600 font-semibold">Bebas Denda</div>
                                @endif
                            @else
                                <span class="text-gray-400 italic">Belum kembali</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            {{ $p->pengembalian->petugas->name ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-gray-500">Tidak ada data transaksi peminjaman ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 bg-gray-50 border-t">{{ $peminjamans->links() }}</div>
</div>

<script>
    function updateMinReturnDate() {
        const dariInput = document.getElementById('tgl_dari');
        const sampaiInput = document.getElementById('tgl_sampai');
        
        if (dariInput && sampaiInput && dariInput.value) {
            sampaiInput.min = dariInput.value;
            if (sampaiInput.value && sampaiInput.value < dariInput.value) {
                sampaiInput.value = dariInput.value;
            }
        }
    }
    document.addEventListener('DOMContentLoaded', updateMinReturnDate);
</script>
@endsection




====================================================END INDEX============================================




<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Peminjaman Alat</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e293b; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; color: #0f172a; }
        .header p { margin: 2px 0 0; font-size: 10px; color: #64748b; }
        .info-bar { margin-bottom: 15px; width: 100%; }
        .info-bar td { border: none; padding: 0; font-size: 10px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        table.data-table th { background-color: #f1f5f9; color: #1e293b; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .badge-diajukan { background-color: #fef3c7; color: #92400e; }
        .badge-dipinjam, .badge-diproses { background-color: #dbeafe; color: #1e40af; }
        .badge-dikembalikan { background-color: #d1fae5; color: #065f46; }
        .badge-telat { background-color: #fee2e2; color: #991b1b; }
        .badge-ditolak { background-color: #e2e8f0; color: #334155; }
        .summary-box { float: right; width: 250px; margin-top: 10px; border: 1px solid #cbd5e1; padding: 8px; background-color: #f8fafc; }
        .footer { margin-top: 40px; width: 100%; }
        .footer td { border: none; text-align: center; font-size: 10px; }
    </style>
</head>
<body>

    <!-- KOP SURAT LAPORAN -->
    <div class="header">
        <h2>Sistem Informasi Peminjaman Alat</h2>
        <p>Laporan Rekapitulasi Transaksi Peminjaman & Pengembalian</p>
    </div>

    <!-- METADATA LAPORAN -->
    <table class="info-bar">
        <tr>
            <td><strong>Periode:</strong> {{ date('d M Y', strtotime($dari)) }} s/d {{ date('d M Y', strtotime($sampai)) }}</td>
            <td class="text-right"><strong>Filter Status:</strong> {{ $status ? ucfirst($status) : 'Semua Status' }}</td>
        </tr>
    </table>

    <!-- TABEL DATA LAPORAN -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">#</th>
                <th width="15%">Peminjam</th>
                <th width="25%">Rincian Alat (Unit)</th>
                <th width="20%">Riwayat Tanggal</th>
                <th width="10%" class="text-center">Status</th>
                <th width="15%">Kondisi / Denda</th>
                <th width="10%">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotalDenda = 0; @endphp
            @forelse($peminjamans as $index => $p)
                @php 
                    $denda = $p->pengembalian->denda ?? 0;
                    $grandTotalDenda += $denda;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $p->user->name ?? 'User Dihapus' }}</td>
                    <td>
                        <ul style="margin: 0; padding-left: 12px;">
                            @foreach($p->detailPinjams as $d)
                                <li>{{ $d->alat->nama_alat ?? 'Alat Dihapus' }} (<strong>{{ $d->jumlah }}</strong>)</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>
                        Pj: {{ date('d/m/Y', strtotime($p->tgl_pinjam)) }}<br>
                        Rc: {{ date('d/m/Y', strtotime($p->tgl_kembali_plan)) }}<br>
                        @if($p->pengembalian)
                            <span style="color: #047857;">Km: {{ date('d/m/Y', strtotime($p->pengembalian->tgl_kembali)) }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-{{ strtolower($p->status) }}">{{ ucfirst($p->status) }}</span>
                    </td>
                    <td>
                        @if($p->pengembalian)
                            {{ $p->pengembalian->kondisi_kembali }}<br>
                            @if($denda > 0)
                                <strong style="color: #dc2626;">Denda: Rp {{ number_format($denda, 0, ',', '.') }}</strong>
                            @else
                                <span style="color: #16a34a;">Bebas Denda</span>
                            @endif
                        @else
                            <span style="color: #94a3b8; font-style: italic;">Belum Kembali</span>
                        @endif
                    </td>
                    <td>{{ $p->pengembalian->petugas->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #64748b;">Tidak ada data peminjaman pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- RINGKASAN REKAPITULASI -->
    <div class="summary-box">
        <table style="width: 100%; border: none;">
            <tr style="border: none;">
                <td style="border: none;">Total Transaksi:</td>
                <td style="border: none;" class="text-right font-bold">{{ count($peminjamans) }} Data</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;">Total Denda Terkumpul:</td>
                <td style="border: none;" class="text-right font-bold" style="color: #dc2626;">Rp {{ number_format($grandTotalDenda, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <!-- TANDA TANGAN PETUGAS -->
    <table class="footer">
        <tr>
            <td width="60%"></td>
            <td width="40%">
                Dicetak Pada: {{ date('d F Y H:i') }}<br>
                Petugas Penanggung Jawab,<br><br><br><br>
                <strong><u>{{ auth()->user()->name }}</u></strong><br>
                NIP / ID: {{ auth()->id() }}
            </td>
        </tr>
    </table>

</body>
</html>





+++++++++++++++++++++++++++++++++++++++++++++ END PDF FORMAT +++++++++++++++++++++++++++++++++++++++






public function indexLaporan(Request $request)
    {
        $dari = $request->input('dari', now()->subMonth()->toDateString());
        $sampai = $request->input('sampai', now()->toDateString());
        $status = $request->input('status');

        if ($request->filled('dari') && $request->filled('sampai')) {
            if ($sampai < $request->input('dari')) {
                return back()->with('error', 'Tanggal "Sampai" tidak boleh lebih awal dari tanggal "Dari".');
            }
        }

        // Query Utama dengan penanganan Soft Delete
        $query = Peminjaman::with([
            'user' => fn($u) => $u->withTrashed(),
            'detailPinjams.alat' => fn($a) => $a->withTrashed(),
            'pengembalian.petugas' => fn($p) => $p->withTrashed()
        ])
        ->whereBetween('tgl_pinjam', [$dari, $sampai])
        ->when($status, fn($q) => $q->where('status', $status));

        $peminjamans = $query->latest()->paginate(10)->withQueryString();

        return view('petugas.laporan.index', compact('peminjamans', 'dari', 'sampai', 'status'));
    }

    public function cetakPdf(Request $request)
    {
        $dari = $request->input('dari', now()->subMonth()->toDateString());
        $sampai = $request->input('sampai', now()->toDateString());
        $status = $request->input('status');

        if ($request->filled('dari') && $request->filled('sampai')) {
            if ($sampai < $request->input('dari')) {
                return back()->with('error', 'Tanggal "Sampai" tidak boleh lebih awal dari tanggal "Dari".');
            }
        }

        $peminjamans = Peminjaman::with([
            'user' => fn($u) => $u->withTrashed(),
            'detailPinjams.alat' => fn($a) => $a->withTrashed(),
            'pengembalian.petugas' => fn($p) => $p->withTrashed()
        ])
        ->whereBetween('tgl_pinjam', [$dari, $sampai])
        ->when($status, fn($q) => $q->where('status', $status))
        ->latest()
        ->get();

        $pdf = Pdf::loadView('petugas.laporan.pdf', compact('peminjamans', 'dari', 'sampai', 'status'));

        return $pdf->stream('Laporan-Peminjaman-' . $dari . '-sampai-' . $sampai . '.pdf');
    }