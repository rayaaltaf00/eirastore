<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Controller Profil Pengguna
 *
 * Mengelola pembaruan identitas akun pengguna (nama, email, nomor WhatsApp)
 * serta penghapusan akun mandiri yang diamankan dari penghapusan akun Admin,
 * Worker, atau Customer yang masih memiliki riwayat transaksi/pesanan.
 */
class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        if (isset($validated['name']) && ! isset($validated['nama'])) {
            $validated['nama'] = $validated['name'];
            unset($validated['name']);
        }

        if (! empty($validated['no_hp'])) {
            $noHp = preg_replace('/[^0-9]/', '', (string) $validated['no_hp']);
            if (str_starts_with($noHp, '62')) {
                $noHp = '0'.substr($noHp, 2);
            }
            $validated['no_hp'] = $noHp;
        }

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->isAdmin()) {
            return back()->withErrors(['password' => 'Akun Administrator tidak dapat dihapus melalui formulir ini.'], 'userDeletion');
        }

        if ($user->isWorker()) {
            return back()->withErrors(['password' => 'Akun Worker tidak dapat dihapus. Silakan hubungi Administrator.'], 'userDeletion');
        }

        if ($user->pesananSebagaiCustomer()->exists()) {
            return back()->withErrors(['password' => 'Akun tidak dapat dihapus karena Anda masih memiliki riwayat transaksi/pesanan di sistem kami.'], 'userDeletion');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
