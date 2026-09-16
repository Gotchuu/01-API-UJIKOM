<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Peminjaman Alat</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-b: 2px solid #1e293b; padding-bottom: 10px; }
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
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 8px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .badge-diajukan { background-color: #fef3c7; color: #92400e; }
        .badge-dipinjam { background-color: #dbeafe; color: #1e40af; }
        .badge-dikembalikan { background-color: #d1fae5; color: #065f46; }
        .badge-telat { background-color: #fee2e2; color: #991b1b; }
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
                        <span class="badge badge-{{ $p->status }}">{{ ucfirst($p->status) }}</span>
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