<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Layanan;
use App\Rules\NoSimilarGameName;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GameController extends Controller
{
    /**
     * Tampilkan daftar seluruh game (aktif dan nonaktif) dengan pagination 10 baris.
     * Referensi: A-04
     */
    public function index(Request $request): View
    {
        $query = Game::withCount('layanan');

        if ($request->filled('q')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->q);
            $query->where(function ($q) use ($search) {
                $q->where('nama_game', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('aktif', true);
            } elseif ($request->status === 'nonaktif') {
                $query->where('aktif', false);
            }
        }

        $games = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => Game::count(),
            'aktif' => Game::where('aktif', true)->count(),
            'total_layanan' => Layanan::count(),
        ];

        return view('admin.games.index', compact('games', 'stats'));
    }

    /**
     * Tampilkan form penambahan data game baru.
     * Referensi: A-03
     */
    public function create(): View
    {
        return view('admin.games.create');
    }

    /**
     * Simpan data game baru ke database.
     * Referensi: A-03
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_game' => ['required', 'string', 'max:100', 'unique:games,nama_game', new NoSimilarGameName],
            'kategori' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string'],
            'gambar_upload' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'gambar_url' => ['nullable', 'url', 'max:2048'],
            'aktif' => ['nullable', 'boolean'],
        ], [
            'nama_game.required' => 'Nama game wajib diisi.',
            'nama_game.unique' => 'Nama game sudah terdaftar.',
            'gambar_upload.image' => 'Berkas harus berupa gambar valid (JPG, PNG, atau WebP).',
            'gambar_upload.max' => 'Ukuran gambar maksimal adalah 2 MB.',
            'gambar_url.url' => 'Format URL gambar tidak valid.',
        ]);

        $fileUpload = $request->file('gambar_upload') ?? $request->file('gambar_file');

        if (! $fileUpload && empty($request->gambar_url)) {
            return back()->withInput()->withErrors([
                'gambar' => 'Pilih salah satu: unggah berkas gambar atau masukkan URL gambar.',
            ]);
        }

        $gambarPath = '';
        if ($fileUpload) {
            $gambarPath = $fileUpload->store('games', 'public');
        } else {
            $gambarPath = $request->gambar_url;
        }

        Game::create([
            'nama_game' => $validated['nama_game'],
            'kategori' => $validated['kategori'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'gambar' => $gambarPath,
            'aktif' => $request->boolean('aktif', true),
        ]);

        return redirect()->route('admin.games.index')
            ->with('success', "Game '{$validated['nama_game']}' berhasil ditambahkan ke katalog.");
    }

    /**
     * Tampilkan form ubah data game.
     * Referensi: A-05
     */
    public function edit(Game $game): View
    {
        return view('admin.games.edit', compact('game'));
    }

    /**
     * Perbarui data game.
     * Referensi: A-05
     */
    public function update(Request $request, Game $game): RedirectResponse
    {
        $validated = $request->validate([
            'nama_game' => ['required', 'string', 'max:100', Rule::unique('games', 'nama_game')->ignore($game->id), new NoSimilarGameName($game->id)],
            'kategori' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string'],
            'gambar_upload' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'gambar_url' => ['nullable', 'url', 'max:2048'],
            'aktif' => ['nullable', 'boolean'],
        ], [
            'nama_game.required' => 'Nama game wajib diisi.',
            'nama_game.unique' => 'Nama game sudah terdaftar pada game lain.',
            'gambar_upload.image' => 'Berkas harus berupa gambar valid (JPG, PNG, atau WebP).',
            'gambar_upload.max' => 'Ukuran gambar maksimal adalah 2 MB.',
            'gambar_url.url' => 'Format URL gambar tidak valid.',
        ]);

        $gambarPath = $game->gambar;
        $fileUpload = $request->file('gambar_upload') ?? $request->file('gambar_file');

        // Jika ada unggahan berkas baru
        if ($fileUpload) {
            // Hapus gambar lama jika berkas lokal di disk public
            if ($game->gambar && ! str_starts_with($game->gambar, 'http') && Storage::disk('public')->exists($game->gambar)) {
                Storage::disk('public')->delete($game->gambar);
            }
            $gambarPath = $fileUpload->store('games', 'public');
        } elseif ($request->filled('gambar_url') && $request->gambar_url !== $game->gambar) {
            // Jika memilih URL baru
            if ($game->gambar && ! str_starts_with($game->gambar, 'http') && Storage::disk('public')->exists($game->gambar)) {
                Storage::disk('public')->delete($game->gambar);
            }
            $gambarPath = $request->gambar_url;
        }

        $game->update([
            'nama_game' => $validated['nama_game'],
            'kategori' => $validated['kategori'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'gambar' => $gambarPath,
            'aktif' => $request->boolean('aktif', false),
        ]);

        return redirect()->route('admin.games.index')
            ->with('success', "Data game '{$game->nama_game}' berhasil diperbarui.");
    }

    /**
     * Hapus data game.
     * Aturan A-06: Ditolak bila game masih memiliki layanan (aktif maupun nonaktif).
     */
    public function destroy(Game $game): RedirectResponse
    {
        if ($game->layanan()->exists()) {
            return redirect()->route('admin.games.index')
                ->with('error', "Game '{$game->nama_game}' tidak dapat dihapus karena masih memiliki {$game->layanan()->count()} layanan joki terkait. Silakan nonaktifkan game melalui menu Ubah jika ingin menyembunyikannya dari katalog.");
        }

        // Hapus berkas gambar lokal jika ada
        if ($game->gambar && ! str_starts_with($game->gambar, 'http') && Storage::disk('public')->exists($game->gambar)) {
            Storage::disk('public')->delete($game->gambar);
        }

        $namaGame = $game->nama_game;
        $game->delete();

        return redirect()->route('admin.games.index')
            ->with('success', "Game '{$namaGame}' beserta berkas gambar berhasil dihapus.");
    }
}
