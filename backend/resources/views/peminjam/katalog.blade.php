<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Katalog Alat - Peminjam</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="#">Panel Peminjam</a>
    <div class="d-flex">
      <a href="{{ route('peminjam.riwayat') }}" class="btn btn-outline-light btn-sm me-2">Riwayat Pinjam</a>
      <form action="{{ route('logout') }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-light btn-sm text-primary">Logout</button></form>
    </div>
  </div>
</nav>

<div class="container">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <h3 class="mb-3">Katalog Alat Tersedia</h3>

  <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST">
    @csrf
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Rencana Tanggal Kembali</label>
          <input type="date" name="tgl_kembali_plan" class="form-control" required>
        </div>

        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th width="50">Pilih</th>
              <th>Alat</th>
              <th>Kategori</th>
              <th>Stok</th>
              <th width="150">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            @forelse($alats as $alat)
            <tr>
              <td class="text-center"><input type="checkbox" name="alat_id[]" value="{{ $alat->id }}" class="form-check-input"></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  @php
    $placeholder = 'https://placehold.co/120x120/f3f4f6/9ca3af?text=' . urlencode(Str::substr(Str::upper($alat->nama_alat), 0, 2));
                  $gambarAda = $alat->gambar && file_exists(public_path('storage/' . $alat->gambar));
@endphp
@if($gambarAda)
    <img src="{{ asset('storage/' . $alat->gambar) }}" alt="{{ $alat->nama_alat }}" class="w-12 h-12 rounded-lg object-cover border border-gray-200">
@else
    <img src="{{ $placeholder }}" alt="{{ $alat->nama_alat }} placeholder" class="w-12 h-12 rounded-lg border border-gray-200 bg-gray-50">
@endif
                  <div>
                    <div class="fw-medium">{{ $alat->nama_alat }}</div>
                    <div class="text-muted small">{{ $alat->kategori->nama_kategori ?? '-' }}</div>
                  </div>
                </div>
              </td>
              <td class="small text-muted">{{ $alat->kategori->nama_kategori ?? '-' }}</td>
              <td class="text-center"><span class="badge {{ $alat->stok <= 3 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">{{ $alat->stok }}</span></td>
              <td><input type="number" name="jumlah[]" value="1" min="1" max="{{ $alat->stok }}" class="form-control form-control-sm text-center" oninput="if(this.value>{{ $alat->stok }})this.value={{ $alat->stok }};if(this.value<1)this.value=1;"></td>
            </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted">Tidak ada alat tersedia.</td></tr>
            @endforelse
          </tbody>
        </table>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex align-items-start gap-3 mt-3">
          <span class="text-blue-600 fs-5 mt-1">&#8505;</span>
          <div>
            <p class="fw-semibold text-blue-800 mb-1">Pilih beberapa alat</p>
            <p class="text-blue-700 small mb-0">Centang <b>Pilih</b> untuk memilih lebih dari 1 alat. Jumlah otomatis divalidasi sesuai stok tersisa.</p>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
          <p class="text-muted small mb-0">Status awal: <span class="fw-bold text-dark">Diajukan</span> — stok dikurangi saat disetujui.</p>
          <button type="submit" class="btn btn-success fw-semibold">Ajukan Peminjaman &rarr;</button>
        </div>
      </div>
    </div>
  </form>
</div>
</body>
</html>
