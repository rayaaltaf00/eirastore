<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\LayananItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LayananItemController extends Controller
{
    /**
     * Tampilkan daftar seluruh item milik sebuah layanan joki.
     * Referensi: A-12
     */
    public function index(Request $request, Layanan $layanan): View
    {
        $layanan->load('game');

        $query = $layanan->items()
            ->with('parent')
            ->withCount('children')
            ->orderByRaw("CASE WHEN jenis = 'utama' THEN 1 ELSE 2 END")
            ->orderBy('urutan');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $items = $query->get();

        $kategoriList = $layanan->items()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->pluck('kategori')
            ->unique()
            ->values();

        return view('admin.layanan-items.index', compact('layanan', 'items', 'kategoriList'));
    }

    /**
     * Tampilkan form penambahan item layanan baru.
     * Referensi: A-11
     */
    public function create(Layanan $layanan): View
    {
        $layanan->load('game');

        // Parent options: hanya item utama milik layanan ini yang belum punya parent (maks 1 tingkat)
        $parentCandidates = $layanan->items()
            ->where('jenis', 'utama')
            ->whereNull('parent_id')
            ->orderBy('urutan')
            ->get();

        // Kategori yang sudah ada di layanan ini untuk saran input
        $existingKategori = $layanan->items()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->pluck('kategori')
            ->map(fn ($k) => trim($k))
            ->filter()
            ->unique()
            ->values();

        // Cari urutan berikutnya secara otomatis
        $nextUrutan = ($layanan->items()->max('urutan') ?? 0) + 1;

        return view('admin.layanan-items.create', compact('layanan', 'parentCandidates', 'nextUrutan', 'existingKategori'));
    }

    /**
     * Simpan data item layanan baru.
     * Referensi: A-11
     */
    public function store(Request $request, Layanan $layanan): RedirectResponse
    {
        $tipeHarga = $layanan->tipe_harga->value;

        $validated = $request->validate([
            'jenis' => ['required', 'in:utama,tambahan'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'nama' => ['required', 'string', 'max:150'],
            'urutan' => ['required', 'integer', 'min:0'],
            'harga' => ['required', 'numeric', 'min:0'],
            'nilai_maks' => ['nullable', 'integer', 'min:1'],
            'satuan' => ['nullable', 'string', 'max:50'],
            'parent_id' => [
                'nullable',
                Rule::exists('layanan_item', 'id')
                    ->where('layanan_id', $layanan->id)
                    ->where('jenis', 'utama'),
            ],
            'aktif' => ['nullable', 'boolean'],
        ], [
            'nama.required' => 'Nama item tingkatan wajib diisi.',
            'urutan.required' => 'Nomor urutan wajib diisi.',
            'harga.required' => 'Harga wajib diisi.',
        ]);

        $this->validateBusinessRules($request, $layanan);

        $layanan->items()->create([
            'parent_id' => $validated['parent_id'] ?? null,
            'jenis' => $validated['jenis'],
            'kategori' => $validated['kategori'] ?? null,
            'nama' => $validated['nama'],
            'urutan' => $validated['urutan'],
            'harga' => $validated['harga'],
            'nilai_maks' => $validated['nilai_maks'] ?? null,
            'satuan' => $validated['satuan'] ?? null,
            'aktif' => $request->boolean('aktif', true),
        ]);

        return redirect()->route('admin.layanan.items.index', $layanan)
            ->with('success', "Item '{$validated['nama']}' berhasil ditambahkan ke layanan.");
    }

    /**
     * Tampilkan form ubah item layanan.
     * Referensi: A-13
     */
    public function edit(Layanan $layanan, LayananItem $item): View
    {
        $layanan->load('game');

        $parentCandidates = $layanan->items()
            ->where('jenis', 'utama')
            ->whereNull('parent_id')
            ->where('id', '!=', $item->id)
            ->orderBy('urutan')
            ->get();

        $existingKategori = $layanan->items()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->pluck('kategori')
            ->map(fn ($k) => trim($k))
            ->filter()
            ->unique()
            ->values();

        return view('admin.layanan-items.edit', compact('layanan', 'item', 'parentCandidates', 'existingKategori'));
    }

    /**
     * Perbarui data item layanan.
     * Referensi: A-13
     */
    public function update(Request $request, Layanan $layanan, LayananItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'jenis' => ['required', 'in:utama,tambahan'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'nama' => ['required', 'string', 'max:150'],
            'urutan' => ['required', 'integer', 'min:0'],
            'harga' => ['required', 'numeric', 'min:0'],
            'nilai_maks' => ['nullable', 'integer', 'min:1'],
            'satuan' => ['nullable', 'string', 'max:50'],
            'parent_id' => [
                'nullable',
                Rule::exists('layanan_item', 'id')
                    ->where('layanan_id', $layanan->id)
                    ->where('jenis', 'utama')
                    ->whereNot('id', $item->id),
            ],
            'aktif' => ['nullable', 'boolean'],
        ]);

        $this->validateBusinessRules($request, $layanan, $item);

        $item->update([
            'parent_id' => $validated['parent_id'] ?? null,
            'jenis' => $validated['jenis'],
            'kategori' => $validated['kategori'] ?? null,
            'nama' => $validated['nama'],
            'urutan' => $validated['urutan'],
            'harga' => $validated['harga'],
            'nilai_maks' => $validated['nilai_maks'] ?? null,
            'satuan' => $validated['satuan'] ?? null,
            'aktif' => $request->boolean('aktif', false),
        ]);

        return redirect()->route('admin.layanan.items.index', $layanan)
            ->with('success', "Item '{$item->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus item layanan beserta seluruh anak hierarkinya.
     * Referensi: A-14
     */
    public function destroy(Layanan $layanan, LayananItem $item): RedirectResponse
    {
        $namaItem = $item->nama;

        // Hapus item anak terlebih dahulu jika ada
        $item->children()->delete();
        $item->delete();

        return redirect()->route('admin.layanan.items.index', $layanan)
            ->with('success', "Item '{$namaItem}' beserta item turunannya berhasil dihapus.");
    }

    /**
     * Validasi aturan bisnis sesuai PRD Tabel Modul Manajemen Data Item Layanan
     */
    private function validateBusinessRules(Request $request, Layanan $layanan, ?LayananItem $currentItem = null): void
    {
        $tipe = $layanan->tipe_harga->value;
        $jenis = $request->input('jenis');
        $urutan = (int) $request->input('urutan');
        $parentId = $request->input('parent_id');
        $nilaiMaks = $request->input('nilai_maks');
        $satuan = $request->input('satuan');

        // Aturan Tipe Tangga (Rank)
        if ($tipe === 'tangga') {
            if ($jenis === 'utama') {
                if (empty($nilaiMaks) || (int) $nilaiMaks < 1) {
                    throw ValidationException::withMessages([
                        'nilai_maks' => 'Untuk layanan bertipe Tangga, Nilai Maks (jumlah bintang/langkah) wajib diisi minimal 1.',
                    ]);
                }

                // Urutan unik di antara item utama milik layanan ini
                $existingOrderQuery = $layanan->items()
                    ->where('jenis', 'utama')
                    ->where('urutan', $urutan);

                if ($currentItem) {
                    $existingOrderQuery->where('id', '!=', $currentItem->id);
                }

                if ($existingOrderQuery->exists()) {
                    throw ValidationException::withMessages([
                        'urutan' => "Nomor urutan {$urutan} sudah digunakan oleh tingkatan utama lain.",
                    ]);
                }

                if (! empty($parentId)) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'Layanan bertipe Tangga tidak mendukung item induk (parent).',
                    ]);
                }
            } else {
                // jenis == tambahan
                if (! empty($parentId)) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'Item tambahan tidak dapat memiliki item induk.',
                    ]);
                }
            }
        }

        // Aturan Tipe Progres (Persen)
        elseif ($tipe === 'progres') {
            if (! empty($nilaiMaks)) {
                throw ValidationException::withMessages([
                    'nilai_maks' => 'Untuk tipe Progres, nilai maks harus dikosongkan (persentase otomatis 0 s.d 100%).',
                ]);
            }

            if ($jenis === 'utama') {
                if (empty($parentId) && (float) $request->harga > 0) {
                    throw ValidationException::withMessages([
                        'harga' => 'Item induk (Region) pada tipe Progres harus berharga Rp 0.',
                    ]);
                }

                if (! empty($parentId)) {
                    $parent = LayananItem::find($parentId);
                    if ($parent && ! empty($parent->parent_id)) {
                        throw ValidationException::withMessages([
                            'parent_id' => 'Hierarki hanya diperbolehkan satu tingkat (wilayah di bawah region).',
                        ]);
                    }
                }
            } else {
                // tambahan
                if (! empty($parentId)) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'Item tambahan tidak dapat memiliki item induk.',
                    ]);
                }
            }
        }

        // Aturan Tipe Pilihan (Item)
        elseif ($tipe === 'pilihan') {
            if (! empty($parentId)) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Layanan bertipe Pilihan tidak mendukung item induk.',
                ]);
            }

            // satuan dan nilai_maks boleh diisi bersama bila item bisa dipesan dalam kuantitas
            if (! empty($satuan) && empty($nilaiMaks)) {
                throw ValidationException::withMessages([
                    'nilai_maks' => 'Bila satuan diisi, batas kuantitas pemesanan (nilai maks) wajib diisi.',
                ]);
            }
        }
    }
}
