<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Controller Manajemen Banner Beranda (Sisi Panel Admin)
 *
 * Mengelola gambar slider promosi pada halaman utama beranda toko:
 * 1. Manajemen materi visual promosi (judul promo, subjudul, dan berkas gambar)
 * 2. Pengurutan posisi tampil slider di beranda (urutan)
 * 3. Toggle penayangan aktif/nonaktif dan pembersihan berkas gambar lama
 */
class BannerController extends Controller
{
    /**
     * Tampilkan daftar banner dengan live preview carousel di bagian atas.
     */
    public function index(): View
    {
        $banners = Banner::urut()->get();
        $bannersAktif = $banners->where('is_aktif', true)->values();

        return view('admin.banners.index', compact('banners', 'bannersAktif'));
    }

    /**
     * Tampilkan form tambah banner baru.
     */
    public function create(): View
    {
        $nextUrutan = (Banner::max('urutan') ?? 0) + 1;

        return view('admin.banners.create', compact('nextUrutan'));
    }

    /**
     * Simpan banner baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:100'],
            'subjudul' => ['nullable', 'string', 'max:255'],
            'gambar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'urutan' => ['required', 'integer', 'min:0', 'max:255'],
            'is_aktif' => ['nullable', 'boolean'],
        ], [
            'judul.required' => 'Judul banner wajib diisi.',
            'gambar.required' => 'Gambar banner wajib diunggah.',
            'gambar.image' => 'Berkas harus berupa gambar valid (JPG, PNG, atau WebP).',
            'gambar.max' => 'Ukuran gambar maksimal adalah 3 MB.',
            'urutan.required' => 'Urutan tampil wajib diisi.',
        ]);

        $gambarPath = $request->file('gambar')->store('banners', 'public');

        Banner::create([
            'judul' => $validated['judul'],
            'subjudul' => $validated['subjudul'] ?? null,
            'gambar' => $gambarPath,
            'urutan' => $validated['urutan'],
            'is_aktif' => $request->boolean('is_aktif', true),
        ]);

        return redirect()->route('admin.banners.index')
            ->with('success', "Banner '{$validated['judul']}' berhasil ditambahkan.");
    }

    /**
     * Tampilkan form edit banner.
     */
    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Perbarui data banner.
     */
    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:100'],
            'subjudul' => ['nullable', 'string', 'max:255'],
            'gambar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'urutan' => ['required', 'integer', 'min:0', 'max:255'],
            'is_aktif' => ['nullable', 'boolean'],
        ], [
            'judul.required' => 'Judul banner wajib diisi.',
            'gambar.image' => 'Berkas harus berupa gambar valid (JPG, PNG, atau WebP).',
            'gambar.max' => 'Ukuran gambar maksimal adalah 3 MB.',
            'urutan.required' => 'Urutan tampil wajib diisi.',
        ]);

        $gambarPath = $banner->gambar;

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama
            if ($banner->gambar && Storage::disk('public')->exists($banner->gambar)) {
                Storage::disk('public')->delete($banner->gambar);
            }
            $gambarPath = $request->file('gambar')->store('banners', 'public');
        }

        $banner->update([
            'judul' => $validated['judul'],
            'subjudul' => $validated['subjudul'] ?? null,
            'gambar' => $gambarPath,
            'urutan' => $validated['urutan'],
            'is_aktif' => $request->boolean('is_aktif', true),
        ]);

        return redirect()->route('admin.banners.index')
            ->with('success', "Banner '{$validated['judul']}' berhasil diperbarui.");
    }

    /**
     * Hapus banner beserta file gambarnya.
     */
    public function destroy(Banner $banner): RedirectResponse
    {
        $judul = $banner->judul;

        if ($banner->gambar && Storage::disk('public')->exists($banner->gambar)) {
            Storage::disk('public')->delete($banner->gambar);
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')
            ->with('success', "Banner '{$judul}' berhasil dihapus.");
    }
}
