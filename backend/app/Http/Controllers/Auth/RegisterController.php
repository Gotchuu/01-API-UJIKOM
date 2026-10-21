<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'no_hp'    => 'nullable|string|max:15',
            'alamat'   => 'nullable|string',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'peminjam',
            'no_hp'    => $request->no_hp,
            'alamat'   => $request->alamat,
        ]);

        // Otomatis login setelah berhasil registrasi
        Auth::login($user);

        return redirect()->route('peminjam.dashboard')->with('success', 'Registrasi berhasil! Selamat datang.');
    }
}