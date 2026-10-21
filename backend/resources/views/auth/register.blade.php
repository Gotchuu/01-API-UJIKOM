<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Sistem Peminjaman Alat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 flex items-center justify-center min-h-screen p-4 py-10 antialiased">

    <div class="w-full max-w-md">
        <!-- Header Logo & Title -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-slate-900 text-white rounded-xl mb-3 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235 drop-shadow-sma8.25 8.25 0 0116 0" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Registrasi Akun Baru</h1>
            <p class="text-sm text-slate-500 mt-1">Lengkapi data Anda untuk mendaftar ke sistem</p>
        </div>

        <!-- Card Container -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200/80">

            <!-- Alert Error Validasi -->
            @if($errors->any())
                <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs font-medium space-y-1">
                    <div class="font-semibold mb-1">Terjadi kesalahan input:</div>
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                        placeholder="Masukkan nama lengkap"
                        class="w-full px-3.5 py-2.5 text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition duration-200">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        placeholder="nama@sekolah.sch.id"
                        class="w-full px-3.5 py-2.5 text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition duration-200">
                </div>

                <!-- No. HP / WhatsApp -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">No. HP / WhatsApp</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp') }}"
                        placeholder="081234567890"
                        class="w-full px-3.5 py-2.5 text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition duration-200">
                </div>

                <!-- Alamat -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Alamat</label>
                    <textarea name="alamat" rows="2" placeholder="Masukkan alamat domisili"
                        class="w-full px-3.5 py-2.5 text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition duration-200">{{ old('alamat') }}</textarea>
                </div>

                <!-- Grid Password & Konfirmasi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Password</label>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full px-3.5 py-2.5 text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition duration-200">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Konfirmasi</label>
                        <input type="password" name="password_confirmation" required placeholder="••••••••"
                            class="w-full px-3.5 py-2.5 text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition duration-200">
                    </div>
                </div>

                <!-- Tombol Submit -->
                <button type="submit"
                    class="w-full mt-2 bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 rounded-xl text-sm transition duration-200 shadow-sm active:scale-[0.99]">
                    Daftar Sekarang
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-slate-500">
                Sudah memiliki akun? <a href="{{ route('login') }}" class="text-slate-900 font-semibold hover:underline">Masuk di sini</a>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; {{ date('Y') }} UJIKOM - Peminjaman Alat
        </p>
    </div>

</body>
</html>