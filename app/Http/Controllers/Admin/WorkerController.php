<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkerController extends Controller
{
    /**
     * Tampilkan daftar seluruh worker dengan pencarian, filter, dan pagination.
     * Referensi: A-16
     */
    public function index(Request $request): View
    {
        $query = User::worker()
            ->withCount(['pesananSebagaiWorker as pesanan_aktif_count' => function ($q) {
                $q->whereIn('status', User::STATUS_PESANAN_AKTIF_WORKER);
            }]);

        // Pencarian berdasarkan nama, email, atau nomor HP
        if ($request->filled('q')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], trim($request->q));
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        // Filter status akun (aktif / nonaktif)
        if ($request->filled('status')) {
            if (in_array($request->status, ['aktif', 'nonaktif'], true)) {
                $query->where('status_akun', $request->status);
            }
        }

        $workers = $query->latest()->paginate(10)->withQueryString();

        // Kartu metrik ringkasan
        $stats = [
            'total' => User::worker()->count(),
            'aktif' => User::worker()->where('status_akun', 'aktif')->count(),
            'nonaktif' => User::worker()->where('status_akun', 'nonaktif')->count(),
            'sedang_bekerja' => User::worker()
                ->whereHas('pesananSebagaiWorker', function ($q) {
                    $q->whereIn('status', User::STATUS_PESANAN_AKTIF_WORKER);
                })->count(),
        ];

        return view('admin.workers.index', compact('workers', 'stats'));
    }

    /**
     * Tampilkan formulir penambahan data worker baru.
     * Referensi: A-15
     */
    public function create(): View
    {
        return view('admin.workers.create');
    }

    /**
     * Simpan data worker baru ke dalam database.
     * Referensi: A-15
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'no_hp' => ['required', 'string', 'regex:/^(\+62|62|0)[0-9]{8,15}$/', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'nama.required' => 'Nama lengkap worker wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah terdaftar dalam sistem.',
            'no_hp.required' => 'Nomor WhatsApp / HP worker wajib diisi.',
            'no_hp.regex' => 'Format nomor HP tidak valid (awali dengan 08, 62, atau +62).',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Sanitasi nomor HP ke format standar Indonesia (08...)
        $noHp = $this->sanitizeNoHp($validated['no_hp']);

        $worker = User::create([
            'nama' => $validated['nama'],
            'email' => strtolower($validated['email']),
            'no_hp' => $noHp,
            'password' => $validated['password'], // otomatis di-hash oleh casts User
            'role' => Role::Worker,
            'status_akun' => 'aktif',
        ]);

        return redirect()
            ->route('admin.workers.index')
            ->with('success', "Worker {$worker->nama} berhasil didaftarkan dan siap menerima tugas.");
    }

    /**
     * Tampilkan formulir pengubahan data worker.
     * Referensi: A-17
     */
    public function edit(User $worker): View
    {
        if (! $worker->isWorker()) {
            abort(404, 'Data worker tidak ditemukan.');
        }

        $worker->loadCount(['pesananSebagaiWorker as pesanan_aktif_count' => function ($q) {
            $q->whereIn('status', User::STATUS_PESANAN_AKTIF_WORKER);
        }]);

        return view('admin.workers.edit', compact('worker'));
    }

    /**
     * Perbarui data worker.
     * Referensi: A-17
     */
    public function update(Request $request, User $worker): RedirectResponse
    {
        if (! $worker->isWorker()) {
            abort(404, 'Data worker tidak ditemukan.');
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($worker->id)],
            'no_hp' => ['required', 'string', 'regex:/^(\+62|62|0)[0-9]{8,15}$/', 'max:20'],
            'status_akun' => ['required', 'in:aktif,nonaktif'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'nama.required' => 'Nama lengkap worker wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email sudah dipakai pengguna lain.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.regex' => 'Format nomor HP tidak valid.',
            'password.min' => 'Password baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        // Guard: Jika status diubah ke nonaktif, pastikan tidak ada pesanan aktif yang berjalan (A-18)
        if ($worker->status_akun === 'aktif' && $validated['status_akun'] === 'nonaktif') {
            if ($worker->memilikiPesananAktif()) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'status_akun' => "Worker {$worker->nama} tidak dapat dinonaktifkan karena masih memiliki {$worker->jumlah_pesanan_aktif} pesanan aktif yang sedang dikerjakan.",
                    ]);
            }
        }

        $noHp = $this->sanitizeNoHp($validated['no_hp']);

        $worker->nama = $validated['nama'];
        $worker->email = strtolower($validated['email']);
        $worker->no_hp = $noHp;
        $worker->status_akun = $validated['status_akun'];

        if (! empty($validated['password'])) {
            $worker->password = $validated['password'];
        }

        $worker->save();

        return redirect()
            ->route('admin.workers.index')
            ->with('success', "Data worker {$worker->nama} berhasil diperbarui.");
    }

    /**
     * Nonaktifkan atau aktifkan kembali akun worker (Toggle Status).
     * Referensi: A-18
     */
    public function toggleStatus(User $worker): RedirectResponse
    {
        if (! $worker->isWorker()) {
            abort(404, 'Data worker tidak ditemukan.');
        }

        if ($worker->status_akun === 'aktif') {
            // Guard: Ditolak bila worker masih memiliki pesanan aktif
            if ($worker->memilikiPesananAktif()) {
                return back()->with(
                    'error',
                    "Aksi ditolak! Worker {$worker->nama} tidak dapat dinonaktifkan karena masih memiliki {$worker->jumlah_pesanan_aktif} pesanan aktif yang sedang berjalan."
                );
            }

            $worker->update(['status_akun' => 'nonaktif']);

            return back()->with('success', "Worker {$worker->nama} berhasil dinonaktifkan.");
        }

        // Jika sebelumnya nonaktif, aktifkan kembali
        $worker->update(['status_akun' => 'aktif']);

        return back()->with('success', "Worker {$worker->nama} berhasil diaktifkan kembali.");
    }

    /**
     * Normalisasi nomor HP ke format standar 08...
     */
    private function sanitizeNoHp(string $raw): string
    {
        $clean = preg_replace('/[^0-9]/', '', $raw);

        if (str_starts_with($clean, '62')) {
            return '08' . substr($clean, 2);
        }

        if (str_starts_with($clean, '8')) {
            return '08' . substr($clean, 1);
        }

        return $clean;
    }
}
