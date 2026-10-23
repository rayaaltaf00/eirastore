<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:100', 'unique:users,email'],
            'no_hp'    => ['required', 'regex:/^[0-9]{10,15}$/'],   // hanya angka, 10-15 digit
            'password' => ['required', 'confirmed', Rules\Password::min(8)],
        ], [
            // Pesan error bahasa Indonesia
            'nama.required'     => 'Nama wajib diisi.',
            'nama.max'          => 'Nama maksimal 100 karakter.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar.',
            'no_hp.required'    => 'Nomor HP wajib diisi.',
            'no_hp.regex'       => 'Nomor HP harus berupa angka 10-15 digit.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        // Buat akun customer baru — role otomatis customer, status aktif
        $user = User::create([
            'nama'        => $request->nama,
            'email'       => $request->email,
            'no_hp'       => $request->no_hp,
            'password'    => Hash::make($request->password),
            'role'        => 'customer',
            'status_akun' => 'aktif',
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Arahkan ke halaman tujuan sebelumnya (jika ada draf pesanan) atau beranda
        return redirect()->intended('/');
    }
}
