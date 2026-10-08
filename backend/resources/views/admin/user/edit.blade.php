@extends('layouts.app')

@section('title', 'Edit User - Panel Admin')
@section('header-title', 'Edit Data Pengguna')

@section('content')
<div class="max-w-xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('admin.user.update', $user->id) }}" method="POST">
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

       <div class="mb-4">
    <label class="block text-sm font-semibold text-gray-700 mb-1">Role / Hak Akses</label>
    
    @if(auth()->id() === $user->id)
        <!-- Jika mengedit akun sendiri: Input dikunci & beri input hidden agar nilai tidak hilang -->
        <select disabled class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
            <option value="admin" selected>Admin (Akun Anda Saat Ini)</option>
        </select>
        <input type="hidden" name="role" value="{{ $user->role }}">
        <p class="text-[11px] text-amber-600 mt-1 italic">*Anda tidak dapat mengubah role akun Anda sendiri.</p>
    @else
        <!-- Jika mengedit akun user lain: Pilihan role aktif -->
        <select name="role" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="petugas" {{ $user->role === 'petugas' ? 'selected' : '' }}>Petugas</option>
            <option value="peminjam" {{ $user->role === 'peminjam' ? 'selected' : '' }}>Peminjam / Siswa</option>
        </select>
    @endif
</div>

       <div class="mb-6">
           <label class="block text-gray-700 text-sm font-semibold mb-2">No. HP (Opsional)</label>
           <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
       </div>

       <div class="flex justify-end space-x-2">
           <a href="{{ route('admin.user.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
           <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Perbarui</button>
       </div>
   </form>
</div>
@endsection