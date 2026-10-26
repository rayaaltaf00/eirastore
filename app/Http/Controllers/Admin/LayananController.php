<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TipeHarga;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Layanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LayananController extends Controller
{
    /**
     * Tampilkan daftar layanan joki dengan filter per game dan pagination.
     * Referensi: A-08
     */
    public function index(Request $request): View
    {
        $query = Layanan::with('game')
            ->withCount(['items', 'pesanan']);

        if ($request->filled('game_id')) {
            $query->where('game_id', $request->game_id);
        }

        if ($request->filled('tipe_harga')) {
            $query->where('tipe_harga', $request->tipe_harga);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nama_layanan', 'like', "%{$search}%")
                    ->orWhereHas('game', function ($g) use ($search) {
                        $g->where('nama_game', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('aktif', true);
            } elseif ($request->status === 'nonaktif') {
                $query->where('aktif', false);
            }
        }

        $layananList = $query->latest()->paginate(10)->withQueryString();
        $games = Game::orderBy('nama_game')->get(['id', 'nama_game']);

        $stats = [
            'total' => Layanan::count(),
            'aktif' => Layanan::where('aktif', true)->count(),
            'nonaktif' => Layanan::where('aktif', false)->count(),
        ];

        return view('admin.layanan.index', compact('layananList', 'games', 'stats'));
    }

    /**
     * Tampilkan form penambahan layanan baru.
     * Referensi: A-07
     */
    public function create(Request $request): View
    {
        $games = Game::orderBy('nama_game')->get(['id', 'nama_game']);
        $selectedGameId = $request->get('game_id');
        $tipeHargaList = TipeHarga::cases();

        return view('admin.layanan.create', compact('games', 'selectedGameId', 'tipeHargaList'));
    }

    /**
     * Simpan data layanan joki baru.
     * Referensi: A-07
     */
    public function store(Request $request): RedirectResponse
    {
        $gameId = $request->input('game_id');

        $validated = $request->validate([
            'game_id' => ['required', 'exists:games,id'],
            'nama_layanan' => [
                'required',
                'string',
                'max:100',
                Rule::unique('layanan', 'nama_layanan')->where('game_id', $gameId),
            ],
            'deskripsi' => ['nullable', 'string'],
            'gambar_upload' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'gambar_url' => ['nullable', 'url', 'max:2048'],
            'tipe_harga' => ['required', Rule::enum(TipeHarga::class)],
            'estimasi_waktu' => ['nullable', 'string', 'max:50'],
            'butuh_login_game' => ['nullable', 'boolean'],
            'aktif' => ['nullable', 'boolean'],
        ], [
            'game_id.required' => 'Pilih game induk untuk layanan ini.',
            'game_id.exists' => 'Game yang dipilih tidak valid.',
            'nama_layanan.required' => 'Nama layanan wajib diisi.',
            'nama_layanan.unique' => 'Nama layanan sudah digunakan pada game ini.',
            'gambar_upload.image' => 'Berkas harus berupa gambar valid (JPG, PNG, atau WebP).',
            'gambar_upload.max' => 'Ukuran gambar maksimal adalah 2 MB.',
            'gambar_url.url' => 'Format URL gambar tidak valid.',
            'tipe_harga.required' => 'Pilih salah satu tipe harga perhitungan.',
            'tipe_harga.enum' => 'Tipe harga tidak valid.',
        ]);

        $fileUpload = $request->file('gambar_upload');
        $gambarPath = null;

        if ($fileUpload) {
            $gambarPath = $fileUpload->store('layanan', 'public');
        } elseif ($request->filled('gambar_url')) {
            $gambarPath = $request->gambar_url;
        }

        $layanan = Layanan::create([
            'game_id' => $validated['game_id'],
            'nama_layanan' => $validated['nama_layanan'],
            'gambar' => $gambarPath,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'tipe_harga' => $validated['tipe_harga'],
            'estimasi_waktu' => $validated['estimasi_waktu'] ?? null,
            'butuh_login_game' => $request->boolean('butuh_login_game', true),
            'aktif' => $request->boolean('aktif', true),
        ]);

        return redirect()->route('admin.layanan.items.index', $layanan)
            ->with('success', "Layanan '{$layanan->nama_layanan}' berhasil dibuat. Silakan tambahkan item tingkat/pilihan di bawah ini.");
    }

    /**
     * Tampilkan form ubah layanan.
     * Referensi: A-09
     */
    public function edit(Layanan $layanan): View
    {
        $layanan->load(['game', 'items', 'pesanan']);
        $games = Game::orderBy('nama_game')->get(['id', 'nama_game']);
        $tipeHargaList = TipeHarga::cases();

        // Aturan Terkunci A-09:
        // tipe_harga tidak dapat diubah bila layanan sudah memiliki item atau pesanan
        $isTipeHargaLocked = $layanan->items()->exists() || $layanan->pesanan()->exists();
        // game_id tidak dapat diubah bila layanan sudah memiliki pesanan
        $isGameLocked = $layanan->pesanan()->exists();

        return view('admin.layanan.edit', compact(
            'layanan',
            'games',
            'tipeHargaList',
            'isTipeHargaLocked',
            'isGameLocked'
        ));
    }

    /**
     * Perbarui data layanan.
     * Referensi: A-09
     */
    public function update(Request $request, Layanan $layanan): RedirectResponse
    {
        $isTipeHargaLocked = $layanan->items()->exists() || $layanan->pesanan()->exists();
        $isGameLocked = $layanan->pesanan()->exists();

        // Validasi aturan terkunci server-side
        if ($isGameLocked && (int) $request->game_id !== (int) $layanan->game_id) {
            return back()->withInput()->withErrors([
                'game_id' => 'Game induk tidak dapat diubah karena layanan ini sudah memiliki riwayat pesanan.',
            ]);
        }

        if ($isTipeHargaLocked && $request->tipe_harga !== $layanan->tipe_harga->value) {
            return back()->withInput()->withErrors([
                'tipe_harga' => 'Tipe harga tidak dapat diubah karena layanan ini sudah memiliki item atau riwayat pesanan.',
            ]);
        }

        $targetGameId = $isGameLocked ? $layanan->game_id : $request->input('game_id');

        $validated = $request->validate([
            'game_id' => [$isGameLocked ? 'nullable' : 'required', 'exists:games,id'],
            'nama_layanan' => [
                'required',
                'string',
                'max:100',
                Rule::unique('layanan', 'nama_layanan')
                    ->where('game_id', $targetGameId)
                    ->ignore($layanan->id),
            ],
            'deskripsi' => ['nullable', 'string'],
            'gambar_upload' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'gambar_url' => ['nullable', 'url', 'max:2048'],
            'tipe_harga' => [$isTipeHargaLocked ? 'nullable' : 'required', Rule::enum(TipeHarga::class)],
            'estimasi_waktu' => ['nullable', 'string', 'max:50'],
            'butuh_login_game' => ['nullable', 'boolean'],
            'aktif' => ['nullable', 'boolean'],
        ], [
            'nama_layanan.required' => 'Nama layanan wajib diisi.',
            'nama_layanan.unique' => 'Nama layanan sudah digunakan pada game ini.',
            'gambar_upload.image' => 'Berkas harus berupa gambar valid (JPG, PNG, atau WebP).',
            'gambar_upload.max' => 'Ukuran gambar maksimal adalah 2 MB.',
            'gambar_url.url' => 'Format URL gambar tidak valid.',
        ]);

        $gambarPath = $layanan->gambar;
        $fileUpload = $request->file('gambar_upload');

        if ($request->boolean('hapus_gambar_khusus')) {
            if ($layanan->gambar && ! str_starts_with($layanan->gambar, 'http') && Storage::disk('public')->exists($layanan->gambar)) {
                Storage::disk('public')->delete($layanan->gambar);
            }
            $gambarPath = null;
        } elseif ($fileUpload) {
            if ($layanan->gambar && ! str_starts_with($layanan->gambar, 'http') && Storage::disk('public')->exists($layanan->gambar)) {
                Storage::disk('public')->delete($layanan->gambar);
            }
            $gambarPath = $fileUpload->store('layanan', 'public');
        } elseif ($request->filled('gambar_url') && $request->gambar_url !== $layanan->gambar) {
            if ($layanan->gambar && ! str_starts_with($layanan->gambar, 'http') && Storage::disk('public')->exists($layanan->gambar)) {
                Storage::disk('public')->delete($layanan->gambar);
            }
            $gambarPath = $request->gambar_url;
        }

        $layanan->update([
            'game_id' => $isGameLocked ? $layanan->game_id : $validated['game_id'],
            'nama_layanan' => $validated['nama_layanan'],
            'gambar' => $gambarPath,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'tipe_harga' => $isTipeHargaLocked ? $layanan->tipe_harga : $validated['tipe_harga'],
            'estimasi_waktu' => $validated['estimasi_waktu'] ?? null,
            'butuh_login_game' => $request->boolean('butuh_login_game', true),
            'aktif' => $request->boolean('aktif', false),
        ]);

        return redirect()->route('admin.layanan.index', ['game_id' => $layanan->game_id])
            ->with('success', "Data layanan '{$layanan->nama_layanan}' berhasil diperbarui.");
    }

    /**
     * Hapus data layanan.
     * Aturan A-10: Ditolak bila layanan sudah punya pesanan.
     */
    public function destroy(Layanan $layanan): RedirectResponse
    {
        if ($layanan->pesanan()->exists()) {
            return redirect()->route('admin.layanan.index', ['game_id' => $layanan->game_id])
                ->with('error', "Layanan '{$layanan->nama_layanan}' tidak dapat dihapus karena sudah memiliki {$layanan->pesanan()->count()} riwayat pesanan. Silakan nonaktifkan layanan melalui menu Ubah jika ingin menyembunyikannya dari katalog.");
        }

        $namaLayanan = $layanan->nama_layanan;
        $gameId = $layanan->game_id;

        // Hapus berkas poster lokal jika ada di storage
        if ($layanan->gambar && ! str_starts_with($layanan->gambar, 'http') && Storage::disk('public')->exists($layanan->gambar)) {
            Storage::disk('public')->delete($layanan->gambar);
        }

        // Hapus item-itemnya terlebih dahulu lalu layanan
        $layanan->items()->delete();
        $layanan->delete();

        return redirect()->route('admin.layanan.index', ['game_id' => $gameId])
            ->with('success', "Layanan '{$namaLayanan}' beserta item layanannya berhasil dihapus.");
    }
}
