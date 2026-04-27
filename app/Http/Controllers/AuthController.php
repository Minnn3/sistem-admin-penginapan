<?php

/*
|--------------------------------------------------------------------------
| AuthController — Hocky Guest House
|--------------------------------------------------------------------------
| Controller ini menangani proses autentikasi:
|   - showLogin() → menampilkan halaman form login
|   - login()     → memvalidasi dan memproses login
|   - logout()    → menghapus sesi dan redirect ke login
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman form login.
     * Jika sudah login, langsung redirect ke dashboard.
     */
    public function showLogin()
    {
        // Cek apakah user sudah login — kalau iya, skip halaman login
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Tampilkan view: resources/views/auth/login.blade.php
        return view('auth.login');
    }

    /**
     * Memproses data yang dikirim dari form login.
     * $request berisi data dari form (email, password, remember).
     */
    public function login(Request $request)
    {
        // Validasi input — jika gagal, otomatis balik ke form dengan pesan error
        $credentials = $request->validate([
            'email'    => ['required', 'email'],   // harus diisi, harus format email
            'password' => ['required'],             // harus diisi
        ], [
            // Pesan error dalam Bahasa Indonesia
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Auth::attempt() mencoba mencocokkan email & password dengan database
        // Parameter kedua ($request->boolean('remember')) = opsi "Ingat saya"
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Regenerate session ID untuk keamanan (mencegah session fixation)
            $request->session()->regenerate();

            // Redirect ke halaman yang dituju sebelum login, atau ke dashboard
            return redirect()->intended(route('dashboard'));
        }

        // Jika login gagal, kembali ke form dengan pesan error pada field email
        return back()->withErrors([
            'email' => 'Email atau password tidak sesuai.',
        ])->onlyInput('email'); // kirim balik nilai email saja (bukan password)
    }

    /**
     * Memproses logout.
     * Menghapus sesi dan redirect ke halaman login.
     */
    public function logout(Request $request)
    {
        Auth::logout(); // Hapus data autentikasi user dari sesi

        // Invalidate seluruh sesi untuk keamanan
        $request->session()->invalidate();

        // Generate CSRF token baru
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
