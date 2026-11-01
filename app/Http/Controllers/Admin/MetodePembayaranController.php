<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetodePembayaranTujuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Controller Metode Pembayaran Rekening/QRIS (Sisi Panel Admin)
 *
 * Mengelola rekening bank, dompet digital, dan barcode QRIS tujuan transfer:
 * 1. Manajemen data rekening (nama bank/e-wallet, nomor akun, nama pemilik)
 * 2. Unggah gambar barcode QRIS dan logo perbankan ke penyimpanan publik
 * 3. Pengaturan urutan prioritas dan toggle status aktif penayangan di checkout
 */
class MetodePembayaranController extends Controller
{
    /**
     * Tampilkan daftar metode pembayaran tujuan.
     */
    public function index(): View
    {
        $metodes = MetodePembayaranTujuan::orderBy('urutan')->get();

        return view('admin.metode-pembayaran.index', compact('metodes'));
    }

    /**
     * Form tambah rekening / QRIS baru.
     */
    public function create(): View
    {
        return view('admin.metode-pembayaran.create');
    }

    /**
     * Simpan rekening / QRIS baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_metode' => ['required', 'string', 'max:60'],
            'kategori' => ['required', 'in:bank,ewallet,qris'],
            'nomor_rekening' => ['nullable', 'string', 'max:60'],
            'atas_nama' => ['required', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            'gambar_qris' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'gambar_qris_url' => ['nullable', 'url', 'max:500'],
            'petunjuk_transfer' => ['nullable', 'string', 'max:1000'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'aktif' => ['nullable', 'boolean'],
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('pembayaran/logo', 'public');
        } elseif ($request->filled('logo_url')) {
            $logoPath = $request->input('logo_url');
        }

        $qrisPath = null;
        if ($request->hasFile('gambar_qris')) {
            $qrisPath = $request->file('gambar_qris')->store('pembayaran/qris', 'public');
        } elseif ($request->filled('gambar_qris_url')) {
            $qrisPath = $request->input('gambar_qris_url');
        }

        MetodePembayaranTujuan::create([
            'nama_metode' => $validated['nama_metode'],
            'kategori' => $validated['kategori'],
            'nomor_rekening' => $validated['nomor_rekening'] ?? null,
            'atas_nama' => $validated['atas_nama'],
            'logo' => $logoPath,
            'gambar_qris' => $qrisPath,
            'petunjuk_transfer' => $validated['petunjuk_transfer'] ?? null,
            'urutan' => $validated['urutan'] ?? 0,
            'aktif' => $request->boolean('aktif', true),
        ]);

        return redirect()->route('admin.metode-pembayaran.index')
            ->with('success', "Metode pembayaran '{$validated['nama_metode']}' berhasil ditambahkan.");
    }

    /**
     * Form edit rekening / QRIS.
     */
    public function edit(MetodePembayaranTujuan $metode_pembayaran): View
    {
        return view('admin.metode-pembayaran.edit', [
            'metode' => $metode_pembayaran,
        ]);
    }

    /**
     * Perbarui rekening / QRIS.
     */
    public function update(Request $request, MetodePembayaranTujuan $metode_pembayaran): RedirectResponse
    {
        $validated = $request->validate([
            'nama_metode' => ['required', 'string', 'max:60'],
            'kategori' => ['required', 'in:bank,ewallet,qris'],
            'nomor_rekening' => ['nullable', 'string', 'max:60'],
            'atas_nama' => ['required', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            'gambar_qris' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'gambar_qris_url' => ['nullable', 'url', 'max:500'],
            'petunjuk_transfer' => ['nullable', 'string', 'max:1000'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'aktif' => ['nullable', 'boolean'],
        ]);

        $logoPath = $metode_pembayaran->logo;
        if ($request->hasFile('logo')) {
            if ($metode_pembayaran->logo && ! str_starts_with($metode_pembayaran->logo, 'http')) {
                Storage::disk('public')->delete($metode_pembayaran->logo);
            }
            $logoPath = $request->file('logo')->store('pembayaran/logo', 'public');
        } elseif ($request->filled('logo_url')) {
            $logoPath = $request->input('logo_url');
        }

        $qrisPath = $metode_pembayaran->gambar_qris;
        if ($request->hasFile('gambar_qris')) {
            if ($metode_pembayaran->gambar_qris && ! str_starts_with($metode_pembayaran->gambar_qris, 'http')) {
                Storage::disk('public')->delete($metode_pembayaran->gambar_qris);
            }
            $qrisPath = $request->file('gambar_qris')->store('pembayaran/qris', 'public');
        } elseif ($request->filled('gambar_qris_url')) {
            $qrisPath = $request->input('gambar_qris_url');
        }

        $metode_pembayaran->update([
            'nama_metode' => $validated['nama_metode'],
            'kategori' => $validated['kategori'],
            'nomor_rekening' => $validated['nomor_rekening'] ?? null,
            'atas_nama' => $validated['atas_nama'],
            'logo' => $logoPath,
            'gambar_qris' => $qrisPath,
            'petunjuk_transfer' => $validated['petunjuk_transfer'] ?? null,
            'urutan' => $validated['urutan'] ?? $metode_pembayaran->urutan,
            'aktif' => $request->boolean('aktif', true),
        ]);

        return redirect()->route('admin.metode-pembayaran.index')
            ->with('success', "Metode pembayaran '{$metode_pembayaran->nama_metode}' berhasil diperbarui.");
    }

    /**
     * Hapus rekening / QRIS.
     */
    public function destroy(MetodePembayaranTujuan $metode_pembayaran): RedirectResponse
    {
        $nama = $metode_pembayaran->nama_metode;

        if ($metode_pembayaran->logo && ! str_starts_with($metode_pembayaran->logo, 'http')) {
            Storage::disk('public')->delete($metode_pembayaran->logo);
        }
        if ($metode_pembayaran->gambar_qris && ! str_starts_with($metode_pembayaran->gambar_qris, 'http')) {
            Storage::disk('public')->delete($metode_pembayaran->gambar_qris);
        }

        $metode_pembayaran->delete();

        return redirect()->route('admin.metode-pembayaran.index')
            ->with('success', "Metode pembayaran '{$nama}' berhasil dihapus.");
    }
}
