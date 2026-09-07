@extends('layouts.app')

@section('title', 'Tambah Peminjaman - Panel Admin')
@section('header-title', 'Form Peminjaman Alat Baru')

@section('content')
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Tambah Peminjaman Alat</h3>
            <a href="{{ route('admin.peminjaman.index') }}" 
                class="bg-gray-500 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded-lg transition">
                Kembali
            </a>
        </div>

        <form action="{{ route('admin.peminjaman.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Alert Error Validasi -->
            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Field Peminjam & Tanggal -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Peminjam</label>
                    <select name="user_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="">-- Pilih Peminjam --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ ucfirst($user->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Tanggal Pinjam</label>
                    <input type="date" name="tgl_pinjam" value="{{ old('tgl_pinjam', date('Y-m-d')) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Rencana Tgl Kembali</label>
                    <input type="date" name="tgl_kembali_plan" value="{{ old('tgl_kembali_plan') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
            </div>

            <hr class="border-gray-200">

            <!-- Bagian Alat & Jumlah (Dinamis) -->
            <div>
                <div class="flex justify-between items-center mb-3">
                    <h4 class="text-md font-bold text-gray-800">Daftar Alat yang Dipinjam</h4>
                    <button type="button" id="btn-tambah-alat" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                        + Tambah Alat
                    </button>
                </div>

                <div id="wrapper-alat" class="space-y-3">
                    <div class="item-alat flex items-center gap-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <div class="flex-1">
                            <select name="alat_id[]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="">-- Pilih Alat --</option>
                                @foreach($alats as $alat)
                                    <option value="{{ $alat->id }}">
                                        {{ $alat->nama_alat }} (Stok: {{ $alat->stok }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-32">
                            <input type="number" name="jumlah[]" min="1" value="1" placeholder="Jumlah" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>

                        <button type="button" class="btn-hapus-alat bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg text-xs font-semibold transition">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="pt-4 border-t border-gray-200 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg text-sm transition">
                    Simpan Peminjaman
                </button>
            </div>
        </form>
    </div>

    <!-- Script JavaScript Tambah Baris Alat Dinamis -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('wrapper-alat');
            const btnTambah = document.getElementById('btn-tambah-alat');

            // Fungsi Tambah Baris Alat
            btnTambah.addEventListener('click', function () {
                const firstRow = wrapper.querySelector('.item-alat');
                const newRow = firstRow.cloneNode(true);

                // Reset nilai input dan select di baris baru
                newRow.querySelector('select').value = '';
                newRow.querySelector('input[type="number"]').value = '1';

                wrapper.appendChild(newRow);
            });

            // Fungsi Hapus Baris Alat
            wrapper.addEventListener('click', function (e) {
                if (e.target.classList.contains('btn-hapus-alat')) {
                    const rows = wrapper.querySelectorAll('.item-alat');
                    if (rows.length > 1) {
                        e.target.closest('.item-alat').remove();
                    } else {
                        alert('Minimal harus meminjam 1 alat!');
                    }
                }
            });
        });
    </script>
@endsection